<?php if (!isset($_SESSION['user_id'])) return; ?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <h2>Tantto CRM</h2>
        <div class="small">Olá, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuário') ?></div>
    </div>

    <nav class="sidebar-menu" aria-label="Menu principal">
        <a href="/TanttoCRM/dashboard.php" class="menu-link <?= strpos($_SERVER['PHP_SELF'], 'dashboard.php') !== false ? 'active' : '' ?>">
            Painel
        </a>
        <a href="/TanttoCRM/clientes/listar.php" class="menu-link <?= strpos($_SERVER['PHP_SELF'], '/clientes/') !== false ? 'active' : '' ?>">
            Clientes
        </a>
        <a href="/TanttoCRM/negociacoes/listar.php" class="menu-link <?= strpos($_SERVER['PHP_SELF'], '/negociacoes/') !== false ? 'active' : '' ?>">
            Negociações
        </a>
    </nav>

    <div class="logout">
        <a href="/TanttoCRM/logout.php" class="menu-link">
            Sair
        </a>
    </div>
</aside>
