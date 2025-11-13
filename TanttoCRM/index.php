<?php
session_start();

// Se já estiver logado, redireciona pro painel
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require 'conexao.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $nome = trim($_POST['nome'] ?? '');

    if ($email && $nome) {
        // Tenta encontrar usuário existente
        $stmt = $pdo->prepare("SELECT id_usuario, nome, cargo FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Se não existir, cria automaticamente um novo
            $insert = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, cargo) VALUES (?, ?, '', 'Usuário')");
            $insert->execute([$nome, $email]);
            $userId = $pdo->lastInsertId();
            $cargo = 'Usuário';
        } else {
            $userId = $user['id_usuario'];
            $nome = $user['nome'];
            $cargo = $user['cargo'];
        }

        // Inicia a sessão
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $nome;
        $_SESSION['user_cargo'] = $cargo;

        header('Location: dashboard.php');
        exit;
    } else {
        $msg = "Preencha seu nome e e-mail.";
    }
}
?>
<?php
  $pageTitle = 'Tantto - Entrar';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/header.php';
?>
  <div class="container">
    <div class="card" style="max-width:480px;margin:40px auto;">
      <h1>Tantto CRM</h1>
      <p class="small">Acesse o sistema (sem senha por enquanto)</p>

      <?php if ($msg): ?>
        <div class="card" style="background:#2a1111;color:#fff;margin-bottom:10px;padding:10px;border-radius:6px">
          <?= $msg ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="form-group">
          <label>Nome</label>
          <input type="text" name="nome" required>
        </div>
        <div class="form-group">
          <label>E-mail</label>
          <input type="email" name="email" required>
        </div>
        <div style="margin-top:12px">
          <button class="btn primary" type="submit">Entrar</button>
        </div>
      </form>
    </div>
  </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/footer.php'; ?>
