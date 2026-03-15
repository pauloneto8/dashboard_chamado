<?php
/**
 * Exibida quando o usuário já fez login no Bitrix24 mas ainda não foi liberado pelo admin.
 */
$titulo = $titulo ?? 'Aguardando liberação';
$nome = $nome ?? 'Usuário';
ob_start();
?>
<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-sm" style="max-width: 480px;">
        <div class="card-body p-4 text-center">
            <div class="mb-3"><i class="bi bi-hourglass-split display-4 text-warning"></i></div>
            <h1 class="h4 mb-2">Aguardando liberação</h1>
            <p class="text-muted mb-0">Olá, <strong><?= htmlspecialchars($nome) ?></strong>. Seu acesso ao Dashboard de Chamados ainda não foi aprovado. Entre em contato com o administrador para liberar seu usuário.</p>
            <hr>
            <a href="<?= BASE_URL ?>/auth/logout" class="btn btn-outline-secondary">Sair</a>
        </div>
    </div>
</div>
<?php
$conteudo = ob_get_clean();
require VIEWS_PATH . '/layout.php';
