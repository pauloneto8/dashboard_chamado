<?php
/**
 * Serviço de integração com a API GLPI.
 * Busca e atualiza chamados (tickets) no sistema GLPI.
 * Documentação: https://suporte.rosamaster.com/api.php/v2.2/doc
 */

namespace App\Services;

use Exception;

class GlpiApiService
{
    private string $baseUrl;
    private string $appToken;
    private string $userToken;
    private string $oauthToken;

    public function __construct(
        ?string $baseUrl = null,
        ?string $appToken = null,
        ?string $userToken = null,
        ?string $oauthToken = null
    ) {
        $this->baseUrl = rtrim($baseUrl ?? GLPI_API_URL, '/');
        $this->appToken = $appToken ?? GLPI_APP_TOKEN;
        $this->userToken = $userToken ?? GLPI_USER_TOKEN;
        $this->oauthToken = $oauthToken ?? (defined('GLPI_OAUTH_ACCESS_TOKEN') ? GLPI_OAUTH_ACCESS_TOKEN : '');
    }

    /**
     * Headers de autenticação: OAuth2 Bearer se definido; senão App-Token + user_token (legacy).
     * @return array<string, string>
     */
    private function getHeaders(): array
    {
        $headers = ['Content-Type' => 'application/json'];
        if ($this->oauthToken !== '') {
            $headers['Authorization'] = 'Bearer ' . $this->oauthToken;
        } else {
            $headers['Authorization'] = 'user_token ' . $this->userToken;
            $headers['App-Token'] = $this->appToken;
        }
        return $headers;
    }

    /**
     * Faz uma requisição GET à API GLPI.
     * @param string $endpoint Ex: /Ticket
     * @param array<string, mixed> $params Query string
     * @return array<string, mixed>
     * @throws Exception
     */
    public function get(string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        if ($ch === false) {
            throw new Exception('Falha ao inicializar cURL');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $this->formatHeaders(),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Erro cURL: ' . $error);
        }

        $data = is_string($response) ? json_decode($response, true) : null;
        if ($httpCode >= 400) {
            $msg = is_array($data) ? ($data['message'] ?? $response) : $response;
            throw new Exception('API GLPI erro ' . $httpCode . ': ' . $msg);
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Lista tickets (chamados) do GLPI.
     * Mapeia status do GLPI para as etapas do Kanban: Novo, Programado, Pendente, Solucionado.
     * @param array<string, mixed> $filtros Filtros opcionais (ex: criteria)
     * @return array<int, array<string, mixed>>
     */
    public function listarChamados(array $filtros = []): array
    {
        $resposta = $this->get('/Ticket', array_merge(['range' => '0-499'], $filtros));
        $itens = $resposta['data'] ?? [];
        $chamados = [];

        foreach ($itens as $item) {
            $input = $item['input'] ?? $item;
            $id = (int)($input['id'] ?? 0);
            if ($id === 0) {
                continue;
            }
            $chamados[] = [
                'id'        => $id,
                'titulo'    => $input['name'] ?? ('Chamado #' . $id),
                'conteudo'  => $input['content'] ?? '',
                'status'    => $this->mapearStatusParaEtapa((int)($input['status'] ?? 0)),
                'etapa'     => $this->mapearStatusParaEtapa((int)($input['status'] ?? 0)),
                'data_abertura' => $input['date'] ?? null,
                'solicitante'   => $input['users_id_recipient'] ?? null,
            ];
        }

        return $chamados;
    }

    /**
     * Mapeia o código de status do GLPI para as etapas do Kanban.
     * Ajustar conforme os IDs de status reais do seu GLPI.
     */
    private function mapearStatusParaEtapa(int $statusId): string
    {
        $mapa = [
            1 => 'Novo',       // Novo
            2 => 'Programado', // Em andamento / Programado
            3 => 'Pendente',   // Pendente
            4 => 'Solucionado',// Fechado
            5 => 'Solucionado',
        ];
        return $mapa[$statusId] ?? 'Novo';
    }

    /**
     * Mapeia etapa do Kanban para o ID de status do GLPI.
     * Ajustar conforme os IDs reais do seu GLPI.
     */
    public function mapearEtapaParaStatusId(string $etapa): int
    {
        $mapa = [
            'Novo'        => 1,
            'Programado'  => 2,
            'Pendente'    => 3,
            'Solucionado' => 4,
        ];
        return $mapa[$etapa] ?? 1;
    }

    /**
     * Atualiza o status de um ticket no GLPI (PATCH /Ticket/:id).
     * @throws Exception
     */
    public function atualizarStatusTicket(int $ticketId, string $etapa): bool
    {
        $statusId = $this->mapearEtapaParaStatusId($etapa);
        $url = $this->baseUrl . '/Ticket/' . $ticketId;
        $body = json_encode(['input' => ['status' => $statusId]]);
        $ch = curl_init($url);
        if ($ch === false) {
            throw new Exception('Falha ao inicializar cURL');
        }
        $headers = $this->formatHeaders();
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PATCH',
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode >= 400) {
            $data = is_string($response) ? json_decode($response, true) : null;
            $msg = is_array($data) ? ($data['message'] ?? $response) : $response;
            throw new Exception('GLPI atualização: ' . $msg);
        }
        return true;
    }

    private function formatHeaders(): array
    {
        $out = [];
        foreach ($this->getHeaders() as $k => $v) {
            $out[] = $k . ': ' . $v;
        }
        return $out;
    }
}
