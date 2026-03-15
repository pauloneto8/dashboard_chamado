<?php
/**
 * Página de login - redireciona para Bitrix24 ou exibe botão de entrada.
 * Quando BITRIX24_CLIENT_ID está configurado, o controller já redireciona; esta view é fallback.
 */
$titulo = $titulo ?? 'Login';
$erro = $erro ?? '';
ob_start();
?>
<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-sm" style="max-width: 400px;">
        <div class="card-body p-4 text-center">
            <h1 class="h4 mb-4"><i class="bi bi-kanban text-primary me-2"></i>Dashboard Chamados</h1>
            <?php if ($erro): ?>
            <div class="alert alert-danger small"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            <p class="text-muted small mb-4">Entre com sua conta Bitrix24 para acessar o dashboard.</p>
            <a href="<?= BASE_URL ?>/auth/login" class="btn btn-primary btn-lg w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar com Bitrix24
            </a>
        </div>
    </div>
</div>
<?php
$conteudo = ob_get_clean();
require VIEWS_PATH . '/layout.php';
