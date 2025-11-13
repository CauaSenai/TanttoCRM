<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../index.php'); exit; }
require '../conexao.php';

// Aceita tanto GET quanto POST
 $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
 if($id){
    // fetch row for audit
    $row = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
    $row->execute([$id]);
    $r = $row->fetch(PDO::FETCH_ASSOC);
    if($r){
        try {
            $ins = $pdo->prepare("INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, valor_antigo) VALUES (?,?,?,?,?)");
            $ins->execute(['cliente', $id, $_SESSION['user_id'] ?? null, 'delete', json_encode($r)]);
        } catch (PDOException $e) {
            // se a tabela/colunas não existirem, loga e continua (não quebra a exclusão)
            error_log('Falha ao gravar auditoria (excluir cliente): ' . $e->getMessage());
        }
    }

    $del = $pdo->prepare("DELETE FROM clientes WHERE id_cliente = ?");
    $del->execute([$id]);
 }
 header('Location: listar.php'); exit;
