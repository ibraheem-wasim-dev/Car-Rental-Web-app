<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';
requireLogin('../index.php');
if (isAdmin()) { header('Location: ../admin/cars.php'); exit(); }

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'rent') {
    $carId = (int)($_POST['car_id'] ?? 0);
    $rentDate = sanitize($_POST['rent_date'] ?? '');
    $dueDate = sanitize($_POST['due_date'] ?? '');
    
    if (!$rentDate || !$dueDate) {
        $message = 'Please select both rent and return dates.';
        $messageType = 'danger';
    } elseif ($dueDate <= $rentDate) {
        $message = 'Return date must be after rent date.';
        $messageType = 'danger';
    } else {
        $result = createRental($_SESSION['user_id'], $carId, $rentDate, $dueDate);
        if ($result['success']) {
            $message = 'Car rented successfully! Enjoy your ride.';
            $messageType = 'success';
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}

$cars = getAllCars();
$categories = array_unique(array_column($cars, 'category'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Cars - DriveEase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .filter-bar {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        .filter-btn {
            padding: 7px 16px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: none;
            color: var(--text-secondary);
            font-size: 13px;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'DM Sans', sans-serif;
        }
        .filter-btn:hover, .filter-btn.active {
            background: var(--accent-dim);
            border-color: var(--border-accent);
            color: var(--accent);
        }
        .rent-summary {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 16px;
            margin-top: 16px;
        }
        .rent-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }
        .rent-summary-row.total {
            border-top: 1px solid var(--border);
            margin-top: 6px;
            padding-top: 12px;
            font-weight: 700;
            color: var(--accent);
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="app-layout">
    <?php renderSidebar('customer', 'cars'); ?>
    
    <div class="main-content">
        <?php renderTopbar('Browse Cars', 'Find your perfect drive'); ?>
        
        <div class="page-content">
            <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= $messageType === 'success' ? '✓' : '⚠' ?> <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="filter-bar">
                <button class="filter-btn active" onclick="filterCars('all', this)">All Cars</button>
                <button class="filter-btn" onclick="filterCars('available', this)">Available Only</button>
                <?php foreach ($categories as $cat): ?>
                <button class="filter-btn" onclick="filterCars('<?= $cat ?>', this)"><?= ucfirst($cat) ?></button>
                <?php endforeach; ?>
            </div>

            <!-- Cars Grid -->
            <?php if (empty($cars)): ?>
            <div class="empty-state">
                <div class="empty-icon">🚘</div>
                <div class="empty-title">No cars available</div>
                <div class="empty-text">Check back soon for available cars</div>
            </div>
            <?php else: ?>
            <div class="cars-grid" id="cars-grid">
                <?php foreach ($cars as $car): ?>
                <div class="car-card" data-status="<?= $car['status'] ?>" data-category="<?= $car['category'] ?>">
                    <div class="car-image">
                        <?php if ($car['image_url']): ?>
                        <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['name']) ?>" onerror="this.parentElement.innerHTML='<div class=\'car-image-placeholder\'>🚗</div>'">
                        <?php else: ?>
                        <div class="car-image-placeholder">
                            <?= ['suv' => '🚙', 'sports' => '🏎', 'van' => '🚐', 'luxury' => '🚘', 'economy' => '🚗'][$car['category']] ?? '🚗' ?>
                        </div>
                        <?php endif; ?>
                        <div class="car-status-badge">
                            <span class="badge <?= $car['status'] === 'available' ? 'badge-success' : ($car['status'] === 'rented' ? 'badge-warning' : 'badge-danger') ?>">
                                <?= ucfirst($car['status']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="car-info">
                        <div class="car-name"><?= htmlspecialchars($car['name']) ?></div>
                        <div class="car-meta"><?= $car['year'] ?> · <?= htmlspecialchars($car['brand']) ?></div>
                        
                        <div class="car-specs">
                            <span class="spec-tag">💺 <?= $car['seats'] ?> seats</span>
                            <span class="spec-tag">⛽ <?= ucfirst($car['fuel_type']) ?></span>
                            <span class="spec-tag">⚙ <?= ucfirst($car['transmission']) ?></span>
                            <span class="spec-tag">📁 <?= ucfirst($car['category']) ?></span>
                        </div>

                        <?php if ($car['description']): ?>
                        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:12px;line-height:1.5">
                            <?= htmlspecialchars(substr($car['description'], 0, 80)) ?>...
                        </p>
                        <?php endif; ?>

                        <div class="car-footer">
                            <div>
                                <div class="car-price">$<?= number_format($car['price_per_day'], 2) ?></div>
                                <div class="car-price-sub">per day</div>
                            </div>
                            <?php if ($car['status'] === 'available'): ?>
                            <button class="btn btn-primary btn-sm" 
                                onclick='openRentModal(<?= json_encode(["id"=>$car['id'],"name"=>$car['name'],"price_per_day"=>$car['price_per_day'],"fine_per_day"=>$car['fine_per_day']]) ?>)'>
                                Rent Now →
                            </button>
                            <?php else: ?>
                            <span class="badge badge-muted">Unavailable</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- RENT MODAL -->
<div class="modal-overlay" id="rent-modal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="rent-modal-title">Rent a Car</h3>
            <button class="modal-close" onclick="closeModal('rent-modal')">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="rent">
            <input type="hidden" name="car_id" id="rent-car-id">
            <div class="modal-body">
                <div style="display:flex;align-items:center;gap:12px;background:var(--bg-secondary);padding:14px;border-radius:8px;margin-bottom:20px">
                    <span style="font-size:28px">🚗</span>
                    <div>
                        <div id="rent-car-name" style="font-family:'Syne',sans-serif;font-weight:700;font-size:16px"></div>
                        <div id="rent-car-price" style="color:var(--accent);font-size:14px"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Rent Date *</label>
                        <input type="date" id="rent_date" name="rent_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Return Date *</label>
                        <input type="date" id="due_date" name="due_date" class="form-control" required>
                    </div>
                </div>

                <div class="rent-summary" id="rent-summary" style="display:none">
                    <div style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px">Cost Summary</div>
                    <div class="rent-summary-row">
                        <span style="color:var(--text-secondary)">Duration</span>
                        <span id="calc-days">—</span>
                    </div>
                    <div class="rent-summary-row">
                        <span style="color:var(--text-secondary)">Rate</span>
                        <span id="calc-price">—</span>
                    </div>
                    <div class="rent-summary-row total">
                        <span>Total Estimate</span>
                        <span id="calc-total">—</span>
                    </div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:8px">
                        ⚠ Late returns incur a fine of <strong id="fine-per-day" style="color:var(--danger)"></strong>/day
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('rent-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Rental →</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
let currentCarPrice = 0;

function openRentModal(car) {
    document.getElementById('rent-car-id').value = car.id;
    document.getElementById('rent-modal-title').textContent = 'Rent: ' + car.name;
    document.getElementById('rent-car-name').textContent = car.name;
    document.getElementById('rent-car-price').textContent = '$' + parseFloat(car.price_per_day).toFixed(2) + ' / day';
    document.getElementById('fine-per-day').textContent = '$' + parseFloat(car.fine_per_day).toFixed(2);
    
    // Set data-price for calculator
    const priceEl = document.querySelector('[data-price]');
    if (priceEl) priceEl.dataset.price = car.price_per_day;
    else document.getElementById('rent-car-id').dataset.price = car.price_per_day;
    
    currentCarPrice = parseFloat(car.price_per_day);
    
    // Reset dates
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('rent_date').value = today;
    document.getElementById('rent_date').min = today;
    document.getElementById('due_date').value = '';
    document.getElementById('due_date').min = today;
    document.getElementById('rent-summary').style.display = 'none';
    
    openModal('rent-modal');
}

// Override price calculation to use currentCarPrice
document.addEventListener('DOMContentLoaded', function() {
    const rentDate = document.getElementById('rent_date');
    const dueDate = document.getElementById('due_date');
    
    function calculate() {
        if (!rentDate?.value || !dueDate?.value) return;
        const start = new Date(rentDate.value);
        const end = new Date(dueDate.value);
        const days = Math.max(1, Math.ceil((end - start) / 86400000));
        const total = days * currentCarPrice;
        
        document.getElementById('calc-days').textContent = days + ' day' + (days > 1 ? 's' : '');
        document.getElementById('calc-price').textContent = '$' + currentCarPrice.toFixed(2) + '/day';
        document.getElementById('calc-total').textContent = '$' + total.toFixed(2);
        document.getElementById('rent-summary').style.display = 'block';
    }
    
    rentDate?.addEventListener('change', function() {
        dueDate.min = this.value;
        calculate();
    });
    dueDate?.addEventListener('change', calculate);
});

function filterCars(filter, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    document.querySelectorAll('.car-card').forEach(card => {
        if (filter === 'all') {
            card.style.display = '';
        } else if (filter === 'available') {
            card.style.display = card.dataset.status === 'available' ? '' : 'none';
        } else {
            card.style.display = card.dataset.category === filter ? '' : 'none';
        }
    });
}
</script>
</body>
</html>
