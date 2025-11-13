<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../index.php'); exit; }
require 'conexao.php';

// Parâmetros de filtro
$busca = trim($_GET['busca'] ?? '');
$filtro_empresa = trim($_GET['empresa'] ?? '');
$filtro_cidade = trim($_GET['cidade'] ?? '');

// Construir query dinamicamente
$sql = "SELECT * FROM clientes WHERE 1=1";
$params = [];

if($busca) {
    $sql .= " AND (nome LIKE ? OR email LIKE ? OR telefone LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

if($filtro_empresa) {
    $sql .= " AND empresa LIKE ?";
    $params[] = "%$filtro_empresa%";
}

if($filtro_cidade) {
    $sql .= " AND cidade LIKE ?";
    $params[] = "%$filtro_cidade%";
}

$sql .= " ORDER BY data_cadastro DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

// Pegar lista única de empresas e cidades para filtros
$empresas = $pdo->query("SELECT DISTINCT empresa FROM clientes WHERE empresa IS NOT NULL AND empresa != '' ORDER BY empresa")->fetchAll();
$cidades = $pdo->query("SELECT DISTINCT cidade FROM clientes WHERE cidade IS NOT NULL AND cidade != '' ORDER BY cidade")->fetchAll();
?>
<?php
  $pageTitle = 'Clientes - Tantto CRM';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/header.php';
?>
  <!-- CONTEÚDO PRINCIPAL -->
  <div class="content">
    <div class="header">
      <h1>Clientes</h1>
      <a href="cadastrar.php" class="button">Adicionar Cliente</a>
    </div>

    <!-- Filtros e Busca -->
    <div class="card" style="margin-bottom:18px;">
      <form method="get" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
        <div style="flex:1; min-width:200px;">
          <label style="display:block; color:var(--muted); font-size:13px; margin-bottom:6px;">🔍 Buscar por Nome, E-mail ou Telefone</label>
          <input type="text" name="busca" placeholder="Digite aqui..." value="<?=htmlspecialchars($busca)?>" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.03); background:var(--surface); color:var(--text);">
        </div>

        <div style="flex:0 1 200px;">
          <label style="display:block; color:var(--muted); font-size:13px; margin-bottom:6px;">🏢 Filtrar por Empresa</label>
          <select name="empresa" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.03); background:var(--surface); color:var(--text);">
            <option value="">— Todas as empresas —</option>
            <?php foreach($empresas as $emp): ?>
              <option value="<?=htmlspecialchars($emp['empresa'])?>" <?=($filtro_empresa === $emp['empresa']) ? 'selected' : ''?>>
                <?=htmlspecialchars($emp['empresa'])?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="flex:0 1 200px;">
          <label style="display:block; color:var(--muted); font-size:13px; margin-bottom:6px;">📍 Filtrar por Cidade</label>
          <select name="cidade" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.03); background:var(--surface); color:var(--text);">
            <option value="">— Todas as cidades —</option>
            <?php foreach($cidades as $cid): ?>
              <option value="<?=htmlspecialchars($cid['cidade'])?>" <?=($filtro_cidade === $cid['cidade']) ? 'selected' : ''?>>
                <?=htmlspecialchars($cid['cidade'])?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn primary" style="padding:10px 20px;">Filtrar</button>
        <?php if($busca || $filtro_empresa || $filtro_cidade): ?>
          <a href="listar.php" class="btn" style="padding:10px 20px;">Limpar Filtros</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Resultado da busca -->
    <?php if($busca || $filtro_empresa || $filtro_cidade): ?>
      <div style="margin-bottom:12px; padding:12px; background:rgba(0,191,255,0.1); border-left:3px solid var(--accent); border-radius:6px; color:var(--muted); font-size:14px;">
        <strong>Resultado:</strong> <?=count($clientes)?> cliente(s) encontrado(s)
        <?php if($busca) echo " • Busca: <strong>" . htmlspecialchars($busca) . "</strong>"; ?>
        <?php if($filtro_empresa) echo " • Empresa: <strong>" . htmlspecialchars($filtro_empresa) . "</strong>"; ?>
        <?php if($filtro_cidade) echo " • Cidade: <strong>" . htmlspecialchars($filtro_cidade) . "</strong>"; ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Empresa</th>
            <th>Telefone</th>
            <th>E-mail</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($clientes as $c): ?>
            <tr>
              <td><?=htmlspecialchars($c['nome'])?></td>
              <td><?=htmlspecialchars($c['empresa'])?></td>
              <td><?=htmlspecialchars($c['telefone'])?></td>
              <td><?=htmlspecialchars($c['email'])?></td>
              <td>
                <a href="editar.php?id=<?= $c['id_cliente'] ?>" class="btn primary">Editar</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!count($clientes)): ?>
            <tr><td colspan="5" class="small">Nenhum cliente cadastrado.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/footer.php'; ?>
