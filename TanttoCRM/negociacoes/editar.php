<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../index.php'); exit; }
require '../conexao.php';

$id = intval($_GET['id'] ?? 0);
if(!$id) header('Location: listar.php');

$neg = $pdo->prepare("SELECT * FROM negociacoes WHERE id_negociacao = ?");
$neg->execute([$id]); $negociacao = $neg->fetch();
if(!$negociacao) header('Location: listar.php');

$clientes = $pdo->query("SELECT id_cliente, nome FROM clientes ORDER BY nome")->fetchAll();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $cliente_id = intval($_POST['cliente_id'] ?? 0);
    $titulo = trim($_POST['titulo']);
    $valor = floatval(str_replace(',','.',$_POST['valor'] ?? 0));
    $status = $_POST['status'] ?? 'Em andamento';
    $data_inicio = $_POST['data_inicio'] ?: null;
    $data_fechamento = $_POST['data_fechamento'] ?: null;
    $obs = trim($_POST['observacoes']);

    $up = $pdo->prepare("UPDATE negociacoes SET cliente_id=?, titulo=?, valor=?, status=?, data_inicio=?, data_fechamento=?, observacoes=? WHERE id_negociacao=?");
    $up->execute([$cliente_id,$titulo,$valor,$status,$data_inicio,$data_fechamento,$obs,$id]);
    header('Location: listar.php'); exit;
}
?>
<?php
  // generate a one-time CSRF token for the delete form
  if (session_status() === PHP_SESSION_NONE) session_start();
  $csrf_token = bin2hex(random_bytes(32));
  $_SESSION['csrf_token'] = $csrf_token;

  $pageTitle = 'Editar Negociação';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/header.php';
?>
  <div class="container">
    <div class="header"><h1 class="small">Editar Negociação</h1><div class="nav"><a href="listar.php">Voltar</a></div></div>
    <div class="card">
      <form method="post">
        <div class="form-row">
          <div class="form-group">
            <label>Cliente</label>
            <select name="cliente_id" required>
              <?php foreach($clientes as $c): ?>
                <option value="<?=$c['id_cliente']?>" <?=($c['id_cliente']==$negociacao['cliente_id'])?'selected':''?>><?=htmlspecialchars($c['nome'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Título</label><input type="text" name="titulo" required value="<?=htmlspecialchars($negociacao['titulo'])?>"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Valor</label><input type="text" name="valor" value="<?=htmlspecialchars($negociacao['valor'])?>"></div>
          <div class="form-group"><label>Status</label>
            <select name="status">
              <option <?=($negociacao['status']=='Em andamento')?'selected':''?>>Em andamento</option>
              <option <?=($negociacao['status']=='Concluída')?'selected':''?>>Concluída</option>
              <option <?=($negociacao['status']=='Perdida')?'selected':''?>>Perdida</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Data de Início</label><input type="date" name="data_inicio" value="<?=$negociacao['data_inicio']?>"></div>
          <div class="form-group"><label>Data de Fechamento</label><input type="date" name="data_fechamento" value="<?=$negociacao['data_fechamento']?>"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Observações</label><textarea name="observacoes"><?=htmlspecialchars($negociacao['observacoes'])?></textarea></div>
        </div>
        <div style="margin-top:12px;display:flex;gap:8px;align-items:center">
          <button class="btn primary" type="submit">Salvar</button>
          <a href="listar.php" class="btn">Cancelar</a>
        </div>
      </form>

      <!-- Delete form separate to avoid nested forms -->
      <form method="post" action="excluir.php" onsubmit="return confirm('Excluir esta negociação?')" style="margin-top:12px">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <button type="submit" class="btn danger">Excluir negociação</button>
      </form>
    </div>
  </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/footer.php'; ?>
