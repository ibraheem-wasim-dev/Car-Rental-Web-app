<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';
requireLogin('../index.php');
if (isAdmin()) { header('Location: ../admin/dashboard.php'); exit(); }

updateOverdueRentals();
$rentals = getUserRentals($_SESSION['user_id']);
$activeRentals = array_filter($rentals, fn($r) => $r['status'] === 'active' || $r['status'] === 'overdue');
$totalRentals = count($rentals);
$overdueCount = count(array_filter($rentals, fn($r) => $r['status'] === 'overdue'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - DriveEase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php renderSidebar('customer', 'dashboard'); ?>
    
    <div class="main-content">
        <?php renderTopbar('My Dashboard', 'Hello, ' . htmlspecialchars($_SESSION['user_name']) . '!'); ?>
        
        <div class="page-content">
            <!-- Stats -->
            <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr)">
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value"><?= $totalRentals ?></div>
                    <div class="stat-label">Total Rentals</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">🔑</div>
                    <div class="stat-value"><?= count($activeRentals) ?></div>
                    <div class="stat-label">Active Rentals</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon danger">⚠</div>
                    <div class="stat-value"><?= $overdueCount ?></div>
                    <div class="stat-label">Overdue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info">🚘</div>
                    <a href="cars.php" style="text-decoration:none">
                    <div class="stat-value">Browse</div>
                    <div class="stat-label">Available Cars →</div>
                    </a>
                </div>
            </div>

            <?php if ($overdueCount > 0): ?>
            <div class="alert alert-danger mb-24">
                ⚠ You have <?= $overdueCount ?> overdue rental<?= $overdueCount > 1 ? 's' : '' ?>. Late fines are being accumulated. Please return your car(s) immediately.
            </div>
            <?php endif; ?>

            <!-- Active Rentals -->
            <?php if (!empty($activeRentals)): ?>
            <div class="mb-24">
                <h3 style="font-family:'Syne',sans-serif; font-size:18px; font-weight:700; margin-bottom:16px">🔑 Active Rentals</h3>
                <div class="cars-grid">
                    <?php foreach ($activeRentals as $r):
                        $isOverdue = $r['status'] === 'overdue';
                        $today = new DateTime();
                        $due = new DateTime($r['due_date']);
                        $rent = new DateTime($r['rent_date']);
                        $totalDays = (int)$rent->diff($due)->days;
                        $elapsed = min($totalDays, (int)$rent->diff($today)->days);
                        $progress = $totalDays > 0 ? min(100, ($elapsed / $totalDays) * 100) : 100;
                    ?>
                    <div class="car-card" style="border-color: <?= $isOverdue ? 'rgba(239,68,68,0.4)' : 'var(--border)' ?>">
                        <div class="car-image">
                            <?php if ($r['image_url']): ?>
                            <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['car_name']) ?>" onerror="this.parentElement.innerHTML='<div class=\'car-image-placeholder\'>🚗</div>'">
                            <?php else: ?>
                            <div class="car-image-placeholder">🚗</div>
                            <?php endif; ?>
                            <div class="car-status-badge">
                                <span class="badge <?= $isOverdue ? 'badge-danger' : 'badge-success' ?>">
                                    <?= $isOverdue ? '⚠ Overdue' : '● Active' ?>
                                </span>
                            </div>
                        </div>
                        <div class="car-info">
                            <div class="car-name"><?= htmlspecialchars($r['car_name']) ?></div>
                            <div class="car-meta">
                                <?= date('M j', strtotime($r['rent_date'])) ?> → <?= date('M j, Y', strtotime($r['due_date'])) ?>
                            </div>
                            
                            <div style="margin-bottom:12px">
                                <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-muted);margin-bottom:4px">
                                    <span>Rental Progress</span>
                                    <span><?= round($progress) ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width:<?= $progress ?>%;background:<?= $isOverdue ? 'var(--danger)' : 'linear-gradient(90deg,var(--accent),var(--accent-light))' ?>"></div>
                                </div>
                            </div>

                            <div style="background:var(--bg-secondary);border-radius:8px;padding:12px;margin-bottom:12px">
                                <div style="font-size:11px;color:var(--text-muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:1px">
                                    <?= $isOverdue ? '⚠ Time Overdue' : '⏱ Time Remaining' ?>
                                </div>
                                <span data-due-date="<?= $r['due_date'] ?>">Calculating...</span>
                            </div>

                            <div style="display:flex;justify-content:space-between;align-items:center">
                                <div>
                                    <div style="font-size:12px;color:var(--text-muted)">Daily rate</div>
                                    <div class="text-accent fw-600">$<?= $r['price_per_day'] ?>/day</div>
                                </div>
                                <?php if ($isOverdue): ?>
                                <div style="text-align:right">
                                    <div style="font-size:12px;color:var(--text-muted)">Fine rate</div>
                                    <div class="text-danger fw-600">+$<?= $r['fine_per_day'] ?>/day</div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Links -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body" style="display:flex;gap:16px;flex-wrap:wrap">
                    <a href="cars.php" class="btn btn-primary">🚘 Rent a Car</a>
                    <a href="rentals.php" class="btn btn-outline">📋 My Rental History</a>
                    <a href="payments.php" class="btn btn-outline">💳 My Payments</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>
