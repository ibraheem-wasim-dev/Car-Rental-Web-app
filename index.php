<?php
require_once 'includes/auth.php';

// Handle POST
$error = '';
$success = '';
$mode = $_GET['mode'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $result = loginUser($email, $password);
            if ($result['success']) {
                header('Location: ' . ($result['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php'));
                exit();
            } else {
                $error = $result['message'];
            }
        } elseif ($_POST['action'] === 'register') {
            $name = sanitize($_POST['name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $phone = sanitize($_POST['phone'] ?? '');

            // Validate name: required, only letters/spaces, 2-50 chars
            if (strlen($name) < 2 || strlen($name) > 50) {
                $error = 'Full name must be between 2 and 50 characters.';
            } elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $name)) {
                $error = 'Full name can only contain letters, spaces, hyphens, and apostrophes.';
            } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif (!empty($phone) && !preg_match('/^[\+\d\s\-\(\)]{7,20}$/', $phone)) {
                $error = 'Please enter a valid phone number.';
            } else {
                $result = registerUser($name, $email, $password, $phone);
                if ($result['success']) {
                    $success = 'Account created! Please login.';
                    $mode = 'login';
                } else {
                    $error = $result['message'];
                }
            }
        }
    }
}

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'customer/dashboard.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveEase - Car Rental Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .tab-switcher {
            display: flex;
            background: var(--bg-secondary);
            border-radius: var(--radius-sm);
            padding: 4px;
            margin-bottom: 28px;
        }
        .tab-btn {
            flex: 1;
            padding: 10px;
            border: none;
            background: none;
            color: var(--text-secondary);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .tab-btn.active {
            background: var(--bg-card);
            color: var(--text-primary);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .demo-credentials {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 14px 16px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .demo-credentials h4 {
            font-size: 12px;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .demo-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            color: var(--text-secondary);
        }
        .demo-row code {
            color: var(--text-primary);
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="auth-page">
    <div class="auth-bg"></div>
    
    <div class="auth-card">
        <div class="auth-brand">
            <div class="auth-brand-icon">🚗</div>
            <div class="auth-brand-name">DriveEase</div>
            <div class="auth-brand-tagline">Car Rental Management System</div>
        </div>

        <div class="tab-switcher">
            <button class="tab-btn <?= $mode === 'login' ? 'active' : '' ?>" onclick="switchMode('login')">Sign In</button>
            <button class="tab-btn <?= $mode === 'register' ? 'active' : '' ?>" onclick="switchMode('register')">Create Account</button>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">⚠ <?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">✓ <?= $success ?></div>
        <?php endif; ?>

        <!-- LOGIN FORM -->
        <div id="login-form" style="display: <?= $mode === 'login' ? 'block' : 'none' ?>">
            <div class="demo-credentials">
                <h4>Demo Credentials</h4>
                <div class="demo-row"><span>Admin</span><code>admin@carrental.com / password</code></div>
                <div class="demo-row"><span>Customer</span><code>john@example.com / password</code></div>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary w-full" style="justify-content:center; padding:13px; font-size:15px;">
                    Sign In →
                </button>
            </form>
        </div>

        <!-- REGISTER FORM -->
        <div id="register-form" style="display: <?= $mode === 'register' ? 'block' : 'none' ?>">
            <form method="POST" onsubmit="return validateRegisterForm(this)">
                <input type="hidden" name="action" value="register">
                <div class="form-row">
                    <div class="form-group mb-0">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" id="reg-name" class="form-control" placeholder="John Doe" required minlength="2" maxlength="50">
                        <div id="name-error" style="color:var(--danger);font-size:12px;margin-top:4px;display:none"></div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" id="reg-phone" class="form-control" placeholder="+1-555-0100">
                        <div id="phone-error" style="color:var(--danger);font-size:12px;margin-top:4px;display:none"></div>
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password <span style="color:var(--text-muted);font-weight:400">(min. 6 characters)</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary w-full" style="justify-content:center; padding:13px; font-size:15px;">
                    Create Account →
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function switchMode(mode) {
    document.getElementById('login-form').style.display = mode === 'login' ? 'block' : 'none';
    document.getElementById('register-form').style.display = mode === 'register' ? 'block' : 'none';
    document.querySelectorAll('.tab-btn').forEach((btn, i) => {
        btn.classList.toggle('active', (i === 0) === (mode === 'login'));
    });
}

function validateRegisterForm(form) {
    let valid = true;

    // Name validation
    const name = form.querySelector('#reg-name');
    const nameError = document.getElementById('name-error');
    const nameVal = name.value.trim();
    if (nameVal.length < 2 || nameVal.length > 50) {
        nameError.textContent = 'Name must be between 2 and 50 characters.';
        nameError.style.display = 'block';
        name.style.borderColor = 'var(--danger)';
        valid = false;
    } else if (!/^[a-zA-Z\s'\-]+$/.test(nameVal)) {
        nameError.textContent = 'Name can only contain letters, spaces, hyphens, and apostrophes.';
        nameError.style.display = 'block';
        name.style.borderColor = 'var(--danger)';
        valid = false;
    } else {
        nameError.style.display = 'none';
        name.style.borderColor = '';
    }

    // Phone validation (optional but if filled must be valid)
    const phone = form.querySelector('#reg-phone');
    const phoneError = document.getElementById('phone-error');
    if (phone.value.trim() && !/^[\+\d\s\-\(\)]{7,20}$/.test(phone.value.trim())) {
        phoneError.textContent = 'Please enter a valid phone number.';
        phoneError.style.display = 'block';
        phone.style.borderColor = 'var(--danger)';
        valid = false;
    } else {
        phoneError.style.display = 'none';
        phone.style.borderColor = '';
    }

    return valid;
}
</script>
</body>
</html>
