<?php
session_start();
include "../db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
    } elseif ($password !== $password_confirm) {
        $_SESSION['error'] = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $_SESSION['error'] = "Email already registered.";
        } else {
            // Insert user with hashed password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $first_name, $last_name, $email, $password_hash);
            if ($stmt->execute()) {
                $_SESSION['message'] = "User added successfully.";
                header("Location: manage_users.php");
                exit();
            } else {
                $_SESSION['error'] = "Failed to add user.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add New User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-4">
    <h2><i class="bi bi-plus-circle"></i> Add New User</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="" method="POST" novalidate>
        <div class="mb-3">
            <label for="first_name" class="form-label">First Name *</label>
            <input type="text" name="first_name" id="first_name" class="form-control" required />
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name *</label>
            <input type="text" name="last_name" id="last_name" class="form-control" required />
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email *</label>
            <input type="email" name="email" id="email" class="form-control" required />
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password *</label>
            <input type="password" name="password" id="password" class="form-control" required minlength="6" />
        </div>
        <div class="mb-3">
            <label for="password_confirm" class="form-label">Confirm Password *</label>
            <input type="password" name="password_confirm" id="password_confirm" class="form-control" required minlength="6" />
        </div>
        <button type="submit" class="btn btn-primary">Add User</button>
        <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
