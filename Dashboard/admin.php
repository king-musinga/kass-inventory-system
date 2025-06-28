<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Dashboard - Stock Management</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="bg-white shadow p-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-red-700">Admin Dashboard</h1>
    <a href="dashboard.php" class="text-blue-600 hover:underline">Back to User Dashboard</a>
</header>

<main class="flex-grow container mx-auto p-6">
    <div class="bg-white rounded shadow p-6 space-y-6">
        <h2 class="text-xl font-semibold border-b border-gray-200 pb-2 mb-4">User Management</h2>

        <!-- Example admin panel features -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="manage_users.php" class="block p-6 bg-red-50 border border-red-200 rounded hover:bg-red-100 transition text-center font-semibold text-red-700">
                Manage Users
            </a>

            <a href="system_settings.php" class="block p-6 bg-red-50 border border-red-200 rounded hover:bg-red-100 transition text-center font-semibold text-red-700">
                System Settings
            </a>

            <a href="audit_logs.php" class="block p-6 bg-red-50 border border-red-200 rounded hover:bg-red-100 transition text-center font-semibold text-red-700">
                Audit Logs
            </a>
        </div>
    </div>
</main>

<footer class="bg-white p-4 text-center text-gray-500 text-sm">
    &copy; <?= date('Y'); ?> Stock Management System
</footer>

</body>
</html>
