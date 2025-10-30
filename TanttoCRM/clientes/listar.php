<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: index.php'); exit; }
require 'conexao.php';

$stmt = $pdo->query("SELECT * FROM clientes ORDER BY data_cadastro DESC");
$clientes = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Clientes - Tantto</title><link rel="stylesheet" href="style.css"></head>
<body>
  <div class="container">
    <div class="header">
      <h1 class="small">Clientes</h1>
      <div class="nav">
        <a href="../dashboard.php">Painel</a>
        <a href="cadastrar.php" class="button">Adicionar Cliente</a>
        <a href="../negociacoes/listar.php">Negociações</a>
        <a href="/logout.php">Sair</a>
      </div>
    </div>

    <div class="card">
      <table class="table">
        <thead><tr><th>Nome</th><th>Empresa</th><th>Telefone</th><th>E-mail</th><th>Ações</th></tr></thead>
        <tbody>
          <?php foreach($clientes as $c): ?>
            <tr>
              <td><?=htmlspecialchars($c['nome'])?></td>
              <td><?=htmlspecialchars($c['empresa'])?></td>
              <td><?=htmlspecialchars($c['telefone'])?></td>
              <td><?=htmlspecialchars($c['email'])?></td>
              <td>
                <a href="editar.php?id=<?= $c['id_cliente'] ?>" class="btn">Editar</a>
                <a href="excluir.php?id=<?= $c['id_cliente'] ?>" class="btn danger" onclick="return confirm('Excluir este cliente? Esta ação remove negociações associadas.')">Excluir</a>
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
</body>
</html>
