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
  $status = $_POST['status'] ?? 'Prospecção';
  $data_inicio = $_POST['data_inicio'] ?: null;
  $data_fechamento = $_POST['data_fechamento'] ?: null;
  $obs = trim($_POST['observacoes']);

  // fetch old row for audit
  $old = $pdo->prepare("SELECT cliente_id,titulo,valor,status,data_inicio,data_fechamento,observacoes FROM negociacoes WHERE id_negociacao = ?");
  $old->execute([$id]);
  $oldRow = $old->fetch(PDO::FETCH_ASSOC) ?: [];

  // Verifica duplicata: mesmo título para o mesmo cliente em outro registro
  $dup = $pdo->prepare("SELECT id_negociacao FROM negociacoes WHERE id_negociacao <> ? AND cliente_id = ? AND titulo = ? LIMIT 1");
  $dup->execute([$id, $cliente_id, $titulo]);
  if ($dup->fetch()) {
    $msg = 'Outra negociação com este título já existe para o cliente selecionado.';
  } else {
    $up = $pdo->prepare("UPDATE negociacoes SET cliente_id=?, titulo=?, valor=?, status=?, data_inicio=?, data_fechamento=?, observacoes=? WHERE id_negociacao=?");
    $up->execute([$cliente_id,$titulo,$valor,$status,$data_inicio,$data_fechamento,$obs,$id]);

    $fields = ['cliente_id','titulo','valor','status','data_inicio','data_fechamento','observacoes'];
    $ins = $pdo->prepare("INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, campo, valor_antigo, valor_novo) VALUES (?,?,?,?,?,?,?)");
    foreach($fields as $f){
      $oldVal = $oldRow[$f] ?? null;
      $newVal = ${$f};
      if((string)$oldVal !== (string)$newVal){
        try {
          $ins->execute(['negociacao', $id, $_SESSION['user_id'] ?? null, 'update', $f, $oldVal, $newVal]);
        } catch (PDOException $e) {
          error_log('Falha ao gravar auditoria (editar negociacao): ' . $e->getMessage());
        }
      }
    }

    header('Location: listar.php'); exit;
  }

  $fields = ['cliente_id','titulo','valor','status','data_inicio','data_fechamento','observacoes'];
  $ins = $pdo->prepare("INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, campo, valor_antigo, valor_novo) VALUES (?,?,?,?,?,?,?)");
  foreach($fields as $f){
    $oldVal = $oldRow[$f] ?? null;
    $newVal = ${$f};
    if((string)$oldVal !== (string)$newVal){
      try {
        $ins->execute(['negociacao', $id, $_SESSION['user_id'] ?? null, 'update', $f, $oldVal, $newVal]);
      } catch (PDOException $e) {
        error_log('Falha ao gravar auditoria (editar negociacao): ' . $e->getMessage());
      }
    }
  }

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
              <option <?=($negociacao['status']=='Prospecção')?'selected':''?>>Prospecção</option>
              <option <?=($negociacao['status']=='Qualificação')?'selected':''?>>Qualificação</option>
              <option <?=($negociacao['status']=='Proposta')?'selected':''?>>Proposta</option>
              <option <?=($negociacao['status']=='Negociação')?'selected':''?>>Negociação</option>
              <option <?=($negociacao['status']=='Fechamento')?'selected':''?>>Fechamento</option>
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
      
      <!-- Histórico de alterações -->
      <div style="margin-top:18px">
        <h3>Histórico de alterações</h3>
        <?php
          $logStmt = $pdo->prepare("SELECT a.criado_em AS created_at, a.entidade_tipo AS entity_type, a.entidade_id AS entity_id, a.usuario_id AS user_id, a.acao AS action, a.campo AS field_name, a.valor_antigo AS old_value, a.valor_novo AS new_value, u.nome as usuario FROM auditoria a LEFT JOIN usuarios u ON a.usuario_id = u.id_usuario WHERE a.entidade_tipo = 'negociacao' AND a.entidade_id = ? ORDER BY a.criado_em DESC LIMIT 30");
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
