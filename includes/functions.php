<?php
require_once __DIR__ . '/config.php';

// ============================================
// CAR FUNCTIONS
// ============================================

function getAllCars($status = null) {
    $conn = getDBConnection();
    if ($status) {
        $stmt = $conn->prepare("SELECT * FROM cars WHERE status = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $status);
    } else {
        $stmt = $conn->prepare("SELECT * FROM cars ORDER BY created_at DESC");
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getCarById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function addCar($data) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO cars (name, brand, model, year, license_plate, category, seats, fuel_type, transmission, price_per_day, fine_per_day, status, image_url, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', ?, ?)");
    $stmt->bind_param("sssississddss", 
        $data['name'], $data['brand'], $data['model'], $data['year'],
        $data['license_plate'], $data['category'], $data['seats'],
        $data['fuel_type'], $data['transmission'], $data['price_per_day'],
        $data['fine_per_day'], $data['image_url'], $data['description']
    );
    if ($stmt->execute()) return ['success' => true, 'id' => $conn->insert_id];
    return ['success' => false, 'message' => $conn->error];
}

function updateCar($id, $data) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE cars SET name=?, brand=?, model=?, year=?, license_plate=?, category=?, seats=?, fuel_type=?, transmission=?, price_per_day=?, fine_per_day=?, status=?, image_url=?, description=? WHERE id=?");
    $stmt->bind_param("sssississddsssi",
        $data['name'], $data['brand'], $data['model'], $data['year'],
        $data['license_plate'], $data['category'], $data['seats'],
        $data['fuel_type'], $data['transmission'], $data['price_per_day'],
        $data['fine_per_day'], $data['status'], $data['image_url'],
        $data['description'], $id
    );
    if ($stmt->execute()) return ['success' => true];
    return ['success' => false, 'message' => $conn->error];
}

function deleteCar($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM cars WHERE id = ? AND status != 'rented'");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        if ($conn->affected_rows > 0) return ['success' => true];
        return ['success' => false, 'message' => 'Cannot delete a rented car.'];
    }
    return ['success' => false, 'message' => $conn->error];
}

// ============================================
// RENTAL FUNCTIONS
// ============================================

function createRental($userId, $carId, $rentDate, $dueDate) {
    $conn = getDBConnection();
    
    // Check car availability
    $car = getCarById($carId);
    if (!$car || $car['status'] !== 'available') {
        return ['success' => false, 'message' => 'Car is not available.'];
    }
    
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO rentals (user_id, car_id, rent_date, due_date, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->bind_param("iiss", $userId, $carId, $rentDate, $dueDate);
        $stmt->execute();
        $rentalId = $conn->insert_id;
        
        $stmt2 = $conn->prepare("UPDATE cars SET status = 'rented' WHERE id = ?");
        $stmt2->bind_param("i", $carId);
        $stmt2->execute();
        
        $conn->commit();
        return ['success' => true, 'rental_id' => $rentalId];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function returnCar($rentalId) {
    $conn = getDBConnection();
    $rental = getRentalById($rentalId);
    if (!$rental) return ['success' => false, 'message' => 'Rental not found.'];
    if ($rental['status'] === 'returned') return ['success' => false, 'message' => 'Already returned.'];
    
    $today = date('Y-m-d');
    $dueDate = $rental['due_date'];
    $rentDate = $rental['rent_date'];
    $car = getCarById($rental['car_id']);
    
    $daysRented = max(1, (strtotime($today) - strtotime($rentDate)) / 86400);
    $overdueDays = max(0, (strtotime($today) - strtotime($dueDate)) / 86400);
    
    $baseAmount = $daysRented * $car['price_per_day'];
    $fineAmount = $overdueDays * $car['fine_per_day'];
    $totalAmount = $baseAmount + $fineAmount;
    
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE rentals SET actual_return_date=?, status='returned' WHERE id=?");
        $stmt->bind_param("si", $today, $rentalId);
        $stmt->execute();
        
        $stmt2 = $conn->prepare("UPDATE cars SET status='available' WHERE id=?");
        $stmt2->bind_param("i", $rental['car_id']);
        $stmt2->execute();
        
        // Create payment record
        $userId = $rental['user_id'];
        $daysRentedInt = (int)ceil($daysRented);
        $overdueDaysInt = (int)ceil($overdueDays);
        $stmt3 = $conn->prepare("INSERT INTO payments (rental_id, user_id, base_amount, fine_amount, total_amount, days_rented, overdue_days, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'paid')");
        $stmt3->bind_param("iidddii", $rentalId, $userId, $baseAmount, $fineAmount, $totalAmount, $daysRentedInt, $overdueDaysInt);
        $stmt3->execute();
        
        $conn->commit();
        return ['success' => true, 'total' => $totalAmount, 'fine' => $fineAmount, 'base' => $baseAmount, 'overdue_days' => $overdueDaysInt];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getRentalById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT r.*, c.name as car_name, c.price_per_day, c.fine_per_day, u.name as user_name, u.email as user_email FROM rentals r JOIN cars c ON r.car_id = c.id JOIN users u ON r.user_id = u.id WHERE r.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getAllRentals() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT r.*, c.name as car_name, c.price_per_day, c.fine_per_day, c.image_url, u.name as user_name, u.email as user_email FROM rentals r JOIN cars c ON r.car_id = c.id JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getUserRentals($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT r.*, c.name as car_name, c.brand, c.model, c.price_per_day, c.fine_per_day, c.image_url, c.category FROM rentals r JOIN cars c ON r.car_id = c.id WHERE r.user_id = ? ORDER BY r.created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getAllPayments() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT p.*, u.name as user_name, u.email as user_email, c.name as car_name FROM payments p JOIN users u ON p.user_id = u.id JOIN rentals r ON p.rental_id = r.id JOIN cars c ON r.car_id = c.id ORDER BY p.payment_date DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAllUsers() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT u.*, COUNT(r.id) as total_rentals FROM users u LEFT JOIN rentals r ON u.id = r.user_id GROUP BY u.id ORDER BY u.created_at DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAdminStats() {
    $conn = getDBConnection();
    $stats = [];
    
    $stats['total_cars'] = $conn->query("SELECT COUNT(*) as c FROM cars")->fetch_assoc()['c'];
    $stats['available_cars'] = $conn->query("SELECT COUNT(*) as c FROM cars WHERE status='available'")->fetch_assoc()['c'];
    $stats['rented_cars'] = $conn->query("SELECT COUNT(*) as c FROM cars WHERE status='rented'")->fetch_assoc()['c'];
    $stats['total_users'] = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc()['c'];
    $stats['active_rentals'] = $conn->query("SELECT COUNT(*) as c FROM rentals WHERE status='active'")->fetch_assoc()['c'];
    $stats['total_revenue'] = $conn->query("SELECT COALESCE(SUM(total_amount),0) as c FROM payments WHERE payment_status='paid'")->fetch_assoc()['c'];
    $stats['total_fines'] = $conn->query("SELECT COALESCE(SUM(fine_amount),0) as c FROM payments WHERE fine_amount > 0")->fetch_assoc()['c'];
    $stats['overdue_rentals'] = $conn->query("SELECT COUNT(*) as c FROM rentals WHERE status='active' AND due_date < CURDATE()")->fetch_assoc()['c'];
    
    return $stats;
}

function calculateRentalCost($carId, $rentDate, $dueDate) {
    $car = getCarById($carId);
    if (!$car) return null;
    $days = max(1, (strtotime($dueDate) - strtotime($rentDate)) / 86400);
    return [
        'days' => (int)$days,
        'price_per_day' => $car['price_per_day'],
        'total' => $days * $car['price_per_day']
    ];
}

function updateOverdueRentals() {
    $conn = getDBConnection();
    $conn->query("UPDATE rentals SET status='overdue' WHERE status='active' AND due_date < CURDATE()");
}
?>
