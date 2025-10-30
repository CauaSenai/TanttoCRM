<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ./index.php'); exit; }
require 'conexao.php';

$stmt = $pdo->query("SELECT n.*, c.nome AS cliente_nome, u.nome AS usuario_nome
                     FROM negociacoes n
                     LEFT JOIN clientes c ON n.cliente_id = c.id_cliente
                     LEFT JOIN usuarios u ON n.usuario_id = u.id_usuario
                     ORDER BY n.data_inicio DESC");
$negociacoes = $stmt->fetchAll();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Negociações - Tantto</title><link rel="stylesheet" href="style.css"></head>
<body>
  <div class="container">
    <div class="header">
      <h1 class="small">Negociações</h1>
      <div class="nav">
        <a href="../dashboard.php">Painel</a>
        <a href="/TanttoCRM/clientes/listar.php">Clientes</a>
        <a href="cadastrar.php" class="button">Adicionar Negociação</a>
        <a href="../logout.php">Sair</a>
      </div>
    </div>

    <div class="card">
      <table class="table">
        <thead><tr><th>Título</th><th>Cliente</th><th>Responsável</th><th>Valor</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
          <?php foreach($negociacoes as $n): ?>
            <tr>
              <td><?=htmlspecialchars($n['titulo'])?></td>
              <td><?=htmlspecialchars($n['cliente_nome'] ?? '—')?></td>
              <td><?=htmlspecialchars($n['usuario_nome'] ?? '—')?></td>
              <td>R$ <?=number_format($n['valor'],2,',','.')?></td>
              <td><?=htmlspecialchars($n['status'])?></td>
              <td>
                <a href="editar.php?id=<?= $n['id_negociacao'] ?>" class="btn">Editar</a>
                <a href="excluir.php?id=<?= $n['id_negociacao'] ?>" class="btn danger" onclick="return confirm('Excluir esta negociação?')">Excluir</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!count($negociacoes)): ?>
            <tr><td colspan="6" class="small">Nenhuma negociação cadastrada.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body></html>
