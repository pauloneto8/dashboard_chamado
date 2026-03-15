<?php
/**
 * View principal do Dashboard - Kanban com 4 colunas.
 * Etapas: Novo, Programado, Pendente, Solucionado.
 */
$etapas = $etapas ?? ['Novo', 'Programado', 'Pendente', 'Solucionado'];
$chamados = $chamados ?? [];
$cores = [
    'Novo'        => 'bg-primary',
    'Programado'  => 'bg-warning text-dark',
    'Pendente'    => 'bg-info text-dark',
    'Solucionado' => 'bg-success',
];
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-kanban me-2"></i><?= htmlspecialchars($titulo ?? 'Dashboard de Chamados') ?></h1>
</div>

<div class="row g-3" id="kanban-board">
    <?php foreach ($etapas as $etapa): ?>
    <div class="col-12 col-md-6 col-xl-3 coluna-kanban-wrapper" data-etapa="<?= htmlspecialchars($etapa) ?>">
        <div class="coluna-kanban card h-100">
            <div class="card-header <?= $cores[$etapa] ?? 'bg-secondary' ?> py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span><?= htmlspecialchars($etapa) ?></span>
                    <span class="badge bg-light text-dark contador">0</span>
                </div>
            </div>
            <div class="card-body p-2 coluna-cards droppable" data-etapa="<?= htmlspecialchars($etapa) ?>">
                <?php
                $chamadosEtapa = array_filter($chamados, fn($c) => ($c['etapa'] ?? $c['status'] ?? '') === $etapa);
                foreach ($chamadosEtapa as $chamado):
                    $id = (int)($chamado['id'] ?? 0);
                    $tituloChamado = htmlspecialchars($chamado['titulo'] ?? $chamado['name'] ?? 'Chamado #' . $id);
                ?>
                <div class="card card-chamado mb-2 shadow-sm draggable" data-id="<?= $id ?>" draggable="true">
                    <div class="card-body py-2 px-3">
                        <small class="text-muted">#<?= $id ?></small>
                        <div class="fw-semibold"><?= $tituloChamado ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($chamadosEtapa)): ?>
                <p class="text-muted small text-center py-3 mb-0 placeholder-nenhum">Nenhum chamado</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
(function() {
    var baseUrl = '<?= BASE_URL ?>';
    // Contadores
    function atualizarContadores() {
        document.querySelectorAll('.coluna-cards').forEach(function(col) {
            var cards = col.querySelectorAll('.card-chamado');
            var contador = col.closest('.coluna-kanban').querySelector('.contador');
            var placeholder = col.querySelector('.placeholder-nenhum');
            if (contador) contador.textContent = cards.length;
            if (placeholder) placeholder.style.display = cards.length ? 'none' : 'block';
        });
    }
    atualizarContadores();

    var cardArrastado = null;
    var colunaOrigem = null;

    document.querySelectorAll('.draggable').forEach(function(card) {
        card.addEventListener('dragstart', function(e) {
            cardArrastado = card;
            colunaOrigem = card.closest('.coluna-cards');
            e.dataTransfer.setData('text/plain', card.dataset.id);
            e.dataTransfer.effectAllowed = 'move';
            card.classList.add('opacity-50');
        });
        card.addEventListener('dragend', function() {
            if (cardArrastado) cardArrastado.classList.remove('opacity-50');
            cardArrastado = null;
            colunaOrigem = null;
        });
    });

    document.querySelectorAll('.droppable').forEach(function(col) {
        col.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            col.classList.add('bg-light');
        });
        col.addEventListener('dragleave', function() {
            col.classList.remove('bg-light');
        });
        col.addEventListener('drop', function(e) {
            e.preventDefault();
            col.classList.remove('bg-light');
            if (!cardArrastado) return;
            var id = cardArrastado.dataset.id;
            var novaEtapa = col.dataset.etapa;
            if (col === colunaOrigem) return;
            var formData = new FormData();
            formData.append('id', id);
            formData.append('etapa', novaEtapa);
            fetch(baseUrl + '/chamado/mover', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(r) { return r.json(); }).then(function(res) {
                if (res.sucesso) {
                    col.querySelector('.placeholder-nenhum') && (col.querySelector('.placeholder-nenhum').style.display = 'none');
                    col.appendChild(cardArrastado);
                    atualizarContadores();
                } else {
                    alert(res.mensagem || 'Erro ao mover.');
                }
            }).catch(function() {
                alert('Erro ao comunicar com o servidor.');
            });
        });
    });
})();
</script>
<?php
$conteudo = ob_get_clean();
require VIEWS_PATH . '/layout.php';
