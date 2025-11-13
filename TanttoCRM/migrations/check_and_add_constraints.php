<?php
// Script seguro para criar índices únicos se não houver duplicatas.
// Execute: php check_and_add_constraints.php (no diretório migrations)
require_once __DIR__ . '/../conexao.php';

$tasks = [
    [
        'table' => 'usuarios',
        'cols' => ['email'],
        'index' => 'ux_usuarios_email'
    ],
    [
        'table' => 'clientes',
        'cols' => ['email'],
        'index' => 'ux_clientes_email'
    ],
    [
        'table' => 'clientes',
        'cols' => ['telefone'],
        'index' => 'ux_clientes_telefone'
    ],
    [
        'table' => 'negociacoes',
        'cols' => ['cliente_id','titulo'],
        'index' => 'ux_negociacoes_cliente_titulo'
    ],
];

foreach ($tasks as $t) {
    $table = $t['table'];
    $cols = $t['cols'];
    $index = $t['index'];

    echo "\nVerificando $table (índice $index) ...\n";

    // Monta GROUP BY para detectar duplicatas
    $groupCols = implode(', ', array_map(function($c){ return "$c"; }, $cols));
    $selectSql = "SELECT $groupCols, COUNT(*) as cnt FROM $table GROUP BY $groupCols HAVING cnt > 1 LIMIT 10";
    try {
        $stmt = $pdo->query($selectSql);
        $dups = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo "Erro ao verificar duplicatas na tabela $table: " . $e->getMessage() . "\n";
        continue;
    }

    if (count($dups) > 0) {
        echo "Encontradas duplicatas que impedem criação do índice $index em $table:\n";
        foreach ($dups as $d) {
            $vals = [];
            foreach ($cols as $c) {
                $vals[] = "$c='" . ($d[$c] ?? '') . "'";
            }
            echo " - " . implode(', ', $vals) . " (count=" . $d['cnt'] . ")\n";
        }
        echo "Corrija ou remova duplicatas manualmente antes de criar o índice.\n";
        continue;
    }

    // verificar se índice já existe
    $checkIdx = $pdo->prepare("SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?");
    $checkIdx->execute([$table, $index]);
    if ($checkIdx->fetchColumn() > 0) {
        echo "Índice $index já existe em $table. Pulando.\n";
        continue;
    }

    // construir ALTER TABLE
    $colsDef = implode(',', $cols);
    $alter = "ALTER TABLE $table ADD UNIQUE INDEX $index ($colsDef)";
    try {
        $pdo->exec($alter);
        echo "Índice $index criado com sucesso em $table.\n";
    } catch (PDOException $e) {
        echo "Falha ao criar índice $index em $table: " . $e->getMessage() . "\n";
    }
}

echo "\nVerificação concluída.\n";
