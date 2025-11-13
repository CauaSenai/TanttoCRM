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

    // Evitar duplicatas por e-mail ou telefone
    $dupStmt = $pdo->prepare("SELECT id_cliente FROM clientes WHERE (email = ? AND ? <> '') OR (telefone = ? AND ? <> '') LIMIT 1");
    $dupStmt->execute([$email, $email, $telefone, $telefone]);
    $exists = $dupStmt->fetch();
    if ($exists) {
        $msg = 'Já existe um cliente com este e-mail ou telefone.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO clientes (nome,email,telefone,empresa,cidade) VALUES (?,?,?,?,?)");
        $stmt->execute([$nome,$email,$telefone,$empresa,$cidade]);
        $newId = $pdo->lastInsertId();
        // log creation (wrap in try/catch so missing audit table/columns won't break the request)
        try {
            $insLog = $pdo->prepare("INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, valor_novo) VALUES (?,?,?,?,?)");
            $insLog->execute(['cliente', $newId, $_SESSION['user_id'] ?? null, 'create', json_encode(['nome'=>$nome,'email'=>$email,'telefone'=>$telefone,'empresa'=>$empresa,'cidade'=>$cidade])]);
        } catch (PDOException $e) {
            error_log("Auditoria falhou (cadastrar cliente): " . $e->getMessage());
        }

        header('Location: listar.php');
        exit;
    }
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
          <div class="form-group"><label>Cidade</label><input type="text" name="cidade" placeholder="Cidade"></div>
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
