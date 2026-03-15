<?php
/**
 * Model de Usuários - CRUD e regras de permissão.
 * Primeiro usuário (por ordem de criação) é considerado admin.
 * Demais usuários precisam ser liberados pelo admin na tabela permissoes.
 */

namespace App\Models;

use PDO;

class UsuarioModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Cria ou atualiza usuário a partir dos dados do Bitrix24.
     * Se for o primeiro usuário do sistema, define admin=1.
     * @return array{id: int, admin: int, nome: string}
     */
    public function criarOuAtualizar(string $bitrixId, string $email, string $nome): array
    {
        $sql = 'SELECT id, admin FROM usuarios WHERE bitrix_id = ? LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$bitrixId]);
        $existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            $stmt = $this->pdo->prepare('UPDATE usuarios SET email = ?, nome = ?, atualizado_em = NOW() WHERE id = ?');
            $stmt->execute([$email, $nome, $existente['id']]);
            return [
                'id'   => (int) $existente['id'],
                'admin' => (int) $existente['admin'],
                'nome' => $nome,
            ];
        }

        $ehPrimeiro = $this->ehPrimeiroUsuario();
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (bitrix_id, email, nome, admin, ativo) VALUES (?, ?, ?, ?, 1)'
        );
        $stmt->execute([$bitrixId, $email, $nome, $ehPrimeiro ? 1 : 0]);
        $id = (int) $this->pdo->lastInsertId();
        return [
            'id'   => $id,
            'admin' => $ehPrimeiro ? 1 : 0,
            'nome' => $nome,
        ];
    }

    /**
     * Verifica se ainda não existe nenhum usuário (primeiro acesso).
     */
    public function ehPrimeiroUsuario(): bool
    {
        $stmt = $this->pdo->query('SELECT 1 FROM usuarios LIMIT 1');
        return $stmt && $stmt->fetch() === false;
    }

    /**
     * Verifica se o usuário tem permissão para acessar o dashboard (é admin ou está em permissoes).
     */
    public function temPermissao(int $usuarioId): bool
    {
        $stmt = $this->pdo->prepare('SELECT admin FROM usuarios WHERE id = ? AND ativo = 1');
        $stmt->execute([$usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        if ((int) $row['admin'] === 1) {
            return true;
        }
        $stmt = $this->pdo->prepare('SELECT 1 FROM permissoes WHERE usuario_id = ? LIMIT 1');
        $stmt->execute([$usuarioId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Retorna usuário por ID.
     * @return array<string, mixed>|null
     */
    public function obterPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, bitrix_id, email, nome, admin, ativo FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Lista usuários que já fizeram login mas ainda não têm permissão (não são admin e não estão em permissoes).
     * Para o admin liberar acesso.
     * @return array<int, array<string, mixed>>
     */
    public function listarSemPermissao(): array
    {
        $sql = "SELECT u.id, u.bitrix_id, u.email, u.nome, u.criado_em
                FROM usuarios u
                WHERE u.admin = 0 AND u.ativo = 1
                AND NOT EXISTS (SELECT 1 FROM permissoes p WHERE p.usuario_id = u.id)
                ORDER BY u.criado_em DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Concede permissão de acesso a um usuário (inserindo em permissoes).
     */
    public function liberarAcesso(int $usuarioId, int $liberadoPor): bool
    {
        $stmt = $this->pdo->prepare('INSERT IGNORE INTO permissoes (usuario_id, liberado_por) VALUES (?, ?)');
        $stmt->execute([$usuarioId, $liberadoPor]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Lista todos os usuários com permissão (para o admin gerenciar).
     * @return array<int, array<string, mixed>>
     */
    public function listarComPermissao(): array
    {
        $sql = "SELECT u.id, u.email, u.nome, u.admin, p.criado_em AS liberado_em
                FROM usuarios u
                LEFT JOIN permissoes p ON p.usuario_id = u.id
                WHERE u.ativo = 1
                ORDER BY u.admin DESC, u.nome";
        $stmt = $this->pdo->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
