<?php
session_start();
include "../db.php";

// Optional: restrict access to admins
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     header('Location: ../login.php');
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = trim($_POST['site_name']);
    $site_description = trim($_POST['site_description']);
    $theme = trim($_POST['theme']);
    $language = trim($_POST['language']);

    if (!$site_name || !$site_description || !$theme || !$language) {
        $_SESSION['error'] = "Please fill in all fields.";
    } else {
        $check = $conn->query("SELECT id FROM settings WHERE id = 1");
        if ($check && $check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE settings SET site_name = ?, site_description = ?, theme = ?, language = ? WHERE id = 1");
        } else {
            $stmt = $conn->prepare("INSERT INTO settings (id, site_name, site_description, theme, language) VALUES (1, ?, ?, ?, ?)");
        }

        $stmt->bind_param("ssss", $site_name, $site_description, $theme, $language);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Settings updated successfully.";
            $_SESSION['theme'] = $theme; // Store theme in session
            $_SESSION['language'] = $language; // Store language in session
        } else {
            $_SESSION['error'] = "Failed to update settings.";
        }
        $stmt->close();
    }

    header('Location: settings.php');
    exit();
}

$result = $conn->query("SELECT * FROM settings WHERE id = 1");
$settings = $result ? $result->fetch_assoc() : null;

if (!$settings) {
    $settings = [
        'site_name' => '',
        'site_description' => '',
        'theme' => 'light',
        'language' => 'en',
    ];
}

// Get current theme from session or settings
$current_theme = $_SESSION['theme'] ?? $settings['theme'];
$current_language = $_SESSION['language'] ?? $settings['language'];
?>
<!DOCTYPE html>
<html lang="en" class="<?= $current_theme === 'dark' ? 'dark' : '' ?> scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Settings - Stock Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                    },
                    transitionProperty: {
                        'width': 'width',
                        'height': 'height',
                        'spacing': 'margin, padding',
                    },
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .language-flag {
            width: 20px;
            height: 15px;
            display: inline-block;
            margin-right: 8px;
            background-size: cover;
            border-radius: 2px;
        }
        .flag-en { background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA2MCAzMCI+PHBhdGggZmlsbD0iIzAxMjE2OSIgZD0iTTAgMGg2MHYzMEgweiIvPjxwYXRoIGZpbGw9IiNGRkYiIGQ9Ik0wIDBoNjB2M0gweiIvPjxwYXRoIGZpbGw9IiNGRkYiIGQ9Ik0wIDI3aDYwdjNIMHoiLz48cGF0aCBmaWxsPSIjRkZGIiBkPSJNMCAxMGg2MHYzSDB6Ii8+PHBhdGggZmlsbD0iI0ZGRiIgZD0iTTAgMjBoNjB2M0gweiIvPjxwYXRoIGZpbGw9IiNGRkYiIGQ9Ik0yNyAwaDN2MzBoLTN6Ii8+PHBhdGggZmlsbD0iI0ZGRiIgZD0iTTEwIDBoM3YzMGgtM3oiLz48cGF0aCBmaWxsPSIjRkZGIiBkPSJNMjAgMGgzLjAwM3YzMEgyMHoiLz48cGF0aCBmaWxsPSIjRkZGIiBkPSJNNDAgMGgzLjAwM3YzMEg0MHoiLz48cGF0aCBmaWxsPSIjRkZGIiBkPSJNNTAgMGgzdjMwaC0zeiIvPjxwYXRoIGZpbGw9IiNDODEwMTgiIGQ9Ik0wIDBoMzB2MThIMHoiLz48cGF0aCBmaWxsPSIjMDEyMTY5IiBkPSJNMi4zIDIuM2g1LjR2NS40SDIuM3oiLz48cGF0aCBmaWxsPSIjRkZGIiBkPSJNMi4zIDIuM2gxLjh2MS44SDIuM3oiLz48L3N2Zz4='); }
        .flag-fr { background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA2MCAzMCI+PHBhdGggZmlsbD0iIzAwNTBBNCIgZD0iTTAgMGgyMHYzMEgweiIvPjxwYXRoIGZpbGw9IiNGRkYiIGQ9Ik0yMCAwaDIwdjMwSDB6Ii8+PHBhdGggZmlsbD0iI0VEMjAwMCIgZD0iTTQwIDBoMjB2MzBINDB6Ii8+PC9zdmc+'); }
        .flag-es { background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA2MCAzMCI+PHBhdGggZmlsbD0iI0M2MUIxQiIgZD0iTTAgMGg2MHYzMEgweiIvPjxwYXRoIGZpbGw9IiNGRkM0MDAiIGQ9Ik0wIDVoNjB2MjBIMHoiLz48L3N2Zz4='); }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex flex-col transition-colors duration-300">
    <!-- Enhanced Navigation Bar -->
    <nav class="bg-white dark:bg-gray-800 shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-xl font-bold text-gray-800 dark:text-white">
                        <?= htmlspecialchars($settings['site_name'] ?: 'Stock Management') ?>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-6">
                    <!-- Dashboard Link -->
                    <a href="index.php" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        Dashboard
                    </a>
                    
                    <!-- Settings Link (Active) -->
                    <a href="settings.php" class="text-primary-600 dark:text-primary-400 font-medium">
                        Settings
                    </a>
                    
                    <!-- Language Dropdown -->
                    <div class="relative dropdown">
                        <button class="flex items-center space-x-1 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                            <span class="language-flag flag-<?= $current_language ?>"></span>
                            <span><?= strtoupper($current_language) ?></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 hidden border border-gray-200 dark:border-gray-700">
                            <form method="POST" action="settings.php" class="space-y-1">
                                <button type="submit" name="language" value="en" class="w-full text-left px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center <?= $current_language === 'en' ? 'bg-gray-100 dark:bg-gray-700' : '' ?>">
                                    <span class="language-flag flag-en mr-2"></span> English
                                </button>
                                <button type="submit" name="language" value="fr" class="w-full text-left px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center <?= $current_language === 'fr' ? 'bg-gray-100 dark:bg-gray-700' : '' ?>">
                                    <span class="language-flag flag-fr mr-2"></span> French
                                </button>
                                <button type="submit" name="language" value="es" class="w-full text-left px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center <?= $current_language === 'es' ? 'bg-gray-100 dark:bg-gray-700' : '' ?>">
                                    <span class="language-flag flag-es mr-2"></span> Spanish
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Theme Toggle -->
                    <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg p-2 transition-colors">
                        <svg id="theme-toggle-dark-icon" class="<?= $current_theme === 'dark' ? 'hidden' : '' ?> w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="<?= $current_theme === 'light' ? 'hidden' : '' ?> w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8 flex-grow">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">System Settings</h1>
            <a href="index.php" class="flex items-center text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-6 rounded-lg bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-100 px-4 py-3 relative transition-all duration-300" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <strong class="font-bold">Success! </strong>
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></span>
                </div>
                <button onclick="this.parentElement.style.display='none'" aria-label="Close" class="absolute top-3 right-3 text-green-700 dark:text-green-100 hover:text-green-900 dark:hover:text-green-300 font-bold transition-colors">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 rounded-lg bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-100 px-4 py-3 relative transition-all duration-300" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <strong class="font-bold">Error! </strong>
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                </div>
                <button onclick="this.parentElement.style.display='none'" aria-label="Close" class="absolute top-3 right-3 text-red-700 dark:text-red-100 hover:text-red-900 dark:hover:text-red-300 font-bold transition-colors">&times;</button>
            </div>
        <?php endif; ?>

        <form method="POST" action="settings.php" class="bg-white dark:bg-gray-800 shadow-xl rounded-xl p-8 max-w-3xl mx-auto border border-gray-200 dark:border-gray-700 transition-all duration-300">
            <div class="space-y-6">
                <div>
                    <label for="site_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Site Name</label>
                    <input
                        type="text"
                        id="site_name"
                        name="site_name"
                        required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent transition-all duration-300"
                        value="<?= htmlspecialchars($settings['site_name']); ?>"
                        placeholder="Enter your site name"
                    />
                </div>

                <div>
                    <label for="site_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Site Description</label>
                    <textarea
                        id="site_description"
                        name="site_description"
                        rows="4"
                        required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent transition-all duration-300 resize-none"
                        placeholder="Enter a brief description of your site"
                    ><?= htmlspecialchars($settings['site_description']); ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Theme</label>
                        <select
                            id="theme"
                            name="theme"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent transition-all duration-300 appearance-none"
                        >
                            <option value="light" <?= $current_theme === 'light' ? 'selected' : ''; ?>>Light</option>
                            <option value="dark" <?= $current_theme === 'dark' ? 'selected' : ''; ?>>Dark</option>
                        </select>
                    </div>

                    <div>
                        <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Language</label>
                        <select
                            id="language"
                            name="language"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent transition-all duration-300 appearance-none"
                        >
                            <option value="en" <?= $current_language === 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="fr" <?= $current_language === 'fr' ? 'selected' : ''; ?>>French</option>
                            <option value="es" <?= $current_language === 'es' ? 'selected' : ''; ?>>Spanish</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full md:w-auto px-8 py-3 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold shadow-lg transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    >
                        Save Settings
                    </button>
                </div>
            </div>
        </form>
    </main>

    <footer class="bg-gray-100 dark:bg-gray-800 text-center py-4 text-gray-600 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-gray-700 transition-colors duration-300">
        &copy; <?= date('Y'); ?> <?= htmlspecialchars($settings['site_name'] ?: 'Stock Management System') ?>. All rights reserved.
    </footer>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
        
        if (localStorage.getItem('color-theme') === 'dark' || (!localStorage.getItem('color-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            document.documentElement.classList.remove('dark');
            themeToggleDarkIcon.classList.remove('hidden');
        }
        
        themeToggle.addEventListener('click', function() {
            // Toggle icons
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');
            
            // Toggle theme
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            }
        });
    </script>
</body>
</html>