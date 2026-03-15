<?php
/**
 * Model de Chamados - CRUD e listagem por etapa.
 * Pode usar cache local (tabela chamados) ou apenas servir dados da API GLPI.
 */

namespace App\Models;

use App\Services\GlpiApiService;
use PDO;

class ChamadoModel
{
    private PDO $pdo;
    private GlpiApiService $glpi;

    public function __construct(PDO $pdo, GlpiApiService $glpi)
    {
        $this->pdo = $pdo;
        $this->glpi = $glpi;
    }

    /**
     * Lista chamados para exibição no Kanban.
     * Por padrão busca da API GLPI; pode ser estendido para usar cache local.
     * @return array<int, array<string, mixed>>
     */
    public function listarParaKanban(): array
    {
        try {
            return $this->glpi->listarChamados();
        } catch (\Throwable $e) {
            error_log('ChamadoModel::listarParaKanban - ' . $e->getMessage());
            try {
                return $this->listarDoCacheLocal();
            } catch (\Throwable $e2) {
                return [];
            }
        }
    }

    /**
     * Lista chamados da tabela local (cache), quando a API não estiver disponível.
     * @return array<int, array<string, mixed>>
     */
    public function listarDoCacheLocal(): array
    {
        $sql = 'SELECT id, glpi_id, titulo, etapa, solicitante, data_abertura FROM chamados ORDER BY data_abertura DESC';
        $stmt = $this->pdo->query($sql);
        if ($stmt === false) {
            return [];
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['status'] = $row['etapa'];
        }
        return $rows;
    }

    /**
     * Atualiza a etapa de um chamado: tenta na API GLPI e, se existir, no cache local.
     * @return array{sucesso: bool, mensagem?: string}
     */
    public function atualizarEtapa(int $chamadoId, string $novaEtapa): array
    {
        $etapasValidas = ['Novo', 'Programado', 'Pendente', 'Solucionado'];
        if (!in_array($novaEtapa, $etapasValidas, true)) {
            return ['sucesso' => false, 'mensagem' => 'Etapa inválida.'];
        }
        try {
            $this->glpi->atualizarStatusTicket($chamadoId, $novaEtapa);
        } catch (\Throwable $e) {
            error_log('ChamadoModel::atualizarEtapa GLPI - ' . $e->getMessage());
            // Continua para atualizar cache local se existir
        }
        try {
            $stmt = $this->pdo->prepare('UPDATE chamados SET etapa = ?, sincronizado_em = NOW() WHERE id = ?');
            $stmt->execute([$novaEtapa, $chamadoId]);
        } catch (\Throwable $e) {
            // Tabela pode não existir ou chamado não estar em cache
        }
        return ['sucesso' => true];
    }
}
