<?php
/**
 * Controller do Dashboard - Kanban de chamados.
 * Exibe os chamados nas colunas: Novo, Programado, Pendente, Solucionado.
 */

namespace App\Controllers;

use App\Core\Container;

class DashboardController extends BaseController
{
    /**
     * Página principal do Kanban.
     */
    public function index(): void
    {
        $chamados = [];
        try {
            $chamados = Container::getChamadoModel()->listarParaKanban();
        } catch (\Throwable $e) {
            // Mantém array vazio em caso de erro (ex: banco não criado ainda)
        }
        $dados = [
            'titulo'   => 'Dashboard de Chamados',
            'etapas'   => ['Novo', 'Programado', 'Pendente', 'Solucionado'],
            'chamados' => $chamados,
        ];
        $this->view('dashboard.index', $dados);
    }
}
