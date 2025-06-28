<?php
session_start();
include "../db.php";

// Optional: Restrict access to admin users only
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     header('Location: ../login.php');
//     exit();
// }

// Handle user deletion
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);

    if ($deleteId === $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete yourself!";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            $_SESSION['message'] = "User deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete user.";
        }
        $stmt->close();
    }

    header("Location: manage_users.php");
    exit();
}

// Fetch users from DB
$query = "SELECT id, firstname, lastname, email FROM users ORDER BY id ASC";
$result = $conn->query($query);

if (!$result) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        .action-btns .btn {
            margin-right: 5px;
        }
        .last-col {
            white-space: nowrap;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people-fill"></i> User Management</h2>
        <div>
            <a href="add_user.php" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add New User
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])) : ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['message']);
            unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])) : ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th class="last-col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= $user['id']; ?></td>
                                <td><?= htmlspecialchars($user['firstname']); ?></td>
                                <td><?= htmlspecialchars($user['lastname']); ?></td>
                                <td><?= htmlspecialchars($user['email']); ?></td>
                                <td class="last-col action-btns">
                                    <a href="edit_user.php?id=<?= $user['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="?delete=<?= $user['id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete <?= addslashes(htmlspecialchars($user['firstname'] . ' ' . $user['lastname'])); ?>?');"
                                       title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; 
                        $result->free(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
