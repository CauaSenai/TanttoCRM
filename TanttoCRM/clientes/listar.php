<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: index.php'); exit; }
require 'conexao.php';

$stmt = $pdo->query("SELECT * FROM clientes ORDER BY data_cadastro DESC");
$clientes = $stmt->fetchAll();
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
