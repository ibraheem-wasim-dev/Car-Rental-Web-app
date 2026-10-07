<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';
requireAdmin('../index.php');

updateOverdueRentals();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'return') {
    $rentalId = (int)($_POST['rental_id'] ?? 0);
    $result = returnCar($rentalId);
    if ($result['success']) {
        $message = sprintf('Car returned. Base: $%.2f | Fine: $%.2f | Total: $%.2f', $result['base'], $result['fine'], $result['total']);
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}

$rentals = getAllRentals();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rentals - DriveEase Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php renderSidebar('admin', 'rentals'); ?>
    
    <div class="main-content">
        <?php renderTopbar('Rentals', 'All rental records and statuses'); ?>
        
        <div class="page-content">
            <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= $messageType === 'success' ? '✓' : '⚠' ?> <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="flex items-center gap-12">
                        <h3 class="card-title">All Rentals (<?= count($rentals) ?>)</h3>
                        <div class="search-bar">
                            <span>🔍</span>
                            <input type="text" id="search-rentals" placeholder="Search rentals...">
                        </div>
                    </div>
                    <div class="flex gap-8">
                        <span class="badge badge-success">Active</span>
                        <span class="badge badge-danger">Overdue</span>
                        <span class="badge badge-info">Returned</span>
                    </div>
                </div>
                <div class="table-wrapper">
                    <?php if (empty($rentals)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <div class="empty-title">No rentals found</div>
                        <div class="empty-text">Rental records will appear here</div>
                    </div>
                    <?php else: ?>
                    <table id="rentals-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Rent Date</th>
                                <th>Due Date</th>
                                <th>Time Remaining</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rentals as $r): ?>
                            <tr>
                                <td style="color:var(--text-muted); font-size:13px">#<?= $r['id'] ?></td>
                                <td>
                                    <div class="fw-600"><?= htmlspecialchars($r['user_name']) ?></div>
                                    <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($r['user_email']) ?></div>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($r['car_name']) ?></div>
                                    <div style="font-size:12px;color:var(--text-muted)">$<?= $r['price_per_day'] ?>/day</div>
                                </td>
                                <td><?= date('M j, Y', strtotime($r['rent_date'])) ?></td>
                                <td><?= date('M j, Y', strtotime($r['due_date'])) ?></td>
                                <td>
                                    <?php if ($r['status'] === 'returned'): ?>
                                        <span style="font-size:13px;color:var(--text-muted)">
                                            Returned: <?= $r['actual_return_date'] ? date('M j, Y', strtotime($r['actual_return_date'])) : 'N/A' ?>
                                        </span>
                                    <?php else: ?>
                                        <span data-due-date="<?= $r['due_date'] ?>">Calculating...</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badges = [
                                        'active' => '<span class="badge badge-success">● Active</span>',
                                        'returned' => '<span class="badge badge-info">✓ Returned</span>',
                                        'overdue' => '<span class="badge badge-danger">⚠ Overdue</span>',
                                    ];
                                    echo $badges[$r['status']] ?? $r['status'];
                                    ?>
                                </td>
                                <td>
                                    <?php if ($r['status'] !== 'returned'): ?>
                                    <form method="POST" onsubmit="return confirm('Process car return? Payment will be calculated.')">
                                        <input type="hidden" name="action" value="return">
                                        <input type="hidden" name="rental_id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">↩ Return</button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-muted" style="font-size:13px">—</span>
                                    <?php endif; ?>
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
