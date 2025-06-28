<?php
include '../db.php';
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_stock') {
    header('Content-Type: application/json');

    $product_id = intval($_POST['product_id']);
    $type = $_POST['type'] === 'in' ? 'in' : 'out';
    $quantity = intval($_POST['quantity']);

    if ($quantity <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Quantity must be greater than zero.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Selected product does not exist.']);
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO stock (product_id, type, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $product_id, $type, $quantity);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Stock recorded successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error while saving stock.']);
    }
    $stmt->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Goods Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-900">

    <div class="container mx-auto px-4 py-6">
        <div class="mb-6">
            <?php include 'menus.php'; ?>
            <h1 class="text-3xl font-bold mb-2">Inventory Management</h1>
            <p class="text-gray-600">Goods Stock Page</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Stock Form -->
            <div class="bg-white p-6 rounded shadow-md">
                <h2 class="text-xl font-semibold mb-4">Record Goods Stock</h2>
                <form id="stockForm" autocomplete="off" class="space-y-4">
                    <div>
                        <label class="block mb-1 font-medium" for="product_id_select">Select Goods</label>
                        <select id="product_id_select" name="product_id" required class="w-full border border-gray-300 p-2 rounded">
                            <option value="" disabled selected>Select goods</option>
                            <?php
                            $result = $conn->query("SELECT * FROM products");
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium" for="type_select">Type</label>
                        <select id="type_select" name="type" required class="w-full border border-gray-300 p-2 rounded">
                            <option value="in">Stock In</option>
                            <option value="out">Stock Out</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium" for="quantity_input">Quantity</label>
                        <input type="number" name="quantity" id="quantity_input" min="1" required class="w-full border border-gray-300 p-2 rounded" />
                    </div>

                    <button type="submit" id="submitBtn" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Save</button>
                </form>

                <div id="form-feedback" class="mt-4"></div>
            </div>

            <!-- Stock Table -->
            <div class="md:col-span-2">
                <h2 class="text-xl font-semibold mb-4">Goods Stock Status</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300 rounded shadow-sm">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700">
                                <th class="text-left py-2 px-4 border-b">Goods Name</th>
                                <th class="text-left py-2 px-4 border-b">Current Stock</th>
                                <th class="text-left py-2 px-4 border-b">Reorder Level</th>
                                <th class="text-left py-2 px-4 border-b">Stock Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
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
                                $lowStock = $row['current_stock'] < $row['reorder_level'];
                                $percentStock = $row['reorder_level'] > 0
                                    ? min(100, ($row['current_stock'] / $row['reorder_level']) * 100)
                                    : 100;
                                $progressColor = $lowStock ? 'bg-red-500' : 'bg-green-500';
                                ?>
                                <tr class="<?= $lowStock ? 'bg-red-100' : 'bg-white' ?>">
                                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="py-2 px-4 border-b"><?= (int)$row['current_stock'] ?></td>
                                    <td class="py-2 px-4 border-b"><?= (int)$row['reorder_level'] ?></td>
                                    <td class="py-2 px-4 border-b">
                                        <div class="w-full bg-gray-200 rounded h-5 overflow-hidden">
                                            <div class="<?= $progressColor ?> h-full text-white text-xs text-center" style="width: <?= round($percentStock) ?>%">
                                                <?= round($percentStock) ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('#stockForm').submit(function (e) {
            e.preventDefault();
            $('#submitBtn').prop('disabled', true);
            $('#form-feedback').html('');

            $.ajax({
                url: '',
                method: 'POST',
                data: {
                    action: 'save_stock',
                    product_id: $('#product_id_select').val(),
                    type: $('#type_select').val(),
                    quantity: $('#quantity_input').val()
                },
                dataType: 'json',
                success: function (response) {
                    const alertClass = response.status === 'success' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100';
                    $('#form-feedback').html(`
                        <div class="p-2 rounded ${alertClass}">${response.message}</div>
                    `);
                    if (response.status === 'success') {
                        $('#stockForm')[0].reset();
                        setTimeout(() => location.reload(), 1200);
                    }
                },
                error: function () {
                    $('#form-feedback').html('<div class="p-2 rounded bg-red-100 text-red-700">An unexpected error occurred.</div>');
                },
                complete: function () {
                    $('#submitBtn').prop('disabled', false);
                }
            });
        });
    </script>
</body>
</html>
