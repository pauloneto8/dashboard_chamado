<?php
/**
 * Serviço de autenticação OAuth2 com Bitrix24.
 * Fluxo: redireciona para autorização, recebe code no callback e troca por token.
 * Documentação: https://apidocs.bitrix24.com/settings/oauth/
 */

namespace App\Services;

use Exception;

class Bitrix24AuthService
{
    private string $domain;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct(
        string $domain,
        string $clientId,
        string $clientSecret,
        string $redirectUri
    ) {
        $this->domain = rtrim($domain, '/');
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }

    /**
     * Retorna a URL para redirecionar o usuário ao Bitrix24 para autorização.
     */
    public function getAuthorizeUrl(): string
    {
        $params = [
            'client_id'     => $this->clientId,
            'response_type' => 'code',
            'redirect_uri'  => $this->redirectUri,
        ];
        $base = (strpos($this->domain, 'http') === 0) ? $this->domain : 'https://' . $this->domain;
        return $base . '/oauth/authorize/?' . http_build_query($params);
    }

    /**
     * Troca o code pelo access_token e retorna os dados do usuário atual.
     * @param string $code Código recebido no callback
     * @param string|null $domain Domínio retornado no callback (se diferente do config)
     * @return array{access_token: string, user_id: string, email: string, name: string}
     * @throws Exception
     */
    public function trocarCodePorUsuario(string $code, ?string $domain = null): array
    {
        $domain = $domain ?? $this->domain;
        $base = (strpos($domain, 'http') === 0) ? $domain : 'https://' . $domain;
        $tokenUrl = $base . '/oauth/token/';
        $params = [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'redirect_uri'  => $this->redirectUri,
        ];

        $ch = curl_init($tokenUrl . '?' . http_build_query($params));
        if ($ch === false) {
            throw new Exception('Falha ao inicializar cURL');
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = is_string($response) ? json_decode($response, true) : null;
        if ($httpCode !== 200 || empty($data['access_token'])) {
            $msg = is_array($data) ? ($data['error_description'] ?? $data['error'] ?? $response) : $response;
            throw new Exception('Bitrix24 token: ' . $msg);
        }

        $accessToken = $data['access_token'];
        $memberId = $data['member_id'] ?? '';

        // Obter dados do usuário atual via REST user.current
        $user = $this->obterUsuarioAtual($base, $accessToken);
        $user['access_token'] = $accessToken;
        $user['member_id'] = $memberId;
        return $user;
    }

    /**
     * Chama user.current na REST API do Bitrix24.
     * @return array{user_id: string, email: string, name: string}
     */
    private function obterUsuarioAtual(string $baseUrl, string $accessToken): array
    {
        $url = rtrim($baseUrl, '/') . '/rest/user.current?auth=' . urlencode($accessToken);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = is_string($response) ? json_decode($response, true) : null;

        if (empty($data['result'])) {
            return [
                'user_id' => '0',
                'email'   => 'sem-email@bitrix24.local',
                'name'    => 'Usuário Bitrix24',
            ];
        }

        $result = $data['result'];
        $name = trim(($result['NAME'] ?? '') . ' ' . ($result['LAST_NAME'] ?? ''));
        $email = $result['EMAIL'] ?? 'sem-email@bitrix24.local';
        return [
            'user_id' => (string) ($result['ID'] ?? ''),
            'email'   => $email,
            'name'    => $name ?: 'Usuário Bitrix24',
        ];
    }
}
