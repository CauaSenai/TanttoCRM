<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../index.php'); exit; }
require '../conexao.php';

$id = intval($_GET['id'] ?? 0);
if($id){
    $del = $pdo->prepare("DELETE FROM negociacoes WHERE id_negociacao = ?");
    $del->execute([$id]);
}
header('Location: listar.php'); exit;
