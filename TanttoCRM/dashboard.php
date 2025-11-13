<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'conexao.php';

// ===== ESTATÍSTICAS BÁSICAS =====
$totalClientes = (int)$pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$totalNeg = (int)$pdo->query("SELECT COUNT(*) FROM negociacoes")->fetchColumn();

$emAndamento = $pdo->prepare("SELECT COUNT(*) FROM negociacoes WHERE status IN ('Prospecção', 'Qualificação', 'Proposta', 'Negociação')");
$emAndamento->execute();
$emAndamentoCount = (int)$emAndamento->fetchColumn();

$concluida = $pdo->prepare("SELECT COUNT(*) FROM negociacoes WHERE status = 'Fechamento'");
$concluida->execute();
$concluidaCount = (int)$concluida->fetchColumn();

$perdida = $pdo->prepare("SELECT COUNT(*) FROM negociacoes WHERE status = 'Perdida'");
$perdida->execute();
$perdidaCount = (int)$perdida->fetchColumn();

// ===== FATURAMENTO =====
$faturamentoTotal = $pdo->query("SELECT COALESCE(SUM(valor), 0) FROM negociacoes WHERE status = 'Fechamento'")->fetchColumn();
$faturamentoTotal = (float)$faturamentoTotal;

$faturamentoAndamento = $pdo->query("SELECT COALESCE(SUM(valor), 0) FROM negociacoes WHERE status IN ('Prospecção', 'Qualificação', 'Proposta', 'Negociação')")->fetchColumn();
$faturamentoAndamento = (float)$faturamentoAndamento;

// ===== TAXA DE CONVERSÃO =====
$clientesComNeg = $pdo->query("SELECT COUNT(DISTINCT cliente_id) FROM negociacoes WHERE cliente_id IS NOT NULL")->fetchColumn();
$clientesComNeg = (int)$clientesComNeg;
$taxaConversao = $totalClientes > 0 ? round(($clientesComNeg / $totalClientes) * 100, 1) : 0;

// ===== RANKING TOP 5 CLIENTES POR VALOR =====
$topClientesValor = $pdo->query("
    SELECT c.nome, SUM(n.valor) as total_valor, COUNT(n.id_negociacao) as total_negs
    FROM clientes c
    LEFT JOIN negociacoes n ON c.id_cliente = n.cliente_id
    GROUP BY c.id_cliente, c.nome
    ORDER BY total_valor DESC
    LIMIT 5
")->fetchAll();

// ===== RANKING TOP 5 CLIENTES POR QUANTIDADE =====
$topClientesQtd = $pdo->query("
    SELECT c.nome, COUNT(n.id_negociacao) as total_negs
    FROM clientes c
    LEFT JOIN negociacoes n ON c.id_cliente = n.cliente_id
    GROUP BY c.id_cliente, c.nome
    ORDER BY total_negs DESC
    LIMIT 5
")->fetchAll();

// ===== DADOS PARA GRÁFICOS =====
$statusLabels = ['Prospecção', 'Qualificação', 'Proposta', 'Negociação', 'Fechamento', 'Perdida'];
$statusCounts = [
    (int)$pdo->query("SELECT COUNT(*) FROM negociacoes WHERE status = 'Prospecção'")->fetchColumn(),
    (int)$pdo->query("SELECT COUNT(*) FROM negociacoes WHERE status = 'Qualificação'")->fetchColumn(),
    (int)$pdo->query("SELECT COUNT(*) FROM negociacoes WHERE status = 'Proposta'")->fetchColumn(),
    (int)$pdo->query("SELECT COUNT(*) FROM negociacoes WHERE status = 'Negociação'")->fetchColumn(),
    $concluidaCount,
    $perdidaCount
];

$topClientesNomes = array_map(fn($c) => $c['nome'], $topClientesQtd);
$topClientesQtds = array_map(fn($c) => (int)$c['total_negs'], $topClientesQtd);

$pageTitle = 'Painel - Tantto CRM';
require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/header.php';
?>

    <h1>Painel de Controle</h1>

    <!-- KPIs Principais -->
    <div class="grid" style="margin-top: 24px;">
        <div class="card">
            <h2>Total de Clientes</h2>
            <div class="big-number"><?= $totalClientes ?></div>
            <p style="color:var(--muted); font-size:14px; margin-top:8px;"><?= $clientesComNeg ?> com negociações</p>
            <a href="clientes/listar.php" class="button" style="margin-top:12px;">Ver Lista</a>
        </div>

        <div class="card">
            <h2>Total de Negociações</h2>
            <div class="big-number"><?= $totalNeg ?></div>
            <p style="color:var(--muted); font-size:14px; margin-top:8px;"><span style="color:var(--accent);">✓</span> <?= $concluidaCount ?> concluídas</p>
            <a href="negociacoes/listar.php" class="button" style="margin-top:12px;">Ver Lista</a>
        </div>

        <div class="card">
            <h2>Faturamento Realizado</h2>
            <div class="big-number" style="font-size:28px;">R$ <?= number_format($faturamentoTotal, 2, ',', '.') ?></div>
            <p style="color:var(--muted); font-size:14px; margin-top:8px;">Em andamento: R$ <?= number_format($faturamentoAndamento, 2, ',', '.') ?></p>
        </div>

        <div class="card">
            <h2>Taxa de Conversão</h2>
            <div class="big-number" style="font-size:32px; color:var(--accent);"><?= $taxaConversao ?>%</div>
            <p style="color:var(--muted); font-size:14px; margin-top:8px;"><?= $clientesComNeg ?> de <?= $totalClientes ?> clientes</p>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid" style="margin-top:24px;">
        <!-- Status das Negociações -->
        <div class="card">
            <h2>Pipeline de Vendas</h2>
            <div class="chart-wrap" style="height:280px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Top 5 Clientes por Quantidade -->
        <div class="card">
            <h2>Top 5 Clientes</h2>
            <div class="chart-wrap" style="height:280px;">
                <canvas id="topClientesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Resumo de Valores -->
    <div class="grid" style="margin-top:24px;">
        <div class="card" style="grid-column:1 / -1;">
            <h2>Comparativo de Faturamento</h2>
            <div class="chart-wrap" style="height:260px;">
                <canvas id="faturamentoChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top 5 Clientes por Faturamento -->
    <div class="grid" style="margin-top:24px;">
        <div class="card" style="grid-column:1 / -1;">
            <h2>Ranking de Clientes por Faturamento</h2>
            <table class="table" style="margin-top:12px;">
                <thead>
                    <tr>
                        <th>Posição</th>
                        <th>Cliente</th>
                        <th>Faturamento Total</th>
                        <th>Negociações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($topClientesValor as $idx => $cliente): ?>
                        <tr>
                            <td><strong><?= $idx + 1 ?></strong></td>
                            <td><?= htmlspecialchars($cliente['nome']) ?></td>
                            <td style="color:var(--accent);">R$ <?= number_format($cliente['total_valor'] ?? 0, 2, ',', '.') ?></td>
                            <td><?= (int)($cliente['total_negs'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(count($topClientesValor) === 0): ?>
                        <tr><td colspan="4" class="small">Nenhum dado disponível.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart.js via CDN (carregamos aqui para garantir que o canvas exista) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function(){
        try {
            // ===== GRÁFICO 1: STATUS DAS NEGOCIAÇÕES (PIZZA) =====
            const statusLabels = <?= json_encode($statusLabels) ?>;
            const statusCounts = <?= json_encode($statusCounts) ?>;
            const statusColors = ['rgba(0, 191, 255, 0.85)', 'rgba(61, 214, 255, 0.85)', 'rgba(0, 150, 200, 0.85)', 'rgba(0, 100, 150, 0.85)', 'rgba(76, 175, 80, 0.85)', 'rgba(255, 107, 107, 0.85)'];

            const ctxStatus = document.getElementById('statusChart').getContext('2d');
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: statusColors,
                        borderColor: 'rgba(15, 20, 22, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });

            // ===== GRÁFICO 2: TOP 5 CLIENTES (BARRA HORIZONTAL) =====
            const topNomes = <?= json_encode($topClientesNomes) ?>;
            const topQtds = <?= json_encode($topClientesQtds) ?>;

            const ctxTop = document.getElementById('topClientesChart').getContext('2d');
            new Chart(ctxTop, {
                type: 'bar',
                data: {
                    labels: topNomes,
                    datasets: [{
                        label: 'Quantidade de Negociações',
                        data: topQtds,
                        backgroundColor: 'rgba(0, 191, 255, 0.85)',
                        borderColor: 'rgba(0, 116, 217, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });

            // ===== GRÁFICO 3: FATURAMENTO (BARRA VERTICAL) =====
            const faturTotal = <?= json_encode($faturamentoTotal) ?>;
            const faturAndamento = <?= json_encode($faturamentoAndamento) ?>;

            const ctxFaturamento = document.getElementById('faturamentoChart').getContext('2d');
            new Chart(ctxFaturamento, {
                type: 'bar',
                data: {
                    labels: ['Realizado', 'Em Andamento'],
                    datasets: [{
                        label: 'Faturamento (R$)',
                        data: [faturTotal, faturAndamento],
                        backgroundColor: ['rgba(76, 175, 80, 0.85)', 'rgba(255, 193, 7, 0.85)'],
                        borderColor: ['rgba(56, 142, 60, 1)', 'rgba(255, 152, 0, 1)'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                }
                            }
                        }
                    },
                    scales: { y: { beginAtZero: true } }
                }
            });

        } catch (err) {
            console.error('Erro ao inicializar gráficos:', err);
        }
    })();
    </script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/footer.php'; ?>
