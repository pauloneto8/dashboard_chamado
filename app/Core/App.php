<?php
/**
 * Classe principal da aplicação - Roteador e despacho de requisições.
 * Interpreta a URL e chama o controller e a ação correspondentes.
 */

namespace App\Core;

class App
{
    /** @var string Controller padrão quando nenhum é informado */
    private string $controllerPadrao = 'Dashboard';

    /** @var string Ação padrão (método do controller) */
    private string $acaoPadrao = 'index';

    /** Rotas que não exigem autenticação nem permissão */
    private const ROTAS_PUBLICAS = [
        'Auth' => ['login', 'callback', 'aguardando'],
    ];

    /**
     * Processa a requisição e executa o controller/action.
     */
    public function executar(): void
    {
        $url = $this->obterUrl();

        $controllerNome = $this->controllerPadrao;
        $acao = $this->acaoPadrao;
        $parametros = [];

        if (!empty($url[0])) {
            $controllerNome = $this->sanitizarNomeController($url[0]);
        }
        if (isset($url[1]) && $url[1] !== '') {
            $acao = $this->sanitizarNomeAcao($url[1]);
        }
        if (count($url) > 2) {
            $parametros = array_slice($url, 2);
        }

        // Exige autenticação e permissão para rotas não públicas
        if (!$this->rotaPublica($controllerNome, $acao)) {
            $this->exigirAuthOuRedirecionar($controllerNome, $acao);
        }

        $classeController = "App\\Controllers\\{$controllerNome}Controller";
        if (!class_exists($classeController)) {
            $this->erro404();
            return;
        }

        $controller = new $classeController();
        if (!method_exists($controller, $acao)) {
            $this->erro404();
            return;
        }

        call_user_func_array([$controller, $acao], $parametros);
    }

    private function rotaPublica(string $controller, string $acao): bool
    {
        $acoes = self::ROTAS_PUBLICAS[$controller] ?? null;
        return $acoes !== null && in_array($acao, $acoes, true);
    }

    private function exigirAuthOuRedirecionar(string $controllerNome, string $acao): void
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if (empty($usuarioId)) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        $userModel = \App\Core\Container::getUsuarioModel();
        if (!$userModel->temPermissao((int) $usuarioId)) {
            $_SESSION['usuario_aguardando'] = $userModel->obterPorId((int) $usuarioId);
            header('Location: ' . BASE_URL . '/auth/aguardando');
            exit;
        }
    }

    /**
     * Retorna a URL como array a partir do parâmetro 'url' ou PATH_INFO.
     * @return array<string>
     */
    private function obterUrl(): array
    {
        $url = $_GET['url'] ?? $_SERVER['PATH_INFO'] ?? '';
        $url = filter_var(trim($url, '/'), FILTER_SANITIZE_URL);
        return $url ? explode('/', $url) : [];
    }

    /**
     * Converte o segmento da URL em nome de classe (PascalCase).
     */
    private function sanitizarNomeController(string $nome): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $nome)));
    }

    /**
     * Converte o segmento da URL em nome de método (camelCase).
     */
    private function sanitizarNomeAcao(string $nome): string
    {
        $nome = str_replace('-', ' ', $nome);
        return lcfirst(str_replace(' ', '', ucwords($nome)));
    }

    /**
     * Responde com HTTP 404.
     */
    private function erro404(): void
    {
        http_response_code(404);
        echo '<h1>404 - Página não encontrada</h1>';
    }
}
