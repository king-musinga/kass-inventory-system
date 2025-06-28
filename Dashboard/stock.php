<?php
require_once '../db.php';
session_start();

// Handle AJAX stock insert request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_stock') {
    header('Content-Type: application/json');
    
    // Validate inputs
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $type = $_POST['type'] === 'in' ? 'in' : 'out';
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (!$quantity) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Quantity must be greater than zero.']);
        exit;
    }

    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid product selection.']);
        exit;
    }

    // Check product exists
    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Selected product does not exist.']);
        exit;
    }
    $stmt->close();

    // Insert stock record
    try {
        $stmt = $conn->prepare("INSERT INTO stock (product_id, type, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $product_id, $type, $quantity);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Stock recorded successfully.']);
        } else {
            throw new Exception('Database error');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save stock. Please try again.']);
    } finally {
        if (isset($stmt)) $stmt->close();
    }
    exit;
}

// Get products and stock data
$products = $conn->query("SELECT id, name, barcode FROM products ORDER BY name");
$stock_levels = $conn->query("
    SELECT p.id, p.name, p.reorder_level,
    COALESCE(SUM(CASE WHEN s.type = 'in' THEN s.quantity ELSE -s.quantity END), 0) AS current_stock
    FROM products p
    LEFT JOIN stock s ON p.id = s.product_id
    GROUP BY p.id, p.name, p.reorder_level
    ORDER BY p.name
");
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management | Inventory System</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    
    <!-- QR Scanner -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <style>
        .progress-bar {
            transition: width 0.3s ease;
        }
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>

<body class="h-full">
    <div class="min-h-full">
        <?php include 'menus.php' ?>
        
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Stock Management</h1>
                        <p class="mt-1 text-sm text-gray-600">Track and manage inventory levels</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-boxes mr-1"></i>
                            <?= $stock_levels->num_rows ?> Products
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Transaction Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-exchange-alt mr-2 text-blue-500"></i>
                                Stock Transaction
                            </h2>
                        </div>
                        <div class="p-6">
                            <form id="stockForm" class="space-y-4">
                                <div>
                                    <label for="product_id_select" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                                    <select id="product_id_select" name="product_id" required
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="" disabled selected>Select a product</option>
                                        <?php while ($product = $products->fetch_assoc()): ?>
                                            <option value="<?= $product['id'] ?>" data-barcode="<?= htmlspecialchars($product['barcode']) ?>">
                                                <?= htmlspecialchars($product['name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div>
                                    <label for="type_select" class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                                    <select id="type_select" name="type" required
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="in">Stock In (Add Inventory)</option>
                                        <option value="out">Stock Out (Remove Inventory)</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="quantity_input" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                    <input type="number" min="1" id="quantity_input" name="quantity" required
                                        class="mt-1 block w-full shadow-sm sm:text-sm rounded-md border border-gray-300 focus:ring-blue-500 focus:border-blue-500 p-2"
                                        placeholder="Enter quantity">
                                </div>

                                <button type="submit" id="submitBtn"
                                    class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <span id="submitText">Record Transaction</span>
                                    <span id="submitSpinner" class="ml-2 hidden">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                    </span>
                                </button>
                            </form>

                            <div id="form-feedback" class="mt-4"></div>

                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-barcode mr-2 text-blue-500"></i>
                                    Barcode Scanner
                                </h3>
                                <div id="reader" class="w-full border-2 border-gray-200 rounded-md mb-2 hidden"></div>
                                <button id="toggleScanner" class="w-full bg-blue-100 text-blue-700 py-2 px-4 rounded-md text-sm font-medium hover:bg-blue-200 transition-colors">
                                    <i class="fas fa-camera mr-2"></i>
                                    <span id="scannerText">Start Scanner</span>
                                </button>
                                <div id="scan-result" class="mt-2 text-sm hidden p-2 rounded-md"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Status Card -->
                <div class="lg:col-span-3">
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                    <i class="fas fa-clipboard-list mr-2 text-blue-500"></i>
                                    Current Stock Levels
                                </h2>
                                <div class="relative">
                                    <input type="text" id="searchInput" placeholder="Search products..." 
                                        class="pl-8 pr-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table id="stockTable" class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Level</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Status</th>
                                    </tr>
                                </thead>
                             <tbody class="bg-white divide-y divide-gray-200">
    <?php while ($item = $stock_levels->fetch_assoc()): 
        $lowStock = $item['current_stock'] < $item['reorder_level'];
        $percentStock = $item['reorder_level'] > 0 
            ? min(100, ($item['current_stock'] / $item['reorder_level']) * 100) 
            : 100;
    ?>

                                        <tr class="<?= $lowStock ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50' ?> transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <i class="fas fa-box text-blue-500"></i>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $lowStock ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                                    <?= (int)$item['current_stock'] ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                <?= (int)$item['reorder_level'] ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mr-3">
                                                        <div class="h-2.5 rounded-full <?= $lowStock ? 'bg-yellow-500' : 'bg-green-500' ?> progress-bar" 
                                                            style="width: <?= $percentStock ?>%"></div>
                                                    </div>
                                                    <span class="text-xs font-medium <?= $lowStock ? 'text-yellow-600' : 'text-green-600' ?>">
                                                        <?= round($percentStock) ?>%
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#stockTable').DataTable({
                responsive: true,
                order: [[0, 'asc']],
                dom: '<"flex justify-between items-center mb-4"f>rt<"flex justify-between items-center mt-4"lip>',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search products...",
                    lengthMenu: "Show _MENU_ products",
                    info: "Showing _START_ to _END_ of _TOTAL_ products",
                    infoEmpty: "No products available",
                    infoFiltered: "(filtered from _MAX_ total products)"
                }
            });

            // Custom search input
            $('#searchInput').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Scanner toggle functionality
            let scannerActive = false;
            let html5QrcodeScanner = null;
            
            $('#toggleScanner').click(function() {
                if (scannerActive) {
                    stopScanner();
                } else {
                    startScanner();
                }
            });

            function startScanner() {
                $('#scannerText').text('Stop Scanner');
                $('#reader').removeClass('hidden');
                scannerActive = true;
                
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", 
                    { 
                        fps: 10, 
                        qrbox: 250,
                        formatsToSupport: [
                            Html5QrcodeSupportedFormats.EAN_13,
                            Html5QrcodeSupportedFormats.QR_CODE
                        ]
                    },
                    /* verbose= */ false
                );
                
                html5QrcodeScanner.render(onScanSuccess, onScanError);
            }

            function stopScanner() {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.clear().catch(error => {
                        console.error("Failed to clear scanner", error);
                    });
                }
                $('#scannerText').text('Start Scanner');
                $('#reader').addClass('hidden');
                scannerActive = false;
            }

            function onScanSuccess(decodedText) {
                const select = $('#product_id_select');
                const option = select.find(`option[data-barcode="${decodedText}"]`);
                const resultDiv = $('#scan-result');
                
                if (option.length) {
                    select.val(option.val()).trigger('change');
                    showAlert(resultDiv, 'success', 
                        `<i class="fas fa-check-circle mr-1"></i> Product selected: ${option.text()}`);
                } else {
                    showAlert(resultDiv, 'danger',
                        `<i class="fas fa-exclamation-circle mr-1"></i> No product found for scanned code`);
                }
                
                stopScanner();
            }

            function onScanError(error) {
                console.error('Scanner error:', error);
            }

            // Form submission
            $('#stockForm').submit(function(e) {
                e.preventDefault();
                
                const $submitBtn = $('#submitBtn');
                const $submitText = $('#submitText');
                const $submitSpinner = $('#submitSpinner');
                
                // Disable button and show spinner
                $submitBtn.prop('disabled', true);
                $submitText.text('Processing...');
                $submitSpinner.removeClass('hidden');
                
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: {
                        action: 'save_stock',
                        product_id: $('#product_id_select').val(),
                        type: $('#type_select').val(),
                        quantity: $('#quantity_input').val()
                    },
                    dataType: 'json'
                })
                .done(function(response) {
                    const alertClass = response.status === 'success' ? 
                        'bg-green-100 border-l-4 border-green-500 text-green-700' : 
                        'bg-red-100 border-l-4 border-red-500 text-red-700';
                    
                    $('#form-feedback').html(`
                        <div class="${alertClass} p-4 fade-in">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas ${response.status === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm">${response.message}</p>
                                </div>
                            </div>
                        </div>
                    `).removeClass('hidden');
                    
                    if (response.status === 'success') {
                        $('#stockForm')[0].reset();
                        setTimeout(() => location.reload(), 1200);
                    }
                })
                .fail(function(xhr) {
                    $('#form-feedback').html(`
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 fade-in">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm">An unexpected error occurred. Please try again.</p>
                                </div>
                            </div>
                        </div>
                    `).removeClass('hidden');
                })
                .always(function() {
                    $submitBtn.prop('disabled', false);
                    $submitText.text('Record Transaction');
                    $submitSpinner.addClass('hidden');
                });
            });

            function showAlert(element, type, message) {
                const alertClass = type === 'success' ? 
                    'bg-green-100 border-l-4 border-green-500 text-green-700' : 
                    'bg-red-100 border-l-4 border-red-500 text-red-700';
                
                $(element).html(`
                    <div class="${alertClass} p-3 fade-in">
                        <div class="flex items-center">
                            <div class="ml-2 text-sm">
                                ${message}
                            </div>
                        </div>
                    </div>
                `).removeClass('hidden');
                
                setTimeout(() => {
                    $(element).addClass('hidden');
                }, 5000);
            }
        });
    </script>
</body>
</html>