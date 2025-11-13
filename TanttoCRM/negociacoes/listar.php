<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../index.php'); exit; }
require '../conexao.php';

$stmt = $pdo->query("SELECT n.*, c.nome AS cliente_nome, u.nome AS usuario_nome
                     FROM negociacoes n
                     LEFT JOIN clientes c ON n.cliente_id = c.id_cliente
                     LEFT JOIN usuarios u ON n.usuario_id = u.id_usuario
                     ORDER BY n.data_inicio DESC");
$negociacoes = $stmt->fetchAll();
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
  </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/footer.php'; ?>
