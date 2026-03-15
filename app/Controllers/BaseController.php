<?php
/**
 * Controller base - métodos comuns a todos os controllers.
 * Responsável por carregar views e dados compartilhados.
 */

namespace App\Controllers;

abstract class BaseController
{
    /**
     * Carrega uma view e passa os dados para ela.
     * @param string $view Nome do arquivo da view (sem .php)
     * @param array<string, mixed> $dados Dados a serem disponibilizados na view
     */
    protected function view(string $view, array $dados = []): void
    {
        extract($dados);
        $arquivo = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($arquivo)) {
            require $arquivo;
        } else {
            http_response_code(500);
            echo 'View não encontrada: ' . htmlspecialchars($view);
        }
    }

    /**
     * Redireciona para outra URL.
     */
    protected function redirecionar(string $url, int $codigo = 302): void
    {
        header('Location: ' . $url, true, $codigo);
        exit;
    }

    /**
     * Retorna JSON e encerra a execução.
     * @param mixed $dados
     */
    protected function json($dados): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
