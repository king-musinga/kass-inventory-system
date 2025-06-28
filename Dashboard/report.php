<?php
require_once '../db.php';

// Handle AJAX stock insert request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_stock') {
    header('Content-Type: application/json');
    
    // Validate and sanitize inputs
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $type = ($_POST['type'] === 'in') ? 'in' : 'out';
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);

    // Validate required fields
    if (!$product_id || !$quantity) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Invalid input data. Please check your entries.'
        ]);
        exit;
    }

    // Verify product exists
    $product_exists = false;
    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->store_result();
    $product_exists = ($stmt->num_rows > 0);
    $stmt->close();

    if (!$product_exists) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Selected product not found in database.'
        ]);
        exit;
    }

    // Process stock transaction
    try {
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("INSERT INTO stock (product_id, type, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $product_id, $type, $quantity);
        
        if ($stmt->execute()) {
            $conn->commit();
            echo json_encode([
                'status' => 'success', 
                'message' => 'Stock transaction recorded successfully.'
            ]);
        } else {
            throw new Exception('Database execution failed');
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Stock transaction error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Failed to process stock transaction. Please try again.'
        ]);
    } finally {
        if (isset($stmt)) $stmt->close();
    }
    exit;
}

// Get product data for the form
$products = [];
$result = $conn->query("SELECT id, name, barcode FROM products ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Get current stock levels
$stock_levels = [];
$query = "
    SELECT 
        p.id, p.name, p.reorder_level,
        COALESCE(SUM(CASE WHEN s.type = 'in' THEN s.quantity ELSE -s.quantity END), 0) AS current_stock
    FROM products p
    LEFT JOIN stock s ON p.id = s.product_id
    GROUP BY p.id, p.name, p.reorder_level
    ORDER BY p.name
";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $stock_levels[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management | Inventory Control System</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap4.min.css">
</head>

<body class="h-full">
    <div class="min-h-full">
        <?php include 'menus.php' ?>
        
        <header class="bg-blue-600 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-white">Inventory Control System</h1>
                    <p class="mt-1 text-blue-100">Comprehensive Stock Management</p>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Transaction Panel -->
                <div class="lg:col-span-1">
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 border-b pb-4 mb-4">
                            <i class="fas fa-clipboard-list mr-2"></i>Stock Transaction
                        </h2>
                        
                        <form id="stockForm" novalidate>
                            <div class="mb-4">
                                <label for="product_id_select" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                                <select name="product_id" id="product_id_select" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" required>
                                    <option value="" disabled selected>Select a product</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= $product['id'] ?>" 
                                            data-barcode="<?= htmlspecialchars($product['barcode']) ?>">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="hidden mt-1 text-sm text-red-600">Please select a product</div>
                            </div>

                            <div class="mb-4">
                                <label for="type_select" class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                                <select name="type" id="type_select" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" required>
                                    <option value="in">Stock In (Add Inventory)</option>
                                    <option value="out">Stock Out (Remove Inventory)</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="quantity_input" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                <input type="number" min="1" name="quantity" id="quantity_input" 
                                    class="mt-1 block w-full shadow-sm sm:text-sm rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Enter quantity" required>
                                <div class="hidden mt-1 text-sm text-red-600">Please enter a valid quantity (minimum 1)</div>
                            </div>

                            <button type="submit" id="submitBtn" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <span id="submitText">Record Transaction</span>
                                <span id="submitSpinner" class="ml-2 hidden">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                </span>
                            </button>
                        </form>

                        <div id="form-feedback" class="mt-4"></div>

                        <hr class="my-6 border-gray-200">

                        <h3 class="text-lg font-medium text-gray-900 mb-3">
                            <i class="fas fa-barcode mr-2"></i>Barcode Scanner
                        </h3>
                        <div class="flex flex-col items-center">
                            <div id="reader" class="w-full max-w-xs mb-3 border-2 border-gray-200 rounded-md overflow-hidden"></div>
                            <button id="startScanner" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-camera mr-2"></i>Start Scanner
                            </button>
                            <div id="scan-result" class="mt-3 w-full p-3 rounded-md hidden"></div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Dashboard -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-6 py-4 bg-gray-800 text-white">
                            <h2 class="text-lg font-medium">
                                <i class="fas fa-boxes mr-2"></i>Inventory Status Overview
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table id="stockTable" class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Level</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Status</th>
                                        </tr>
                                    </thead><tbody class="bg-white divide-y divide-gray-200">
    <?php foreach ($stock_levels as $item): 
        $lowStock = $item['current_stock'] < $item['reorder_level'];
        $percentStock = ($item['reorder_level'] > 0) 
            ? min(100, ($item['current_stock'] / $item['reorder_level']) * 100) 
            : 100;
        $statusClass = $lowStock ? 'text-yellow-500' : 'text-green-500';
    ?>

                                            <tr class="<?= $lowStock ? 'bg-red-50' : '' ?> hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><?= (int)$item['current_stock'] ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><?= (int)$item['reorder_level'] ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-1 h-4 bg-gray-200 rounded-full overflow-hidden mr-3">
                                                            <div class="h-full <?= $lowStock ? 'bg-yellow-400' : 'bg-green-500' ?>" 
                                                                style="width: <?= $percentStock ?>%"></div>
                                                        </div>
                                                        <span class="text-xs font-semibold <?= $statusClass ?>">
                                                            <?= round($percentStock) ?>%
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <!-- Application Script -->
    <script>
        (function($) {
            "use strict";
            
            let html5QrcodeScanner = null;
            
            // Initialize when DOM is ready
            $(document).ready(function() {
                initDataTable();
                setupFormHandling();
                setupScanner();
            });
            
            /**
             * Initialize DataTable with enhanced options
             */
            function initDataTable() {
                $('#stockTable').DataTable({
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
            }
            
            /**
             * Setup barcode scanner functionality
             */
            function setupScanner() {
                $('#startScanner').on('click', function() {
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.clear();
                        $('#reader').empty();
                    }
                    
                    function onScanSuccess(decodedText) {
                        const select = $('#product_id_select');
                        const option = select.find(`option[data-barcode="${decodedText}"]`);
                        const resultDiv = $('#scan-result');
                        
                        if (option.length) {
                            select.val(option.val()).trigger('change');
                            showAlert(resultDiv, 'success', 
                                `<i class="fas fa-check-circle mr-2"></i>Product selected: ${option.text()}`);
                        } else {
                            showAlert(resultDiv, 'danger',
                                `<i class="fas fa-exclamation-circle mr-2"></i>No product found for: ${decodedText}`);
                        }
                        
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.clear();
                        }
                    }
                    
                    html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
                        fps: 10, 
                        qrbox: 250,
                        formatsToSupport: [
                            Html5QrcodeSupportedFormats.EAN_13,
                            Html5QrcodeSupportedFormats.QR_CODE
                        ]
                    });
                    html5QrcodeScanner.render(onScanSuccess);
                });
            }
            
            /**
             * Configure form submission handling
             */
            function setupFormHandling() {
                const $form = $('#stockForm');
                const $submitBtn = $('#submitBtn');
                const $submitText = $('#submitText');
                const $submitSpinner = $('#submitSpinner');
                
                // Client-side validation
                $form.on('submit', function(e) {
                    e.preventDefault();
                    
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                        $(this).addClass('was-validated');
                        return;
                    }
                    
                    submitForm();
                });
                
                // Handle form submission via AJAX
                function submitForm() {
                    toggleSubmitButton(true);
                    
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
                        if (response.status === 'success') {
                            showAlert('#form-feedback', 'success', response.message);
                            $form[0].reset();
                            $form.removeClass('was-validated');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert('#form-feedback', 'danger', response.message);
                        }
                    })
                    .fail(function(xhr) {
                        const message = (xhr.responseJSON && xhr.responseJSON.message) 
                            ? xhr.responseJSON.message 
                            : 'An unexpected error occurred. Please try again.';
                        showAlert('#form-feedback', 'danger', message);
                    })
                    .always(function() {
                        toggleSubmitButton(false);
                    });
                }
                
                // Toggle submit button state
                function toggleSubmitButton(loading) {
                    $submitBtn.prop('disabled', loading);
                    $submitText.text(loading ? 'Processing...' : 'Record Transaction');
                    $submitSpinner.toggleClass('hidden', !loading);
                }
            }
            
            /**
             * Show alert message
             * @param {string} target - jQuery selector for target element
             * @param {string} type - Alert type (success, danger, etc.)
             * @param {string} message - HTML message content
             * @param {number} [timeout] - Optional timeout in ms to auto-hide
             */
            function showAlert(target, type, message, timeout = 5000) {
                const $element = $(target);
                const alertClass = type === 'success' 
                    ? 'bg-green-100 border-green-400 text-green-700' 
                    : 'bg-red-100 border-red-400 text-red-700';
                
                $element.html(`
                    <div class="border px-4 py-3 rounded relative ${alertClass}">
                        ${message}
                    </div>
                `).removeClass('hidden');
                
                if (timeout) {
                    setTimeout(() => $element.fadeOut(), timeout);
                }
            }
        })(jQuery);
    </script>
</body>
</html>[]