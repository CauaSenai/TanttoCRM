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

$del = $pdo->prepare("DELETE FROM negociacoes WHERE id_negociacao = ?");
$del->execute([$id]);

// invalidate CSRF token after use
unset($_SESSION['csrf_token']);

header('Location: listar.php'); exit;
