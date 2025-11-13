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

  // Fetch old values for audit
  $old = $pdo->prepare("SELECT nome,email,telefone,empresa,cidade FROM clientes WHERE id_cliente = ?");
  $old->execute([$id]);
  $oldRow = $old->fetch(PDO::FETCH_ASSOC) ?: [];

  // Verifica duplicatas (outros clientes) por email ou telefone
  $dup = $pdo->prepare("SELECT id_cliente FROM clientes WHERE id_cliente <> ? AND ((email = ? AND ? <> '') OR (telefone = ? AND ? <> '')) LIMIT 1");
  $dup->execute([$id, $email, $email, $telefone, $telefone]);
  $dupExists = $dup->fetch();
  if ($dupExists) {
    $msg = 'Outro cliente já utiliza este e-mail ou telefone.';
  } else {
    $up = $pdo->prepare("UPDATE clientes SET nome=?, email=?, telefone=?, empresa=?, cidade=? WHERE id_cliente=?");
    $up->execute([$nome,$email,$telefone,$empresa,$cidade,$id]);

    // Insert audit entries for changed fields (usa tabela auditoria com colunas em português)
    $fields = ['nome','email','telefone','empresa','cidade'];
    $ins = $pdo->prepare("INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, campo, valor_antigo, valor_novo) VALUES (?,?,?,?,?,?,?)");
    foreach($fields as $f){
      $oldVal = $oldRow[$f] ?? null;
      $newVal = ${$f};
      if((string)$oldVal !== (string)$newVal){
        try {
          $ins->execute(['cliente', $id, $_SESSION['user_id'] ?? null, 'update', $f, $oldVal, $newVal]);
        } catch (PDOException $e) {
          error_log('Falha ao gravar auditoria (editar cliente): ' . $e->getMessage());
        }
      }
    }

    header('Location: listar.php'); exit;
  }
  
}

// Gerar token CSRF para o formulário de exclusão
if (session_status() === PHP_SESSION_NONE) session_start();
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;
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

      <!-- Delete form (separate to avoid nested forms) -->
      <form method="post" action="excluir.php" onsubmit="return confirm('Tem certeza que deseja excluir este cliente?')" style="margin-top:12px">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <button type="submit" class="btn danger">Excluir Cliente</button>
      </form>

      <!-- Histórico de alterações -->
      <div style="margin-top:18px">
        <h3>Histórico de alterações</h3>
        <?php
          // Busca histórico na tabela 'auditoria'. Os aliases mapearão para os nomes antigos esperados pelo template.
          $logStmt = $pdo->prepare("SELECT a.criado_em AS created_at, a.entidade_tipo AS entity_type, a.entidade_id AS entity_id, a.usuario_id AS user_id, a.acao AS action, a.campo AS field_name, a.valor_antigo AS old_value, a.valor_novo AS new_value, u.nome as usuario FROM auditoria a LEFT JOIN usuarios u ON a.usuario_id = u.id_usuario WHERE a.entidade_tipo = 'cliente' AND a.entidade_id = ? ORDER BY a.criado_em DESC LIMIT 20");
          $logStmt->execute([$id]);
          $logs = $logStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if(count($logs)): ?>
          <table class="table" style="margin-top:12px">
            <thead><tr><th>Quando</th><th>Usuário</th><th>Ação</th><th>Campo</th><th>Antes</th><th>Depois</th></tr></thead>
            <tbody>
            <?php foreach($logs as $l): ?>
              <tr>
                <td><?=htmlspecialchars($l['created_at'])?></td>
                <td><?=htmlspecialchars($l['usuario'] ?? ($l['user_id'] ?? 'Sistema'))?></td>
                <td><?=htmlspecialchars($l['action'])?></td>
                <td><?=htmlspecialchars($l['field_name'] ?? '-')?></td>
                <td><?=htmlspecialchars($l['old_value'] ?? '-')?></td>
                <td><?=htmlspecialchars($l['new_value'] ?? '-')?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="small" style="margin-top:8px;color:var(--muted)">Nenhuma alteração registrada.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/footer.php'; ?>
