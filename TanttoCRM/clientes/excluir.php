<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../index.php'); exit; }
require '../conexao.php';

$id = intval($_GET['id'] ?? 0);
if($id){
    $del = $pdo->prepare("DELETE FROM clientes WHERE id_cliente = ?");
    $del->execute([$id]);
}
header('Location: listar.php'); exit;
