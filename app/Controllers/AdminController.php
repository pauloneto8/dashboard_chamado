<?php
/**
 * Controller de administração - Gestão de permissões de usuários.
 * Apenas usuários com perfil admin podem acessar.
 */

namespace App\Controllers;

use App\Core\Container;

class AdminController extends BaseController
{
    /**
     * Verifica se o usuário logado é admin; caso contrário, redireciona.
     */
    private function exigirAdmin(): void
    {
        if (!\App\Controllers\AuthController::isAdmin()) {
            $this->redirecionar(BASE_URL);
            exit;
        }
    }

    /**
     * Lista usuários com e sem permissão; admin pode liberar quem está aguardando.
     */
    public function usuarios(): void
    {
        $this->exigirAdmin();
        $userModel = Container::getUsuarioModel();
        $dados = [
            'titulo'           => 'Permissões de usuários',
            'aguardando'       => $userModel->listarSemPermissao(),
            'comPermissao'     => $userModel->listarComPermissao(),
        ];
        $this->view('admin.usuarios', $dados);
    }

    /**
     * Libera acesso a um usuário (action via GET ou POST por simplicidade).
     * @param string $id ID do usuário a ser liberado (da URL)
     */
    public function liberar(string $id): void
    {
        $this->exigirAdmin();
        $usuarioId = (int) $id;
        $liberadoPor = \App\Controllers\AuthController::getUsuarioId();
        if ($usuarioId < 1 || !$liberadoPor) {
            $this->redirecionar(BASE_URL . '/admin/usuarios');
            return;
        }
        $userModel = Container::getUsuarioModel();
        $userModel->liberarAcesso($usuarioId, $liberadoPor);
        $_SESSION['flash_sucesso'] = 'Acesso liberado com sucesso.';
        $this->redirecionar(BASE_URL . '/admin/usuarios');
    }
}
