<?php
require_once __DIR__ . '/config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

function isCustomer() {
    return isLoggedIn() && $_SESSION['user_role'] === 'customer';
}

function requireLogin($redirect = '../index.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit();
    }
}

function requireAdmin($redirect = '../index.php') {
    if (!isAdmin()) {
        header("Location: $redirect");
        exit();
    }
}

function requireCustomer($redirect = '../index.php') {
    if (!isCustomer()) {
        header("Location: $redirect");
        exit();
    }
}

function loginUser($email, $password) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            return ['success' => true, 'role' => $user['role']];
        }
    }
    return ['success' => false, 'message' => 'Invalid email or password.'];
}

function registerUser($name, $email, $password, $phone) {
    $conn = getDBConnection();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'customer')");
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $phone);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Registration successful!'];
    }
    return ['success' => false, 'message' => 'Registration failed. Please try again.'];
}

function logoutUser() {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}
?>
