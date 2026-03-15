<?php
/**
 * Controller de Chamados - ações AJAX para o Kanban (mover entre etapas).
 */

namespace App\Controllers;

use App\Core\Container;

class ChamadoController extends BaseController
{
    /**
     * Move um chamado para outra etapa (chamado via AJAX no drag-and-drop).
     * Espera: POST ou GET com id e etapa (ou nova_etapa).
     */
    public function mover(): void
    {
        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $etapa = (string) ($_POST['etapa'] ?? $_POST['nova_etapa'] ?? $_GET['etapa'] ?? '');
        $etapa = trim($etapa);
        if ($id < 1 || $etapa === '') {
            $this->json(['sucesso' => false, 'mensagem' => 'Dados inválidos.']);
            return;
        }
        $model = Container::getChamadoModel();
        $resultado = $model->atualizarEtapa($id, $etapa);
        $this->json($resultado);
    }
}
