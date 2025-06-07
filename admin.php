<?php
session_start();
require_once "db.php";

// Ensure admin access
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Flash message
$statusMessage = '';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE orderid = ?");
    $stmt->bind_param("si", $status, $orderId);
    $stmt->execute();

    if ($status === "Delivered") {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE orderid = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();

        if ($order) {
            $stmt = $conn->prepare("INSERT INTO transactions (userid, orderid, total, payment_method, payment_status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("iisss", $order['userid'], $order['orderid'], $order['total'], $order['payment_method'], $status);
            $stmt->execute();
        }
    }

    $statusMessage = "Order status updated successfully.";
}

// Delete product
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM products WHERE proid = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Edit product
$editMode = false;
$editData = [];
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editMode = true;
    $stmt = $conn->prepare("SELECT * FROM products WHERE proid = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
}

// Add product
if (isset($_POST['add'])) {
    $name = $_POST['productname'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $categoryname = $_POST['categoryname'];
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    // Handle category insert or fetch
    $stmt = $conn->prepare("SELECT catid FROM categories WHERE categoryname = ?");
    $stmt->bind_param("s", $categoryname);
    $stmt->execute();
    $stmt->bind_result($catid);
    if (!$stmt->fetch()) {
        $stmt->close();
        $insertCat = $conn->prepare("INSERT INTO categories (categoryname) VALUES (?)");
        $insertCat->bind_param("s", $categoryname);
        $insertCat->execute();
        $catid = $insertCat->insert_id;
    } else {
        $stmt->close();
    }

    $stmt = $conn->prepare("INSERT INTO products (productname, price, size, image, catid) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sissi", $name, $price, $size, $image, $catid);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Update product
if (isset($_POST['update'])) {
    $proid = $_POST['proid'];
    $name = $_POST['productname'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $categoryname = $_POST['categoryname'];

    // Handle category insert or fetch
    $stmt = $conn->prepare("SELECT catid FROM categories WHERE categoryname = ?");
    $stmt->bind_param("s", $categoryname);
    $stmt->execute();
    $stmt->bind_result($catid);
    if (!$stmt->fetch()) {
        $stmt->close();
        $insertCat = $conn->prepare("INSERT INTO categories (categoryname) VALUES (?)");
        $insertCat->bind_param("s", $categoryname);
        $insertCat->execute();
        $catid = $insertCat->insert_id;
    } else {
        $stmt->close();
    }

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target = "uploads/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $stmt = $conn->prepare("UPDATE products SET productname=?, price=?, size=?, image=?, catid=? WHERE proid=?");
        $stmt->bind_param("sissii", $name, $price, $size, $image, $catid, $proid);
    } else {
        $stmt = $conn->prepare("UPDATE products SET productname=?, price=?, size=?, catid=? WHERE proid=?");
        $stmt->bind_param("sisii", $name, $price, $size, $catid, $proid);
    }

    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Delete order
if (isset($_GET['delete_order']) && is_numeric($_GET['delete_order'])) {
    $stmt = $conn->prepare("DELETE FROM orders WHERE orderid = ?");
    $stmt->bind_param("i", $_GET['delete_order']);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Fetch all products
$products = $conn->query("SELECT products.*, categories.categoryname FROM products LEFT JOIN categories ON products.catid = categories.catid");

// Fetch all orders
$orders = $conn->query("SELECT orders.*, products.productname FROM orders JOIN products ON orders.productid = products.proid ORDER BY orderid DESC");

// Get all category names
$categoryList = $conn->query("SELECT * FROM categories");
$categoryMap = [];
while ($cat = $categoryList->fetch_assoc()) {
    $categoryMap[$cat['catid']] = $cat['categoryname'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="admin.css">
</head>
<body>

<header>
    <?php include('navbar.php'); ?>
    <h1>Admin Panel</h1>
</header>

<section>
    <h2>Product List</h2>
    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Price</th><th>Size</th><th>Image</th><th>Category</th><th>Actions</th>
        </tr>
        <?php while ($row = $products->fetch_assoc()) : ?>
            <tr>
                <td><?= $row['proid'] ?></td>
                <td><?= htmlspecialchars($row['productname']) ?></td>
                <td>$<?= $row['price'] ?></td>
                <td><?= htmlspecialchars($row['size']) ?></td>
                <td><img src="uploads/<?= $row['image'] ?>" width="60"></td>
                <td><?= htmlspecialchars($row['categoryname']) ?></td>
                <td>
                    <a href="admin.php?edit=<?= $row['proid'] ?>">Edit</a> |
                    <a href="admin.php?delete=<?= $row['proid'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2 style="text-align:center;"><?= $editMode ? 'Edit Product' : 'Add New Product' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="proid" value="<?= $editMode ? $editData['proid'] : '' ?>">
        <div class="form-group">
            <input type="text" name="productname" placeholder="Product Name" value="<?= $editMode ? htmlspecialchars($editData['productname']) : '' ?>" required>
        </div>
        <div class="form-group">
            <input type="number" name="price" placeholder="Price" value="<?= $editMode ? $editData['price'] : '' ?>" required>
        </div>
        <div class="form-group">
            <input type="text" name="size" placeholder="Size" value="<?= $editMode ? htmlspecialchars($editData['size']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="categoryname">Select Category:</label>
            <select name="categoryname" required>
                <option value="">--Select--</option>
                <?php
                foreach (['Formal Dress', 'Beach Dress', 'Boho Dress', 'Elegant Dress', 'Leather Dress'] as $catName):
                    $selected = ($editMode && isset($editData['catid']) && $categoryMap[$editData['catid']] == $catName) ? 'selected' : '';
                    echo "<option value=\"$catName\" $selected>$catName</option>";
                endforeach;
                ?>
            </select>
        </div>
        <div class="form-group">
            <input type="file" name="image">
        </div>
        <button type="submit" name="<?= $editMode ? 'update' : 'add' ?>"><?= $editMode ? 'Update' : 'Add Product' ?></button>
    </form>

    <h2>Order List</h2>
    <?php if (!empty($statusMessage)) : ?>
        <p style="color: green;"><?= $statusMessage ?></p>
    <?php endif; ?>

    <h2 style="text-align:center;">All Orders</h2>
    <table border="1" style="width:90%;margin:auto;">
        <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Product</th><th>Address</th><th>Status</th><th>Tracking</th><th>Delete</th>
        </tr>
        <?php while ($order = $orders->fetch_assoc()): ?>
        <tr>
            <td><?= $order['orderid'] ?></td>
            <td><?= htmlspecialchars($order['name']) ?></td>
            <td><?= htmlspecialchars($order['email']) ?></td>
            <td><?= htmlspecialchars($order['productname']) ?></td>
            <td><?= htmlspecialchars($order['address']) ?></td>
            <td>
                <form method="post" action="">
                    <input type="hidden" name="order_id" value="<?= $order['orderid'] ?>">
                    <select name="status">
                        <?php
                        $statuses = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Out of Delivery', 'Delivered', 'Cancelled', 'Returned'];
                        foreach ($statuses as $status):
                            $selected = $order['status'] === $status ? 'selected' : '';
                            echo "<option value=\"$status\" $selected>$status</option>";
                        endforeach;
                        ?>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </td>
            <td><a href="trackingupdt.php?id=<?= $order['orderid'] ?>">Edit Tracking</a></td>
            <td><a href="?delete_order=<?= $order['orderid'] ?>" onclick="return confirm('Are you sure you want to delete this order?')">Delete</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>

<footer>&copy; <?= date('Y') ?> Admin Panel</footer>
</body>
</html>
