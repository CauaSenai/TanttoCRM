<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: /index.php'); exit; }
require 'conexao.php';
$msg='';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nome = trim($_POST['nome']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telefone = trim($_POST['telefone']);
    $empresa = trim($_POST['empresa']);
    $cidade = trim($_POST['cidade']);

    $stmt = $pdo->prepare("INSERT INTO clientes (nome,email,telefone,empresa,cidade) VALUES (?,?,?,?,?)");
    $stmt->execute([$nome,$email,$telefone,$empresa,$cidade]);
    header('Location: listar.php');
    exit;
}
?>
<?php
  $pageTitle = 'Cadastrar Cliente';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/header.php';
?>
  <div class="container">
    <div class="header"><h1 class="small">Cadastrar Cliente</h1><div class="nav"><a href="listar.php">Voltar</a></div></div>
    <div class="card">
      <form method="post">
        <div class="form-row">
          <div class="form-group"><label>Nome do Cliente</label><input type="text" name="nome" required></div>
          <div class="form-group"><label>E-mail</label><input type="email" name="email"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Telefone</label><input type="text" name="telefone"></div>
          <div class="form-group"><label>Empresa</label><input type="text" name="empresa"></div>
        </div>
        <div style="margin-top:12px">
          <button class="btn primary" type="submit">Salvar</button>
          <a href="listar.php" class="btn">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/footer.php'; ?>
