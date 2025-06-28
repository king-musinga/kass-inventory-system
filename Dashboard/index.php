<?php
session_start();
include "../db.php";

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$session_timeout = 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?session_expired=true");
    exit();
}
$_SESSION['last_activity'] = time();

$display_name = htmlspecialchars($_SESSION['user_firstname'] ?? $_SESSION['user_email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Inventory System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">

    <!-- Navbar -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="./kass_png_logo.png" alt="Logo" class="w-10 h-10 object-cover">
                <h1 class="text-xl font-bold">Inventory System</h1>
            </div>
            <nav>
                <?php include "menus.php"; ?>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-10">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold mb-2">Welcome, <?= $display_name ?> 👋</h2>
            <p class="text-gray-600 text-lg">You're logged into the Inventory Management System Dashboard.</p>
            <p class="text-sm text-gray-500 mt-1">Manage your goods, monitor stock, and take charge of inventory flow.</p>
        </div>

        <!-- Hero / Illustration -->
        <div class="flex justify-center">
            <img src="./The mobile cycle count applications are usually….jpeg"
                 alt="Inventory Illustration"
                 class="rounded-lg shadow-lg max-w-full w-[900px] h-[330px] object-cover">
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white shadow py-4 mt-10">
        <div class="text-center text-sm text-gray-500">
            &copy; <?= date("Y") ?> Inventory System. All rights reserved.
        </div>
    </footer>
</body>
</html>
