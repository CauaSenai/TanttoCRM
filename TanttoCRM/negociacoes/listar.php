<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../index.php'); exit; }
require '../conexao.php';

// Parâmetros de filtro
$busca = trim($_GET['busca'] ?? '');
$filtro_status = trim($_GET['status'] ?? '');
$filtro_cliente = intval($_GET['cliente_id'] ?? 0);
$view_mode = $_GET['view'] ?? 'table'; // 'table' ou 'kanban'

// Construir query dinamicamente
$sql = "SELECT n.*, c.nome AS cliente_nome, u.nome AS usuario_nome
         FROM negociacoes n
         LEFT JOIN clientes c ON n.cliente_id = c.id_cliente
         LEFT JOIN usuarios u ON n.usuario_id = u.id_usuario
         WHERE 1=1";
$params = [];

if($busca) {
    $sql .= " AND (n.titulo LIKE ? OR n.observacoes LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

if($filtro_status) {
    $sql .= " AND n.status = ?";
    $params[] = $filtro_status;
}

if($filtro_cliente) {
    $sql .= " AND n.cliente_id = ?";
    $params[] = $filtro_cliente;
}

$sql .= " ORDER BY n.data_inicio DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$negociacoes = $stmt->fetchAll();

// Pegar lista de status e clientes para filtros
$statuses = ['Prospecção', 'Qualificação', 'Proposta', 'Negociação', 'Fechamento', 'Perdida'];
$clientes = $pdo->query("SELECT id_cliente, nome FROM clientes ORDER BY nome")->fetchAll();

// Para view kanban: agrupar negociações por status
$negsGrouped = [];
if($view_mode === 'kanban') {
    foreach($statuses as $s) {
        $negsGrouped[$s] = array_filter($negociacoes, fn($n) => $n['status'] === $s);
    }
}
?>
<?php
  $pageTitle = 'Negociações - Tantto CRM';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/header.php';
?>
  <!-- CONTEÚDO PRINCIPAL -->
  <div class="content">
    <div class="header">
      <h1>Negociações</h1>
      <a href="cadastrar.php" class="button">Nova Negociação</a>
    </div>

    <!-- Toggle de Visualização -->
    <div class="card" style="margin-bottom:18px; padding:12px; display:flex; gap:12px; align-items:center;">
      <span style="color:var(--muted); font-size:14px;">Visualização:</span>
      <a href="?busca=<?=urlencode($busca)?>&status=<?=urlencode($filtro_status)?>&cliente_id=<?=$filtro_cliente?>&view=table" 
         class="btn <?=($view_mode === 'table') ? 'primary' : 'ghost'?>" style="padding:8px 12px; font-size:13px;">
        📋 Tabela
      </a>
      <a href="?busca=<?=urlencode($busca)?>&status=<?=urlencode($filtro_status)?>&cliente_id=<?=$filtro_cliente?>&view=kanban" 
         class="btn <?=($view_mode === 'kanban') ? 'primary' : 'ghost'?>" style="padding:8px 12px; font-size:13px;">
        📊 Pipeline
      </a>
    </div>

    <!-- Filtros e Busca -->
    <div class="card" style="margin-bottom:18px;">
      <form method="get" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
        <div style="flex:1; min-width:200px;">
          <label style="display:block; color:var(--muted); font-size:13px; margin-bottom:6px;">🔍 Buscar por Título ou Descrição</label>
          <input type="text" name="busca" placeholder="Digite aqui..." value="<?=htmlspecialchars($busca)?>" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.03); background:var(--surface); color:var(--text);">
        </div>

        <div style="flex:0 1 200px;">
          <label style="display:block; color:var(--muted); font-size:13px; margin-bottom:6px;">📊 Filtrar por Status</label>
          <select name="status" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.03); background:var(--surface); color:var(--text);">
            <option value="">— Todos os status —</option>
            <?php foreach($statuses as $st): ?>
              <option value="<?=htmlspecialchars($st)?>" <?=($filtro_status === $st) ? 'selected' : ''?>>
                <?=htmlspecialchars($st)?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="flex:0 1 200px;">
          <label style="display:block; color:var(--muted); font-size:13px; margin-bottom:6px;">👤 Filtrar por Cliente</label>
          <select name="cliente_id" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.03); background:var(--surface); color:var(--text);">
            <option value="">— Todos os clientes —</option>
            <?php foreach($clientes as $cli): ?>
              <option value="<?=$cli['id_cliente']?>" <?=($filtro_cliente === $cli['id_cliente']) ? 'selected' : ''?>>
                <?=htmlspecialchars($cli['nome'])?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn primary" style="padding:10px 20px;">Filtrar</button>
        <?php if($busca || $filtro_status || $filtro_cliente): ?>
          <a href="listar.php" class="btn" style="padding:10px 20px;">Limpar Filtros</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Resultado da busca -->
    <?php if($busca || $filtro_status || $filtro_cliente): ?>
      <div style="margin-bottom:12px; padding:12px; background:rgba(0,191,255,0.1); border-left:3px solid var(--accent); border-radius:6px; color:var(--muted); font-size:14px;">
        <strong>Resultado:</strong> <?=count($negociacoes)?> negociação(ões) encontrada(s)
        <?php if($busca) echo " • Busca: <strong>" . htmlspecialchars($busca) . "</strong>"; ?>
        <?php if($filtro_status) echo " • Status: <strong>" . htmlspecialchars($filtro_status) . "</strong>"; ?>
        <?php if($filtro_cliente) { $cli_nome = array_filter($clientes, fn($c) => $c['id_cliente'] === $filtro_cliente); $cli_nome = reset($cli_nome); echo " • Cliente: <strong>" . htmlspecialchars($cli_nome['nome'] ?? '?') . "</strong>"; } ?>
      </div>
    <?php endif; ?>

    <?php if($view_mode === 'kanban'): ?>
      <!-- VIS ÃO KANBAN / PIPELINE -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:16px; margin-bottom:24px;">
        <?php foreach($statuses as $status): ?>
          <div class="card" style="min-height:400px; padding:12px; background:rgba(255,255,255,0.01); border:1px dashed var(--glass);">
            <h3 style="margin:0 0 12px; font-size:15px; color:var(--accent);">
              <?=htmlspecialchars($status)?> 
              <span style="float:right; color:var(--muted); font-size:13px;">(<?=count($negsGrouped[$status])?> )</span>
            </h3>
            <div style="display:flex; flex-direction:column; gap:12px;">
              <?php foreach($negsGrouped[$status] as $n): ?>
                <div class="kanban-card" style="padding:10px; background:var(--surface); border-radius:6px; border-left:3px solid var(--accent); cursor:pointer; transition:all 0.2s;">
                  <div style="font-weight:600; color:var(--text); font-size:14px; margin-bottom:4px;">
                    <?=htmlspecialchars($n['titulo'])?>
                  </div>
                  <div style="color:var(--muted); font-size:12px; margin-bottom:6px;">
                    <strong><?=htmlspecialchars($n['cliente_nome'])?></strong>
                  </div>
                  <div style="color:var(--accent); font-weight:600; font-size:13px; margin-bottom:8px;">
                    R$ <?=number_format($n['valor'],2,',','.')?>
                  </div>
                  <div style="display:flex; gap:6px;">
                    <a href="editar.php?id=<?=$n['id_negociacao']?>" class="btn" style="flex:1; padding:6px 8px; font-size:12px; text-align:center;">Editar</a>
                  </div>
                </div>
              <?php endforeach; ?>
              <?php if(count($negsGrouped[$status]) === 0): ?>
                <div style="color:var(--muted); font-size:12px; text-align:center; padding:20px 0;">
                  Vazio
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
      <!-- Gráfico de Funil -->
      <div class="card" style="margin-top:24px;">
        <h2>Funil de Vendas</h2>
        <div class="chart-wrap" style="height:280px;">
          <canvas id="funnelChart"></canvas>
        </div>
      </div>

      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <script>
      (function(){
        try {
          const statuses = <?=json_encode($statuses)?>;
          const counts = [<?=implode(',', array_map(fn($s) => 'count($negsGrouped[$s])', $statuses))?>];
          const countsData = <?=json_encode(array_map(fn($s) => count($negsGrouped[$s]), $statuses))?>;
          const colors = ['rgba(0, 191, 255, 0.85)', 'rgba(61, 214, 255, 0.85)', 'rgba(0, 150, 200, 0.85)', 'rgba(0, 100, 150, 0.85)', 'rgba(76, 175, 80, 0.85)', 'rgba(255, 107, 107, 0.85)'];
          
          const ctx = document.getElementById('funnelChart').getContext('2d');
          new Chart(ctx, {
            type: 'bar',
            data: {
              labels: statuses,
              datasets: [{
                label: 'Quantidade de Negociações',
                data: countsData,
                backgroundColor: colors,
                borderColor: 'rgba(15, 20, 22, 1)',
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
        } catch(e) { console.error(e); }
      })();
      </script>

    <?php else: ?>
      <!-- VIS ÃO TABELA -->
      <div class="card">
        <table class="table">
          <thead>
            <tr>
              <th>Título</th>
              <th>Cliente</th>
              <th>Responsável</th>
              <th>Valor</th>
              <th>Status</th>
              <th>Descrição</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($negociacoes as $n): ?>
              <tr>
                <td><?=htmlspecialchars($n['titulo'] ?? '—')?></td>
                <td><?=htmlspecialchars($n['cliente_nome'] ?? '—')?></td>
                <td><?=htmlspecialchars($n['usuario_nome'] ?? '—')?></td>
                <td>R$ <?=number_format($n['valor'],2,',','.')?></td>
                <td><?=htmlspecialchars($n['status'])?></td>
                <td><?=htmlspecialchars($n['observacoes'] ?? '—')?></td>
                <td>
                  <a href="editar.php?id=<?= $n['id_negociacao'] ?>" class="btn primary">Editar</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if(!count($negociacoes)): ?>
              <tr><td colspan="7" class="small">Nenhuma negociação cadastrada.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/footer.php'; ?>
