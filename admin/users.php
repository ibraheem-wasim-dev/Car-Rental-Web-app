<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/layout.php';
requireAdmin('../index.php');

$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - DriveEase Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php renderSidebar('admin', 'users'); ?>
    
    <div class="main-content">
        <?php renderTopbar('Users', 'All registered users'); ?>
        
        <div class="page-content">
            <div class="card">
                <div class="card-header">
                    <div class="flex items-center gap-12">
                        <h3 class="card-title">All Users (<?= count($users) ?>)</h3>
                        <div class="search-bar">
                            <span>🔍</span>
                            <input type="text" id="search-users" placeholder="Search users...">
                        </div>
                    </div>
                </div>
                <div class="table-wrapper">
                    <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">👥</div>
                        <div class="empty-title">No users yet</div>
                    </div>
                    <?php else: ?>
                    <table id="users-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Total Rentals</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-12">
                                        <div class="user-avatar" style="width:32px;height:32px;font-size:13px">
                                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                        </div>
                                        <div class="fw-600"><?= htmlspecialchars($u['name']) ?></div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge badge-accent">Admin</span>
                                    <?php else: ?>
                                    <span class="badge badge-info">Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $u['total_rentals'] ?></td>
                                <td style="font-size:13px;color:var(--text-muted)"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
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
