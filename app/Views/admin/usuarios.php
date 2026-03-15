<?php
/**
 * Página de gestão de permissões - lista usuários aguardando e com acesso.
 */
$titulo = $titulo ?? 'Permissões';
$aguardando = $aguardando ?? [];
$comPermissao = $comPermissao ?? [];
$flash = $_SESSION['flash_sucesso'] ?? '';
unset($_SESSION['flash_sucesso']);
ob_start();
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-people me-2"></i><?= htmlspecialchars($titulo) ?></h1>
    </div>
    <?php if ($flash): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-hourglass-split me-2"></i>Aguardando liberação (<?= count($aguardando) ?>)
                </div>
                <div class="card-body">
                    <?php if (empty($aguardando)): ?>
                    <p class="text-muted mb-0">Nenhum usuário aguardando.</p>
                    <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($aguardando as $u): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($u['nome']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                            </div>
                            <a href="<?= BASE_URL ?>/admin/liberar/<?= (int)$u['id'] ?>" class="btn btn-sm btn-success">Liberar</a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-check-circle me-2"></i>Com acesso (<?= count($comPermissao) ?>)
                </div>
                <div class="card-body">
                    <?php if (empty($comPermissao)): ?>
                    <p class="text-muted mb-0">Nenhum usuário com permissão.</p>
                    <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($comPermissao as $u): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($u['nome']) ?></strong>
                            <?php if (!empty($u['admin'])): ?><span class="badge bg-primary ms-2">Admin</span><?php endif; ?>
                            <br><small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$conteudo = ob_get_clean();
require VIEWS_PATH . '/layout.php';
