<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: /index.php'); exit; }
require 'conexao.php';

$clientes = $pdo->query("SELECT id_cliente, nome FROM clientes ORDER BY nome")->fetchAll();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $cliente_id = intval($_POST['cliente_id'] ?? 0);
    $titulo = trim($_POST['titulo']);
    $valor = floatval(str_replace(',','.',$_POST['valor'] ?? 0));
    $status = $_POST['status'] ?? 'Em andamento';
    $data_inicio = $_POST['data_inicio'] ?: null;
    $obs = trim($_POST['observacoes']);
    $usuario_id = $_SESSION['user_id'];

    $ins = $pdo->prepare("INSERT INTO negociacoes (cliente_id, usuario_id, titulo, valor, status, data_inicio, observacoes) VALUES (?,?,?,?,?,?,?)");
    $ins->execute([$cliente_id,$usuario_id,$titulo,$valor,$status,$data_inicio,$obs]);
    header('Location: listar.php'); exit;
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Adicionar Negociação</title><link rel="stylesheet" href="../style.css"></head>
<body>
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
              <option>Em andamento</option>
              <option>Concluída</option>
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
</body></html>
