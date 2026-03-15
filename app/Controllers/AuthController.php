<?php
/**
 * Controller de autenticação - Login via Bitrix24 e gestão de sessão.
 * Callback OAuth, logout e página "aguardando liberação" para usuários não aprovados.
 */

namespace App\Controllers;

use App\Core\Container;
use App\Services\Bitrix24AuthService;

class AuthController extends BaseController
{
    private function getBitrixAuth(): Bitrix24AuthService
    {
        return new Bitrix24AuthService(
            BITRIX24_DOMAIN,
            BITRIX24_CLIENT_ID,
            BITRIX24_CLIENT_SECRET,
            BITRIX24_REDIRECT_URI
        );
    }

    /**
     * Exibe a tela de login ou redireciona para o Bitrix24.
     * Se houver erro em sessão ou Bitrix24 não configurado, exibe a view.
     */
    public function login(): void
    {
        if (self::usuarioLogado()) {
            $this->redirecionar(BASE_URL);
            return;
        }
        if (!empty($_SESSION['auth_erro']) || !BITRIX24_CLIENT_ID) {
            $dados = ['titulo' => 'Login', 'erro' => $_SESSION['auth_erro'] ?? (BITRIX24_CLIENT_ID ? '' : 'Bitrix24 não configurado. Defina BITRIX24_CLIENT_ID e BITRIX24_CLIENT_SECRET em config/config.php.')];
            $this->view('auth.login', $dados);
            return;
        }
        $auth = $this->getBitrixAuth();
        header('Location: ' . $auth->getAuthorizeUrl());
        exit;
    }

    /**
     * Callback OAuth - recebe o code do Bitrix24, troca por token e registra/atualiza usuário.
     */
    public function callback(): void
    {
        $code = $_GET['code'] ?? '';
        $domain = $_GET['domain'] ?? null;
        if ($code === '') {
            $_SESSION['auth_erro'] = 'Código de autorização não recebido.';
            $this->redirecionar(BASE_URL . '/auth/login');
            return;
        }

        try {
            $auth = $this->getBitrixAuth();
            $dados = $auth->trocarCodePorUsuario($code, $domain);
        } catch (\Throwable $e) {
            $_SESSION['auth_erro'] = $e->getMessage();
            $this->redirecionar(BASE_URL . '/auth/login');
            return;
        }

        $userModel = Container::getUsuarioModel();
        $usuario = $userModel->criarOuAtualizar(
            (string) $dados['user_id'],
            $dados['email'],
            $dados['name']
        );

        if (!$userModel->temPermissao($usuario['id'])) {
            $_SESSION['usuario_aguardando'] = $usuario;
            $_SESSION['usuario_id'] = null;
            $this->redirecionar(BASE_URL . '/auth/aguardando');
            return;
        }

        $this->iniciarSessaoUsuario($usuario['id'], $usuario['nome'], (bool) $usuario['admin']);
        $this->redirecionar(BASE_URL);
    }

    /**
     * Página exibida quando o usuário já fez login no Bitrix24 mas ainda não foi liberado pelo admin.
     */
    public function aguardando(): void
    {
        if (self::usuarioLogado()) {
            $this->redirecionar(BASE_URL);
            return;
        }
        $dados = [
            'titulo' => 'Aguardando liberação',
            'nome'   => $_SESSION['usuario_aguardando']['nome'] ?? 'Usuário',
        ];
        $this->view('auth.aguardando', $dados);
    }

    /**
     * Encerra a sessão e redireciona para login.
     */
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->redirecionar(BASE_URL . '/auth/login');
    }

    /**
     * Verifica se há usuário logado (session com usuario_id).
     */
    public static function usuarioLogado(): bool
    {
        return !empty($_SESSION['usuario_id']);
    }

    /**
     * Retorna o ID do usuário logado ou null.
     * @return int|null
     */
    public static function getUsuarioId(): ?int
    {
        return isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;
    }

    /**
     * Retorna se o usuário logado é admin.
     */
    public static function isAdmin(): bool
    {
        return !empty($_SESSION['usuario_admin']);
    }

    private function iniciarSessaoUsuario(int $id, string $nome, bool $admin): void
    {
        $_SESSION['usuario_id'] = $id;
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_admin'] = $admin;
        unset($_SESSION['usuario_aguardando'], $_SESSION['auth_erro']);
    }
}
