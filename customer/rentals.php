<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';
requireLogin('../index.php');
if (isAdmin()) { header('Location: ../admin/rentals.php'); exit(); }

updateOverdueRentals();

$message = '';
$messageType = '';

// Handle redirect message after successful car return (PRG pattern)
if (isset($_GET['msg']) && $_GET['msg'] === 'success' && isset($_GET['text'])) {
    $message = htmlspecialchars(urldecode($_GET['text']));
    $messageType = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'return') {
    $rentalId = (int)($_POST['rental_id'] ?? 0);
    
    // Verify this rental belongs to the user
    $rental = getRentalById($rentalId);
    if ($rental && $rental['user_id'] == $_SESSION['user_id']) {
        $result = returnCar($rentalId);
        if ($result['success']) {
            $successMsg = urlencode(sprintf('Car returned successfully! Base cost: $%.2f | Fine: $%.2f | Total paid: $%.2f', $result['base'], $result['fine'], $result['total']));
            header("Location: rentals.php?msg=success&text=$successMsg");
            exit();
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    } else {
        $message = 'Unauthorized action.';
        $messageType = 'danger';
    }
}

$rentals = getUserRentals($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rentals - DriveEase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php renderSidebar('customer', 'rentals'); ?>
    
    <div class="main-content">
        <?php renderTopbar('My Rentals', 'View and manage your rental history'); ?>
        
        <div class="page-content">
            <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= $messageType === 'success' ? '✓' : '⚠' ?> <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <?php if (empty($rentals)): ?>
            <div class="card">
                <div class="empty-state" style="padding: 80px 20px">
                    <div class="empty-icon">🚘</div>
                    <div class="empty-title">No rentals yet</div>
                    <div class="empty-text">Browse our fleet and rent your first car!</div>
                    <a href="cars.php" class="btn btn-primary mt-16">Browse Cars →</a>
                </div>
            </div>
            <?php else: ?>

            <!-- Active Rentals -->
            <?php
            $activeRentals = array_filter($rentals, fn($r) => in_array($r['status'], ['active', 'overdue']));
            $pastRentals = array_filter($rentals, fn($r) => $r['status'] === 'returned');
            ?>

            <?php if (!empty($activeRentals)): ?>
            <div class="mb-24">
                <h3 style="font-family:'Syne',sans-serif;font-size:18px;font-weight:700;margin-bottom:16px">🔑 Active Rentals</h3>
                <?php foreach ($activeRentals as $r):
                    $isOverdue = $r['status'] === 'overdue';
                    $today = new DateTime();
                    $due = new DateTime($r['due_date']);
                    $rent = new DateTime($r['rent_date']);
                    $daysDiff = $today->diff($due)->days;
                    $isLate = $today > $due;
                    $estimatedFine = $isLate ? $daysDiff * $r['fine_per_day'] : 0;
                    $daysRented = max(1, (int)(new DateTime($r['rent_date']))->diff($today)->days);
                    $estimatedTotal = ($daysRented * $r['price_per_day']) + $estimatedFine;
                ?>
                <div class="card mb-16" style="border-color: <?= $isOverdue ? 'rgba(239,68,68,0.4)' : 'var(--border)' ?>">
                    <div class="card-body">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px">
                            <div style="display:flex;gap:16px;align-items:flex-start">
                                <div style="width:64px;height:64px;background:var(--bg-secondary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:32px;flex-shrink:0">
                                    <?= ['suv'=>'🚙','sports'=>'🏎','van'=>'🚐','luxury'=>'🚘','economy'=>'🚗'][$r['category']] ?? '🚗' ?>
                                </div>
                                <div>
                                    <div style="font-family:'Syne',sans-serif;font-size:18px;font-weight:700;margin-bottom:4px">
                                        <?= htmlspecialchars($r['car_name']) ?>
                                    </div>
                                    <div style="font-size:13px;color:var(--text-secondary);margin-bottom:8px">
                                        <?= htmlspecialchars($r['brand']) ?> <?= htmlspecialchars($r['model']) ?> · $<?= $r['price_per_day'] ?>/day
                                    </div>
                                    <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:13px">
                                        <span style="color:var(--text-secondary)">📅 Rented: <strong style="color:var(--text-primary)"><?= date('M j, Y', strtotime($r['rent_date'])) ?></strong></span>
                                        <span style="color:var(--text-secondary)">🔔 Due: <strong style="color:<?= $isOverdue ? 'var(--danger)' : 'var(--text-primary)' ?>"><?= date('M j, Y', strtotime($r['due_date'])) ?></strong></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="text-align:right">
                                <div style="margin-bottom:12px">
                                    <span data-due-date="<?= $r['due_date'] ?>">Calculating...</span>
                                </div>
                                
                                <?php if ($isOverdue): ?>
                                <div style="background:var(--danger-dim);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:10px 14px;margin-bottom:12px;text-align:center">
                                    <div style="font-size:12px;color:var(--danger);margin-bottom:2px">Estimated Fine</div>
                                    <div style="font-size:20px;font-weight:800;color:var(--danger);font-family:'Syne',sans-serif">
                                        +$<?= number_format($estimatedFine, 2) ?>
                                    </div>
                                    <div style="font-size:11px;color:var(--text-muted)">$<?= $r['fine_per_day'] ?>/day × <?= $daysDiff ?> days</div>
                                </div>
                                <?php endif; ?>
                                
                                <div style="background:var(--bg-secondary);border-radius:8px;padding:10px 14px;margin-bottom:12px;text-align:center">
                                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:2px">Est. Total</div>
                                    <div style="font-size:18px;font-weight:800;color:var(--accent);font-family:'Syne',sans-serif">
                                        $<?= number_format($estimatedTotal, 2) ?>
                                    </div>
                                </div>
                                
                                <form method="POST" onsubmit="return confirm('Confirm car return? Final payment will be calculated.')">
                                    <input type="hidden" name="action" value="return">
                                    <input type="hidden" name="rental_id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="btn <?= $isOverdue ? 'btn-danger' : 'btn-success' ?>">
                                        ↩ Return Car <?= $isOverdue ? '(Pay Now)' : '' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Rental History -->
            <?php if (!empty($pastRentals)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📜 Rental History</h3>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Car</th>
                                <th>Rent Date</th>
                                <th>Due Date</th>
                                <th>Returned</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pastRentals as $r): ?>
                            <tr>
                                <td>
                                    <div class="fw-600"><?= htmlspecialchars($r['car_name']) ?></div>
                                    <div style="font-size:12px;color:var(--text-muted)"><?= ucfirst($r['category']) ?></div>
                                </td>
                                <td><?= date('M j, Y', strtotime($r['rent_date'])) ?></td>
                                <td><?= date('M j, Y', strtotime($r['due_date'])) ?></td>
                                <td><?= $r['actual_return_date'] ? date('M j, Y', strtotime($r['actual_return_date'])) : '—' ?></td>
                                <td><span class="badge badge-info">✓ Returned</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>
