<?php
session_start();

// Se já estiver logado, redireciona pro painel
if (isset($_SESSION['user_id'])) {
  header('Location: dashboard.php');
  exit;
}

require 'conexao.php';

$msg = '';

/*
  Novo fluxo de login/registro com senha:
  - Form com email + senha + nome (nome usado no registro)
  - Se marcar "Criar conta" (register=1), cria conta com senha hash
  - Se não marcar, tenta autenticar: procura usuário por email e verifica senha com password_verify
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $senha = $_POST['senha'] ?? '';
  $nome = trim($_POST['nome'] ?? '');
  $register = isset($_POST['register']) && $_POST['register'] == '1';

  if (!$email || !$senha) {
    $msg = 'Preencha e-mail e senha.';
  } else {
    // Busca usuário pelo email
    $stmt = $pdo->prepare("SELECT id_usuario, nome, cargo, senha FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($register) {
      // Registrar novo usuário
      if ($user) {
        $msg = 'Já existe um usuário com este e-mail. Faça login.';
      } else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, cargo) VALUES (?, ?, ?, 'Usuário')");
        $insert->execute([$nome ?: $email, $email, $hash]);
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $nome ?: $email;
        $_SESSION['user_cargo'] = 'Usuário';
        header('Location: dashboard.php');
        exit;
      }
    } else {
      // Login
      if (!$user) {
        $msg = 'Usuário não encontrado. Marque "Criar conta" para registrar.';
      } else {
        // Se senha armazenada vazia (legado), negar login e pedir registro/redefinição
        if (empty($user['senha'])) {
          $msg = 'Conta sem senha. Crie uma nova conta ou peça para redefinir a senha.';
        } else {
          if (password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_cargo'] = $user['cargo'];
            header('Location: dashboard.php');
            exit;
          } else {
            $msg = 'Senha incorreta.';
          }
        }
      }
    }
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
  <p class="small">Acesse o sistema com e-mail e senha. Se for novo, marque "Criar conta" e informe um nome.</p>

      <?php if ($msg): ?>
        <div class="card" style="background:#2a1111;color:#fff;margin-bottom:10px;padding:10px;border-radius:6px">
          <?= $msg ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="form-group">
          <label>E-mail</label>
          <input type="email" name="email" required>
        </div>
        <div class="form-group">
          <label>Senha</label>
          <input type="password" name="senha" required>
        </div>
        <div class="form-group">
          <label>Nome (apenas para registro)</label>
          <input type="text" name="nome" placeholder="Seu nome">
        </div>
        <div class="form-group">
          <label><input type="checkbox" name="register" value="1"> Criar conta se não existir</label>
        </div>
        <div style="margin-top:12px">
          <button class="btn primary" type="submit">Entrar / Registrar</button>
        </div>
      </form>
    </div>
  </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/footer.php'; ?>
