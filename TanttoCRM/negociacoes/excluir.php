<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../index.php'); exit; }
require '../conexao.php';

// Only accept POST requests for deletion (prevents accidental GET deletes and CSRF)
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: listar.php'); exit;
}

$id = intval($_POST['id'] ?? 0);
$token = $_POST['csrf_token'] ?? '';

// Validate token
if (!$id || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)){
    // invalid request
    header('Location: listar.php'); exit;
}

$row = $pdo->prepare("SELECT * FROM negociacoes WHERE id_negociacao = ?");
$row->execute([$id]);
$r = $row->fetch(PDO::FETCH_ASSOC);
if($r){
    try {
        $ins = $pdo->prepare("INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, valor_antigo) VALUES (?,?,?,?,?)");
        $ins->execute(['negociacao', $id, $_SESSION['user_id'] ?? null, 'delete', json_encode($r)]);
    } catch (PDOException $e) {
        error_log('Falha ao gravar auditoria (excluir negociacao): ' . $e->getMessage());
    }
}

$del = $pdo->prepare("DELETE FROM negociacoes WHERE id_negociacao = ?");
$del->execute([$id]);

// invalidate CSRF token after use
unset($_SESSION['csrf_token']);

header('Location: listar.php'); exit;
