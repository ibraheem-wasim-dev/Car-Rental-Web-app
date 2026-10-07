<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';
requireAdmin('../index.php');

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $data = [
            'name'          => sanitize($_POST['name'] ?? ''),
            'brand'         => sanitize($_POST['brand'] ?? ''),
            'model'         => sanitize($_POST['model'] ?? ''),
            'year'          => (int)($_POST['year'] ?? 2023),
            'license_plate' => sanitize($_POST['license_plate'] ?? ''),
            'category'      => sanitize($_POST['category'] ?? 'sedan'),
            'seats'         => (int)($_POST['seats'] ?? 5),
            'fuel_type'     => sanitize($_POST['fuel_type'] ?? 'petrol'),
            'transmission'  => sanitize($_POST['transmission'] ?? 'automatic'),
            'price_per_day' => (float)($_POST['price_per_day'] ?? 0),
            'fine_per_day'  => (float)($_POST['fine_per_day'] ?? 15),
            'status'        => sanitize($_POST['status'] ?? 'available'),
            'image_url'     => sanitize($_POST['image_url'] ?? ''),
            'description'   => sanitize($_POST['description'] ?? ''),
        ];
        
        if ($action === 'add') {
            $result = addCar($data);
            $message = $result['success'] ? 'Car added successfully!' : $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
        } else {
            $id = (int)($_POST['car_id'] ?? 0);
            $result = updateCar($id, $data);
            $message = $result['success'] ? 'Car updated successfully!' : $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['car_id'] ?? 0);
        $result = deleteCar($id);
        $message = $result['success'] ? 'Car deleted.' : $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
}

$cars = getAllCars();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cars Management - DriveEase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php renderSidebar('admin', 'cars'); ?>
    
    <div class="main-content">
        <?php renderTopbar('Cars Management', 'Add, update, and manage your fleet'); ?>
        
        <div class="page-content">
            <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>"><?= $message === 'success' ? '✓' : '⚠' ?> <?= $message ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="flex items-center gap-12">
                        <h3 class="card-title">Fleet (<?= count($cars) ?> cars)</h3>
                        <div class="search-bar">
                            <span>🔍</span>
                            <input type="text" id="search-cars" placeholder="Search cars...">
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="openModal('add-car-modal')">+ Add Car</button>
                </div>
                <div class="table-wrapper">
                    <?php if (empty($cars)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🚘</div>
                        <div class="empty-title">No cars added yet</div>
                        <div class="empty-text">Add your first car to get started</div>
                    </div>
                    <?php else: ?>
                    <table id="cars-table">
                        <thead>
                            <tr>
                                <th>Car</th>
                                <th>Details</th>
                                <th>Category</th>
                                <th>Price/Day</th>
                                <th>Fine/Day</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $car): ?>
                            <tr>
                                <td>
                                    <div class="fw-600"><?= htmlspecialchars($car['name']) ?></div>
                                    <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($car['license_plate']) ?></div>
                                </td>
                                <td>
                                    <div style="font-size:13px"><?= $car['year'] ?> · <?= ucfirst($car['fuel_type']) ?> · <?= ucfirst($car['transmission']) ?></div>
                                    <div style="font-size:12px;color:var(--text-muted)"><?= $car['seats'] ?> seats</div>
                                </td>
                                <td><span class="badge badge-muted"><?= ucfirst($car['category']) ?></span></td>
                                <td class="text-accent fw-600">$<?= number_format($car['price_per_day'], 2) ?></td>
                                <td class="text-danger">$<?= number_format($car['fine_per_day'], 2) ?></td>
                                <td>
                                    <?php
                                    $statusBadges = [
                                        'available' => 'badge-success',
                                        'rented' => 'badge-warning',
                                        'maintenance' => 'badge-danger',
                                    ];
                                    $cls = $statusBadges[$car['status']] ?? 'badge-muted';
                                    ?>
                                    <span class="badge <?= $cls ?>"><?= ucfirst($car['status']) ?></span>
                                </td>
                                <td>
                                    <div class="flex gap-8">
                                        <button class="btn btn-outline btn-sm" onclick='editCar(<?= json_encode($car) ?>)'>✏ Edit</button>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this car?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                                        </form>
                                    </div>
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

<!-- ADD CAR MODAL -->
<div class="modal-overlay" id="add-car-modal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add New Car</h3>
            <button class="modal-close" onclick="closeModal('add-car-modal')">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <?php include 'car_form.php'; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('add-car-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Car</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT CAR MODAL -->
<div class="modal-overlay" id="edit-car-modal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Edit Car</h3>
            <button class="modal-close" onclick="closeModal('edit-car-modal')">×</button>
        </div>
        <form method="POST" id="edit-car-form">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="car_id" id="edit-car-id">
            <div class="modal-body" id="edit-modal-body">
                <?php include 'car_form.php'; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('edit-car-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
function editCar(car) {
    const form = document.getElementById('edit-car-form');
    document.getElementById('edit-car-id').value = car.id;
    
    const fields = ['name','brand','model','year','license_plate','category','seats','fuel_type','transmission','price_per_day','fine_per_day','status','image_url','description'];
    fields.forEach(field => {
        const el = form.querySelector(`[name="${field}"]`);
        if (el) el.value = car[field] || '';
    });
    
    openModal('edit-car-modal');
}
</script>
</body>
</html>
