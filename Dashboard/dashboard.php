<?php
include '../db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Goods</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

  <div class="p-6 bg-white shadow mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Manage Goods</h1>
    <p class="text-gray-500">Add, view, or remove goods</p>
  </div>

  <div class="px-6">
    <form class="bg-white p-6 rounded-xl shadow mb-6" action="save_good.php" method="POST">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block mb-1 text-gray-600 font-medium">Product Name</label>
          <input type="text" name="name" required class="w-full border px-3 py-2 rounded-lg" />
        </div>
        <div>
          <label class="block mb-1 text-gray-600 font-medium">Reorder Level</label>
          <input type="number" name="reorder_level" min="1" required class="w-full border px-3 py-2 rounded-lg" />
        </div>
        <div class="flex items-end">
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Good</button>
        </div>
      </div>
    </form>

    <div class="bg-white rounded-xl shadow p-4">
      <h2 class="text-xl font-semibold mb-4 text-gray-700">Goods List</h2>
      <table class="w-full table-auto text-sm text-left">
        <thead class="bg-gray-200 text-gray-600 uppercase text-xs">
          <tr>
            <th class="px-4 py-2">#</th>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Reorder Level</th>
            <th class="px-4 py-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
          $i = 1;
          while ($row = $result->fetch_assoc()):
          ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-2"><?= $i++ ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['name']) ?></td>
            <td class="px-4 py-2"><?= (int)$row['reorder_level'] ?></td>
            <td class="px-4 py-2">
              <form action="delete_good.php" method="POST" onsubmit="return confirm('Delete this good?')">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" class="text-red-500 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>
