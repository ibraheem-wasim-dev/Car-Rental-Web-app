<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';
requireLogin('../index.php');
if (isAdmin()) { header('Location: ../admin/payments.php'); exit(); }

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT p.*, c.name as car_name FROM payments p JOIN rentals r ON p.rental_id = r.id JOIN cars c ON r.car_id = c.id WHERE p.user_id = ? ORDER BY p.payment_date DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$totalSpent = array_sum(array_column($payments, 'total_amount'));
$totalFines = array_sum(array_column($payments, 'fine_amount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payments - DriveEase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php renderSidebar('customer', 'payments'); ?>
    
    <div class="main-content">
        <?php renderTopbar('My Payments', 'Your payment and fine history'); ?>
        
        <div class="page-content">
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 28px">
                <div class="stat-card">
                    <div class="stat-icon success">💰</div>
                    <div class="stat-value">$<?= number_format($totalSpent, 2) ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value"><?= count($payments) ?></div>
                    <div class="stat-label">Completed Rentals</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon danger">🔴</div>
                    <div class="stat-value">$<?= number_format($totalFines, 2) ?></div>
                    <div class="stat-label">Fines Paid</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payment History</h3>
                    <input type="text" id="search-payments" class="form-control" placeholder="Search payments..." style="max-width:220px;padding:7px 12px;font-size:13px">
                </div>
                
                <?php if (empty($payments)): ?>
                <div class="empty-state">
                    <div class="empty-icon">💳</div>
                    <div class="empty-title">No payments yet</div>
                    <div class="empty-text">Completed rentals and payments will appear here</div>
                    <a href="cars.php" class="btn btn-primary mt-16">Rent a Car →</a>
                </div>
                <?php else: ?>
                <div class="table-wrapper">
                    <table id="payments-table">
                        <thead>
                            <tr>
                                <th>Car</th>
                                <th>Days</th>
                                <th>Base Amount</th>
                                <th>Overdue</th>
                                <th>Fine</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                            <tr>
                                <td class="fw-600"><?= htmlspecialchars($p['car_name']) ?></td>
                                <td><?= $p['days_rented'] ?> days</td>
                                <td>$<?= number_format($p['base_amount'], 2) ?></td>
                                <td>
                                    <?php if ($p['overdue_days'] > 0): ?>
                                    <span class="badge badge-danger"><?= $p['overdue_days'] ?> days</span>
                                    <?php else: ?>
                                    <span class="text-muted">On time ✓</span>
                                    <?php endif; ?>
                                </td>
                                <td class="<?= $p['fine_amount'] > 0 ? 'text-danger fw-600' : 'text-muted' ?>">
                                    <?= $p['fine_amount'] > 0 ? '+$'.number_format($p['fine_amount'],2) : '—' ?>
                                </td>
                                <td class="text-accent fw-600">$<?= number_format($p['total_amount'], 2) ?></td>
                                <td style="font-size:13px"><?= date('M j, Y', strtotime($p['payment_date'])) ?></td>
                                <td><span class="badge badge-success">✓ Paid</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>
