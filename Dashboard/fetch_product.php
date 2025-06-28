<?php
include 'db.php';

if (isset($_GET['barcode'])) {
    $barcode = mysqli_real_escape_string($conn, $_GET['barcode']);
    $result = mysqli_query($conn, "SELECT * FROM products WHERE barcode = '$barcode'");

    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        echo "<div class='alert alert-success'>
                <strong>Product Found:</strong><br>
                Name: {$product['name']}<br>
                Stock: {$product['quantity']}<br>
                Price: {$product['price']}
              </div>";
    } else {
        echo "<div class='alert alert-danger'>Product not found for barcode: $barcode</div>";
    }
}
?>
