<?php
// Script opcional para migrar dados da tabela antiga `audit_logs` para a nova `auditoria`.
// Execute com: php migrate_audit_to_auditoria.php (no diretório migrations)

require_once __DIR__ . '/../conexao.php';

try {
    // Verifica se audit_logs existe
    $check = $pdo->prepare("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'audit_logs'");
    $check->execute();
    $exists = $check->fetchColumn() > 0;

    if (!$exists) {
        echo "Tabela audit_logs não encontrada. Nada a migrar.\n";
        exit;
    }

    // Cria auditoria se não existir
    $pdo->exec(file_get_contents(__DIR__ . '/003_create_auditoria.sql'));

    // Copia dados (mapeando colunas)
    $copy = $pdo->prepare("INSERT INTO auditoria (entidade_tipo, entidade_id, usuario_id, acao, campo, valor_antigo, valor_novo, criado_em)
        SELECT entity_type, entity_id, user_id, action, field_name, old_value, new_value, created_at FROM audit_logs");
    $copy->execute();

    // Opcional: renomear tabela antiga para backup
    $pdo->exec("RENAME TABLE audit_logs TO audit_logs_backup");

    echo "Migração concluída. Dados copiados para 'auditoria' e 'audit_logs' renomeada para 'audit_logs_backup'.\n";
} catch (PDOException $e) {
    echo "Erro durante a migração: " . $e->getMessage() . "\n";
    exit(1);
}
