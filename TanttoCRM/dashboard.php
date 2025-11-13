<?php
session_start();
// DEBUG: habilitado localmente para diagnóstico, remova em produção
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'conexao.php';

// Estatísticas básicas simples
$totalClientes = (int)$pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$totalNeg = (int)$pdo->query("SELECT COUNT(*) FROM negociacoes")->fetchColumn();

$emAndamento = $pdo->prepare("SELECT COUNT(*) FROM negociacoes WHERE status = 'Em andamento'");
$emAndamento->execute();
$emAndamentoCount = (int)$emAndamento->fetchColumn();

$pageTitle = 'Painel - Tantto CRM';
require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/header.php';
?>

    <h1>Painel de Controle</h1>

    <div class="grid" style="margin-top: 24px;">
        <div class="card">
            <h2>Total de Clientes</h2>
            <div class="big-number"><?= $totalClientes ?></div>
            <a href="clientes/listar.php" class="button" style="margin-top:16px;">Ver Lista</a>
        </div>

        <div class="card">
            <h2>Total de Negociações</h2>
            <div class="big-number"><?= $totalNeg ?></div>
            <a href="negociacoes/listar.php" class="button" style="margin-top:16px;">Ver Lista</a>
        </div>

        <div class="card">
            <h2>Em Andamento</h2>
            <div class="big-number"><?= $emAndamentoCount ?></div>
        </div>
    </div>

    <!-- Gráfico de linha: comparação rápida (Clientes / Negociações / Em Andamento) -->
    <div class="grid" style="margin-top:18px;">
        <div class="card" style="grid-column:1 / -1;">
            <h2>Resumo</h2>
            <div class="chart-wrap" style="height:260px;">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart.js via CDN (carregamos aqui para garantir que o canvas exista) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function(){
        try {
            const totalClientes = <?= json_encode($totalClientes) ?>;
            const totalNeg = <?= json_encode($totalNeg) ?>;
            const emAndamento = <?= json_encode($emAndamentoCount) ?>;

            const ctx = document.getElementById('lineChart').getContext('2d');
            // Desenha um gráfico simples de linha com três pontos para comparação rápida
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Clientes', 'Negociações', 'Em Andamento'],
                    datasets: [{
                        label: 'Quantidade',
                        data: [totalClientes, totalNeg, emAndamento],
                        borderColor: 'rgba(0, 191, 255, 0.95)',
                        backgroundColor: 'rgba(0, 191, 255, 0.12)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 5,
                        pointBackgroundColor: 'rgba(0, 116, 217, 0.9)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        } catch (err) {
            // falha silenciosa: se Chart.js não carregar por alguma razão, não quebremos a página
            console.error('Erro ao inicializar gráfico:', err);
        }
    })();
    </script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/footer.php'; ?>
