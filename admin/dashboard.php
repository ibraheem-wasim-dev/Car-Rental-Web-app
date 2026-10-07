<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';
requireAdmin('../index.php');

updateOverdueRentals();
$stats = getAdminStats();
$recentRentals = array_slice(getAllRentals(), 0, 6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DriveEase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php renderSidebar('admin', 'dashboard'); ?>
    
    <div class="main-content">
        <?php renderTopbar('Dashboard', 'Welcome back, ' . htmlspecialchars($_SESSION['user_name'])); ?>
        
        <div class="page-content">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🚘</div>
                    <div class="stat-value" data-count="<?= $stats['total_cars'] ?>"><?= $stats['total_cars'] ?></div>
                    <div class="stat-label">Total Cars</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">✅</div>
                    <div class="stat-value" data-count="<?= $stats['available_cars'] ?>"><?= $stats['available_cars'] ?></div>
                    <div class="stat-label">Available</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">🔑</div>
                    <div class="stat-value" data-count="<?= $stats['rented_cars'] ?>"><?= $stats['rented_cars'] ?></div>
                    <div class="stat-label">Rented Out</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info">👥</div>
                    <div class="stat-value" data-count="<?= $stats['total_users'] ?>"><?= $stats['total_users'] ?></div>
                    <div class="stat-label">Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value" data-count="<?= $stats['active_rentals'] ?>"><?= $stats['active_rentals'] ?></div>
                    <div class="stat-label">Active Rentals</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">💰</div>
                    <div class="stat-value">$<?= number_format($stats['total_revenue'], 0) ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon danger">⚠</div>
                    <div class="stat-value" data-count="<?= $stats['overdue_rentals'] ?>"><?= $stats['overdue_rentals'] ?></div>
                    <div class="stat-label">Overdue Rentals</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">🔴</div>
                    <div class="stat-value">$<?= number_format($stats['total_fines'], 0) ?></div>
                    <div class="stat-label">Fines Collected</div>
                </div>
            </div>

            <!-- Recent Rentals -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Rentals</h3>
                    <a href="rentals.php" class="btn btn-outline btn-sm">View All →</a>
                </div>
                <div class="table-wrapper">
                    <?php if (empty($recentRentals)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <div class="empty-title">No rentals yet</div>
                        <div class="empty-text">Rental records will appear here</div>
                    </div>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Rent Date</th>
                                <th>Due Date</th>
                                <th>Remaining</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRentals as $r): ?>
                            <tr>
                                <td>
                                    <div><?= htmlspecialchars($r['user_name']) ?></div>
                                    <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($r['user_email']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($r['car_name']) ?></td>
                                <td><?= date('M j, Y', strtotime($r['rent_date'])) ?></td>
                                <td><?= date('M j, Y', strtotime($r['due_date'])) ?></td>
                                <td>
                                    <?php if ($r['status'] === 'active' || $r['status'] === 'overdue'): ?>
                                    <span data-due-date="<?= $r['due_date'] ?>">Loading...</span>
                                    <?php else: ?>
                                    <span class="text-muted">Returned <?= date('M j', strtotime($r['actual_return_date'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        'active' => '<span class="badge badge-success">Active</span>',
                                        'returned' => '<span class="badge badge-info">Returned</span>',
                                        'overdue' => '<span class="badge badge-danger">Overdue</span>',
                                    ];
                                    echo $statusMap[$r['status']] ?? $r['status'];
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>
