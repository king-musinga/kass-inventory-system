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

<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo & Title -->
            <div class="flex items-center">
                <a href="index.php" class="flex items-center space-x-2 text-blue-600 hover:text-blue-800 font-bold text-lg">
                    <i class="fas fa-warehouse"></i>
                    <span>Inventory System</span>
                </a>
            </div>

            <!-- Navigation Links -->
<div class="hidden sm:flex sm:items-center sm:space-x-6">
    <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 font-medium">Dashboard</a>
    <a href="products.php" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 font-medium">Products</a>
    <a href="stock.php" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 font-medium">Stock</a>
    <a href="report.php" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 font-medium">Reports</a>
    <a href="settings.php" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 font-medium">Settings</a>
    <a href="admin.php" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 font-medium">Admin</a>
    <a href="../login.php" class="text-sm text-red-600 hover:underline dark:text-red-400">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
    
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
<body class="bg-white dark:bg-gray-900 transition-colors duration-300 min-h-screen flex items-center justify-center">

  
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
<!-- 
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
    </script> -->