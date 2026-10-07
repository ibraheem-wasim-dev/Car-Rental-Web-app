<?php
// includes/layout.php - Shared layout components

function renderSidebar($role, $activePage = '') {
    $isAdmin = ($role === 'admin');
    $basePath = $isAdmin ? '' : '';
    $prefix = $isAdmin ? '' : '';
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">
            <div class="brand-icon">🚗</div>
            <div>
                <div class="brand-name">DriveEase</div>
                <div class="brand-sub"><?= $isAdmin ? 'Admin Panel' : 'Customer' ?></div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($isAdmin): ?>
        <div class="nav-section-title">Overview</div>
        <a href="dashboard.php" class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>
        
        <div class="nav-section-title">Management</div>
        <a href="cars.php" class="nav-item <?= $activePage === 'cars' ? 'active' : '' ?>">
            <span class="nav-icon">🚘</span> Cars
        </a>
        <a href="rentals.php" class="nav-item <?= $activePage === 'rentals' ? 'active' : '' ?>">
            <span class="nav-icon">📋</span> Rentals
        </a>
        <a href="payments.php" class="nav-item <?= $activePage === 'payments' ? 'active' : '' ?>">
            <span class="nav-icon">💰</span> Payments
        </a>
        <a href="users.php" class="nav-item <?= $activePage === 'users' ? 'active' : '' ?>">
            <span class="nav-icon">👥</span> Users
        </a>

        <?php else: ?>
        <div class="nav-section-title">My Account</div>
        <a href="dashboard.php" class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">🏠</span> Dashboard
        </a>
        <a href="cars.php" class="nav-item <?= $activePage === 'cars' ? 'active' : '' ?>">
            <span class="nav-icon">🚘</span> Browse Cars
        </a>
        <a href="rentals.php" class="nav-item <?= $activePage === 'rentals' ? 'active' : '' ?>">
            <span class="nav-icon">📋</span> My Rentals
        </a>
        <a href="payments.php" class="nav-item <?= $activePage === 'payments' ? 'active' : '' ?>">
            <span class="nav-icon">💳</span> Payments
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></div>
            <div>
                <div class="user-info-name"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></div>
                <div class="user-info-role"><?= $_SESSION['user_role'] ?? '' ?></div>
            </div>
        </div>
        <a href="../logout.php" class="btn-logout">
            <span>↩</span> Sign Out
        </a>
    </div>
</div>
<?php
}

function renderTopbar($title, $subtitle = '') {
?>
<div class="topbar">
    <div>
        <h1 class="topbar-title"><?= $title ?></h1>
        <?php if ($subtitle): ?>
        <div style="font-size:13px;color:var(--text-secondary);margin-top:2px"><?= $subtitle ?></div>
        <?php endif; ?>
    </div>
    <div class="topbar-right">
        <div style="font-size:13px;color:var(--text-muted)"><?= date('D, M j Y') ?></div>
        <button class="btn btn-outline btn-sm" onclick="toggleSidebar()" style="display:none" id="menu-toggle">☰</button>
    </div>
</div>
<?php
}
?>
