<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($pageTitle)) $pageTitle = 'Tantto CRM';
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="/TanttoCRM/style.css">
</head>
<body>
  <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="left-nav" aria-label="Navegação principal">
      <div class="left-brand">
        <h2>Tantto CRM</h2>
        <div class="small"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuário') ?></div>
      </div>

      <div class="left-menu">
        <a href="/TanttoCRM/dashboard.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'dashboard.php') !== false ? 'active' : '' ?>">Painel</a>
        <a href="/TanttoCRM/clientes/listar.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/clientes/') !== false ? 'active' : '' ?>">Clientes</a>
        <a href="/TanttoCRM/negociacoes/listar.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/negociacoes/') !== false ? 'active' : '' ?>">Negociações</a>
      </div>

      <div class="left-logout">
        <a href="/TanttoCRM/logout.php" class="nav-link nav-logout">Sair</a>
      </div>
    </nav>

    <main class="content">
  <?php else: ?>
    <main class="container">
  <?php endif; ?>
