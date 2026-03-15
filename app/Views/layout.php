<?php
/**
 * Layout principal da aplicação.
 * Inclui Bootstrap 5 e estrutura base responsiva.
 * Variáveis esperadas: $titulo, $conteudo (ou conteúdo via buffer)
 */
$titulo = $titulo ?? 'Dashboard de Chamados';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard de Chamados - Gestão à vista do time de suporte">
    <title><?= htmlspecialchars($titulo) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --kanban-novo: #0d6efd;
            --kanban-programado: #fd7e14;
            --kanban-pendente: #ffc107;
            --kanban-solucionado: #198754;
        }
        body { background: #f8f9fa; min-height: 100vh; }
        .navbar-brand { font-weight: 700; }
        .coluna-kanban {
            min-height: 70vh;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }
        .coluna-kanban .card-header {
            border-radius: 12px 12px 0 0;
            font-weight: 600;
            color: #fff;
        }
        .coluna-kanban .card { border: none; border-radius: 8px; transition: transform .15s; }
        .coluna-kanban .card:hover { transform: translateY(-2px); }
        .card-chamado { cursor: grab; }
        .card-chamado:active { cursor: grabbing; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>">
                <i class="bi bi-kanban me-2"></i>Dashboard Chamados
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>"><i class="bi bi-house me-1"></i>Início</a>
                    </li>
                    <?php if (!empty($_SESSION['usuario_id'])): ?>
                    <?php if (!empty($_SESSION['usuario_admin'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/usuarios"><i class="bi bi-people me-1"></i>Permissões</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/auth/login">Entrar</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container-fluid py-4">
        <?php
        // Conteúdo da página (injetado pela view específica)
        if (isset($conteudo)) {
            echo $conteudo;
        }
        ?>
    </main>

    <footer class="py-3 text-center text-muted small">
        Dashboard de Chamados &copy; <?= date('Y') ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (!empty($scripts)): ?>
        <?= $scripts ?>
    <?php endif; ?>
</body>
</html>
