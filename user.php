<?php
session_start();
require 'db.php';

// Redirect if not logged in or not a user
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user details from users table
$userQuery = $conn->prepare("SELECT * FROM users WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();

// Extract user data
$name = $user['name'];
$email = $user['email'];
$image = $user['image'];
// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['quantity_update'])) {
    $order_id = intval($_POST['order_id']);
    $new_quantity = intval($_POST['quantity_update']);

    $updateQtyStmt = $conn->prepare("UPDATE orders SET quantity = ? WHERE orderid = ?");
    $updateQtyStmt->bind_param("ii", $new_quantity, $order_id);
    $updateQtyStmt->execute();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <style>
        /* Reset and base */
* {
    box-sizing: border-box;
}
body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    background: linear-gradient(135deg, #3b8d99, #6b6b83, #aa4b6b);
    color: #333;
    animation: fadeIn 1s ease forwards;
}

/* Navbar */
nav.navbar {
    background-color: rgba(255, 255, 255, 0.85);
    padding: 15px 30px;
    position: fixed;
    top: 0; left: 0; right: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    backdrop-filter: blur(8px);
    z-index: 1000;
}

nav.navbar .logo {
    font-size: 20px;
    font-weight: bold;
    color: #3b8d99;
    user-select: none;
}

nav.navbar .nav-links a {
    margin-left: 20px;
    color: #3b8d99;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

nav.navbar .nav-links a:hover {
    color: #2a6b72;
}

/* Container with profile info */
.container {
    max-width: 900px;
    margin: 140px auto 60px auto;
    background: #fff;
    border-radius: 15px;
    padding: 40px 30px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    text-align: center;
    animation: slideUp 0.6s ease forwards;
}

h2, h3 {
    margin: 0 0 10px 0;
    color: #3b8d99;
}

.email {
    color: #555;
    font-size: 14px;
    margin-bottom: 20px;
    user-select: text;
}

.profile-img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #3b8d99;
    margin-bottom: 20px;
    box-shadow: 0 4px 10px rgba(59,141,153,0.5);
}

/* Button style */
.btn {
    margin-top: 20px;
    padding: 12px 25px;
    background: #3b8d99;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
    user-select: none;
}

.btn:hover, .btn:focus {
    background: #2a6b72;
    outline: none;
    transform: scale(1.05);
}

/* Orders table styling */
.orders {
    width: 90%;
    max-width: 900px;
    margin: 40px auto 60px auto;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    animation: fadeSlide 0.7s ease forwards;
    overflow-x: auto;
}

.orders table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
}

.orders-table th, .orders-table td {
    padding: 12px 10px;
    text-align: center;
    font-size: 0.95rem;
    user-select: text;
}

.orders-table th {
    background: #3b8d99;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    border-radius: 8px 8px 0 0;
}

.orders-table td {
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: inset 0 0 6px #eaeaea;
    vertical-align: middle;
}

/* Even rows lighter */
.orders-table tr:nth-child(even) td {
    background-color: #f1f1f1;
}

/* Row hover effect */
.orders-table tr:hover td {
    background-color: #d2ebf1;
    transition: background-color 0.3s ease;
}

/* Form inputs inside table */
.orders-table input[type="number"] {
    width: 60px;
    padding: 6px 8px;
    border-radius: 6px;
    border: 1.5px solid #cbd5e1;
    font-size: 0.9rem;
    text-align: center;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.orders-table input[type="number"]:focus {
    border-color: #3b8d99;
    outline: none;
    box-shadow: 0 0 8px #3b8d9966;
}

/* Select dropdown */
.orders-table select {
    padding: 6px 8px;
    border-radius: 6px;
    border: 1.5px solid #cbd5e1;
    font-size: 0.9rem;
    cursor: pointer;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    background-color: white;
}

.orders-table select:focus {
    border-color: #3b8d99;
    outline: none;
    box-shadow: 0 0 8px #3b8d9966;
}

/* Make sure form buttons inside table are consistent */
.orders-table form button {
    padding: 5px 10px;
    font-size: 13px;
    border-radius: 6px;
    border: none;
    background-color: #3b8d99;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    user-select: none;
}

.orders-table form button:hover {
    background-color: #2a6b72;
    transform: scale(1.05);
}

/* Link styling */
.track-link {
    color: #3b8d99;
    font-weight: bold;
    text-decoration: none;
    transition: color 0.3s ease;
}

.track-link:hover {
    color: #29666f;
    text-decoration: underline;
}

/* Animations */
@keyframes fadeSlide {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideUp {
    from {
        transform: translateY(30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 700px) {
    .container, .orders {
        width: 95%;
        padding: 20px 15px;
        margin-top: 120px;
    }
    .orders-table th, .orders-table td {
        font-size: 0.85rem;
        padding: 8px 6px;
    }
    .orders-table input[type="number"], .orders-table select {
        width: 100%;
    }
}

    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($name) ?>!</h2>
    <p class="email"><?= htmlspecialchars($email) ?></p>

    <?php if (!empty($image)): ?>
        <img src="img/<?= htmlspecialchars($image) ?>" alt="Profile Image" class="profile-img">
    <?php else: ?>
        <img src="default-user.png" alt="Default User" class="profile-img">
    <?php endif; ?>

    <p>This is your user dashboard. You can explore your account, manage your info, and view your orders.</p>

    <a href="logout.php"><button class="btn">Logout</button></a>

    
</div>
<?php
// Fetch user orders
$orders = $conn->query("SELECT orders.*, products.productname FROM orders 
                        JOIN products ON orders.productid = products.proid 
                        ORDER BY orderid DESC");
// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE orderid = ?");
    $updateStmt->bind_param("si", $status, $order_id);
    $updateStmt->execute();
}

?>
<h2 style="text-align:center;">All Orders</h2>
<table class='orders-table'border="1" style="width:90%;margin:auto; ">
    <tr>
       <tr>
    <th>ID</th><th>Name</th><th>Email</th><th>Product</th><th>Address</th><th>Quantity</th><th>Status</th>
</tr>

    </tr>
    <?php while ($order = $orders->fetch_assoc()): ?>
    <tr>
        <td><?= $order['orderid'] ?></td>
        <td><?= htmlspecialchars($order['name']) ?></td>
        <td><?= htmlspecialchars($order['email']) ?></td>
        <td><?= htmlspecialchars($order['productname']) ?></td>
        <td><?= htmlspecialchars($order['address']) ?></td>
       <td>
    <form method="post" action="" style="display: flex; align-items: center; gap: 5px;">
        <input type="hidden" name="order_id" value="<?= $order['orderid'] ?>">
        <input type="number" name="quantity_update" value="<?= $order['quantity'] ?>" min="1" style="width: 60px; padding: 5px; text-align: center;">
        <button type="submit" class="btn" style="padding: 5px 10px; font-size: 13px;">Update</button>
    </form>
</td>

        <td>
    <form method="post" action="" onchange="this.submit()" style="margin: 0;">
        <input type="hidden" name="order_id" value="<?= $order['orderid'] ?>">
        <select name="status">
            <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
             <option value="Confirmed" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Confirmed</option>
            <option value="Processing" <?= $order['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
            <option value="Shipped" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
            <option value="Out of Delivery" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Out fo Delivery</option>
            <option value="Delivered" <?= $order['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
            <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            <option value="Returned" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Returned</option>
        </select>
    </form>
</td>
    
        
    </tr>
    <?php endwhile; ?>

</table>

</body>
</html>
