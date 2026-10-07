<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';
requireAdmin('../index.php');

$payments = getAllPayments();
$totalRevenue = array_sum(array_column($payments, 'total_amount'));
$totalFines = array_sum(array_column($payments, 'fine_amount'));
$totalBase = array_sum(array_column($payments, 'base_amount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - DriveEase Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php renderSidebar('admin', 'payments'); ?>
    
    <div class="main-content">
        <?php renderTopbar('Payments & Fines', 'Revenue overview and payment records'); ?>
        
        <div class="page-content">
            <!-- Revenue Stats -->
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 28px">
                <div class="stat-card">
                    <div class="stat-icon success">💰</div>
                    <div class="stat-value">$<?= number_format($totalRevenue, 2) ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🚘</div>
                    <div class="stat-value">$<?= number_format($totalBase, 2) ?></div>
                    <div class="stat-label">Rental Income</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon danger">🔴</div>
                    <div class="stat-value">$<?= number_format($totalFines, 2) ?></div>
                    <div class="stat-label">Late Fines</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="flex items-center gap-12">
                        <h3 class="card-title">Payment Records (<?= count($payments) ?>)</h3>
                        <div class="search-bar">
                            <span>🔍</span>
                            <input type="text" id="search-payments" placeholder="Search payments...">
                        </div>
                    </div>
                </div>
                <div class="table-wrapper">
                    <?php if (empty($payments)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">💰</div>
                        <div class="empty-title">No payments yet</div>
                        <div class="empty-text">Payments will appear here when cars are returned</div>
                    </div>
                    <?php else: ?>
                    <table id="payments-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Days</th>
                                <th>Base Amount</th>
                                <th>Overdue Days</th>
                                <th>Fine</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                            <tr>
                                <td style="color:var(--text-muted);font-size:13px">#<?= $p['id'] ?></td>
                                <td>
                                    <div class="fw-600"><?= htmlspecialchars($p['user_name']) ?></div>
                                    <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($p['user_email']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($p['car_name']) ?></td>
                                <td><?= $p['days_rented'] ?> days</td>
                                <td>$<?= number_format($p['base_amount'], 2) ?></td>
                                <td>
                                    <?php if ($p['overdue_days'] > 0): ?>
                                    <span class="badge badge-danger"><?= $p['overdue_days'] ?> days late</span>
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="<?= $p['fine_amount'] > 0 ? 'text-danger fw-600' : 'text-muted' ?>">
                                    <?= $p['fine_amount'] > 0 ? '+$' . number_format($p['fine_amount'], 2) : '—' ?>
                                </td>
                                <td class="text-accent fw-600">$<?= number_format($p['total_amount'], 2) ?></td>
                                <td style="font-size:13px"><?= date('M j, Y', strtotime($p['payment_date'])) ?></td>
                                <td>
                                    <?php
                                    $statuses = [
                                        'paid' => '<span class="badge badge-success">✓ Paid</span>',
                                        'pending' => '<span class="badge badge-warning">Pending</span>',
                                        'refunded' => '<span class="badge badge-info">Refunded</span>',
                                    ];
                                    echo $statuses[$p['payment_status']] ?? $p['payment_status'];
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
