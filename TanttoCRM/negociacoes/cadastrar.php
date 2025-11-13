<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: /index.php'); exit; }
require 'conexao.php';

$clientes = $pdo->query("SELECT id_cliente, nome FROM clientes ORDER BY nome")->fetchAll();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $cliente_id = intval($_POST['cliente_id'] ?? 0);
    $titulo = trim($_POST['titulo']);
    $valor = floatval(str_replace(',','.',$_POST['valor'] ?? 0));
    $status = $_POST['status'] ?? 'Prospecção';
    $data_inicio = $_POST['data_inicio'] ?: null;
    $obs = trim($_POST['observacoes']);
    $usuario_id = $_SESSION['user_id'];

  // Evitar duplicata: mesmo título para o mesmo cliente
  $dup = $pdo->prepare("SELECT id_negociacao FROM negociacoes WHERE cliente_id = ? AND titulo = ? LIMIT 1");
  $dup->execute([$cliente_id, $titulo]);
  if ($dup->fetch()) {
    $msg = 'Já existe uma negociação com este título para o cliente selecionado.';
  } else {
  // Garantir que usuario_id referencia um usuário válido; se não, usar NULL para evitar violação de FK
  if (!empty($usuario_id)) {
    $chk = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ? LIMIT 1");
    $chk->execute([$usuario_id]);
    $found = $chk->fetch();
    if (!$found) {
      // usuário da sessão não existe no banco (pode ter sido removido) -> colocar NULL
      $usuario_id = null;
    }
  } else {
    $usuario_id = null;
  }

  $ins = $pdo->prepare("INSERT INTO negociacoes (cliente_id, usuario_id, titulo, valor, status, data_inicio, observacoes) VALUES (?,?,?,?,?,?,?)");
  try {
    $ins->execute([$cliente_id,$usuario_id,$titulo,$valor,$status,$data_inicio,$obs]);
  } catch (PDOException $e) {
    // Se por algum motivo ainda ocorrer violação de FK, tentar novamente sem usuario_id
    if (strpos($e->getMessage(), '1452') !== false || strpos($e->getMessage(), 'foreign key') !== false) {
      try {
        $ins2 = $pdo->prepare("INSERT INTO negociacoes (cliente_id, usuario_id, titulo, valor, status, data_inicio, observacoes) VALUES (?,?,?,?,?,?,?)");
        $ins2->execute([$cliente_id, null, $titulo, $valor, $status, $data_inicio, $obs]);
      } catch (PDOException $e2) {
        // registrar e re-throw para debugging
        error_log('Erro ao inserir negociacao mesmo sem usuario_id: ' . $e2->getMessage());
        throw $e2;
      }
    } else {
      // re-throw outros erros
      throw $e;
    }
  }
    $newId = $pdo->lastInsertId();
     try {
       $insLog = $pdo->prepare("INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, valor_novo) VALUES (?,?,?,?,?)");
       $insLog->execute(['negociacao', $newId, $_SESSION['user_id'] ?? null, 'create', json_encode(['titulo'=>$titulo,'valor'=>$valor,'status'=>$status,'cliente_id'=>$cliente_id])]);
        } catch (PDOException $e) {
          error_log('Falha ao gravar auditoria (cadastrar negociacao): ' . $e->getMessage());
        }
        header('Location: listar.php'); exit;
    }
}
?>
<?php
  $pageTitle = 'Adicionar Negociação';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/header.php';
?>
  <div class="container">
    <div class="header"><h1 class="small">Adicionar Negociação</h1><div class="nav"><a href="listar.php">Voltar</a></div></div>
    <div class="card">
      <form method="post">
        <div class="form-row">
          <div class="form-group"><label>Cliente</label>
            <select name="cliente_id" required>
              <option value="">-- Selecionar --</option>
              <?php foreach($clientes as $c): ?>
                <option value="<?=$c['id_cliente']?>"><?=htmlspecialchars($c['nome'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Título</label><input type="text" name="titulo" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Valor (ex: 1500.00)</label><input type="text" name="valor"></div>
          <div class="form-group"><label>Status</label>
            <select name="status">
              <option>Prospecção</option>
              <option>Qualificação</option>
              <option>Proposta</option>
              <option>Negociação</option>
              <option>Fechamento</option>
              <option>Perdida</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Data de Início</label><input type="date" name="data_inicio"></div>
          <div class="form-group"><label>Observações</label><textarea name="observacoes"></textarea></div>
        </div>
        <div style="margin-top:12px">
          <button class="btn primary" type="submit">Salvar</button>
          <a href="listar.php" class="btn">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/TanttoCRM/includes/footer.php'; ?>
