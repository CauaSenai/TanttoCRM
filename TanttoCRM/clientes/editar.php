<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../index.php'); exit; }
require '../conexao.php';

$id = intval($_GET['id'] ?? 0);
if(!$id){ header('Location: listar.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();
if(!$cliente){ header('Location: listar.php'); exit; }

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nome = trim($_POST['nome']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telefone = trim($_POST['telefone']);
    $empresa = trim($_POST['empresa']);
    $cidade = trim($_POST['cidade']);

    $up = $pdo->prepare("UPDATE clientes SET nome=?, email=?, telefone=?, empresa=?, cidade=? WHERE id_cliente=?");
    $up->execute([$nome,$email,$telefone,$empresa,$cidade,$id]);
    header('Location: listar.php'); exit;
}
?>
<?php
  $pageTitle = 'Editar Cliente';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/header.php';
?>
  <div class="container">
    <div class="header"><h1 class="small">Editar Cliente</h1><div class="nav"><a href="listar.php">Voltar</a></div></div>
    <div class="card">
      <form method="post">
        <div class="form-row">
          <div class="form-group"><label>Nome do Cliente</label><input type="text" name="nome" required value="<?=htmlspecialchars($cliente['nome'])?>"></div>
          <div class="form-group"><label>E-mail</label><input type="email" name="email" value="<?=htmlspecialchars($cliente['email'])?>"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Telefone</label><input type="text" name="telefone" value="<?=htmlspecialchars($cliente['telefone'])?>"></div>
          <div class="form-group"><label>Empresa</label><input type="text" name="empresa" value="<?=htmlspecialchars($cliente['empresa'])?>"></div>
        </div>
        <div style="margin-top:12px">
          <button class="btn primary" type="submit">Salvar</button>
          <a href="listar.php" class="btn">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/footer.php'; ?>
