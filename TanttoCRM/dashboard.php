<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'conexao.php';

// Estatísticas básicas
$totalClientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$totalNeg = $pdo->query("SELECT COUNT(*) FROM negociacoes")->fetchColumn();

$emAndamento = $pdo->prepare("SELECT COUNT(*) FROM negociacoes WHERE status = 'Em andamento'");
$emAndamento->execute();
$emAndamentoCount = $emAndamento->fetchColumn();
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Painel - Tantto CRM</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">

    <div class="header">
      <div>
        <h1 class="small">Painel de Controle</h1>
        <div class="small">Bem-vindo, <?= htmlspecialchars($_SESSION['user_name']) ?></div>
      </div>

      <div class="nav">
        <a href="dashboard.php" class="active">Painel</a>
        <a href="clientes/listar.php">Clientes</a>
        <a href="negociacoes/listar.php">Negociações</a>
        <a href="logout.php">Sair</a>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-top: 18px;">
      <div class="card">
        <h2>Total de Clientes</h2>
        <div style="font-size:28px; margin-top:8px;"><?= $totalClientes ?></div>
        <a href="clientes/listar.php" class="button" style="margin-top:10px;display:inline-block;">Ver Lista</a>
      </div>

      <div class="card">
        <h2>Total de Negociações</h2>
        <div style="font-size:28px; margin-top:8px;"><?= $totalNeg ?></div>
        <a href="negociacoes/listar.php" class="button" style="margin-top:10px;display:inline-block;">Ver Lista</a>
      </div>

      <div class="card">
        <h2>Em Andamento</h2>
        <div style="font-size:28px; margin-top:8px;"><?= $emAndamentoCount ?></div>
      </div>
    </div>

    <div class="footer">Tantto CRM — Etapa 4</div>
  </div>
</body>
</html>
