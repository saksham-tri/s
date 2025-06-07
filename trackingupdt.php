<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Update tracking info
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderid = intval($_POST['orderid']);
    $courier_name = $_POST['courier_name'];
    $tracking_number = $_POST['tracking_number'];
    $tracking_url = $_POST['tracking_url'];

    $stmt = $conn->prepare("UPDATE orders SET courier_name = ?, tracking_number = ?, tracking_url = ? WHERE orderid = ?");
    $stmt->bind_param("sssi", $courier_name, $tracking_number, $tracking_url, $orderid);
    $stmt->execute();
}

// Fetch all orders
$orders = $conn->query("SELECT * FROM orders ORDER BY orderid DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Order Tracking</title>
    <link rel="stylesheet" href="style1.css">
    <style>
       /* General body and fonts */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f7fb;
    margin: 0;
    padding: 20px 10px;
    color: #333;
    min-height: 100vh;
}

/* Centered page title */
h2 {
    text-align: center;
    color: #007BFF;
    margin-bottom: 25px;
    user-select: none;
}

/* Table styling */
table {
    width: 95%;
    margin: 0 auto 40px auto;
    border-collapse: separate;
    border-spacing: 0 10px;
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.15);
    border-radius: 12px;
    background: #ffffff;
    overflow: hidden;
    animation: fadeSlideIn 0.6s ease forwards;
}

/* Header styles */
table th {
    background-color: #007BFF;
    color: white;
    font-weight: 600;
    padding: 15px 10px;
    user-select: none;
    text-transform: uppercase;
    font-size: 0.9rem;
}

/* Table cells */
table td {
    background: #f9fbff;
    padding: 12px 10px;
    text-align: center;
    vertical-align: middle;
    font-size: 0.95rem;
    border-bottom: none !important;
    border-radius: 6px;
    box-shadow: inset 0 0 4px #e1e7f0;
}

/* Add some space between rows by making rows block elements */
table tr {
    display: table-row;
    box-shadow: 0 1px 3px rgb(0 0 0 / 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: default;
}

/* Hover effect on rows */
table tr:hover {
    background-color: #e6f0ff;
    box-shadow: 0 6px 15px rgb(0 123 255 / 0.25);
    transform: translateY(-3px);
}

/* Inputs in table */
input[type="text"], input[type="url"] {
    width: 90%;
    padding: 6px 8px;
    font-size: 0.9rem;
    border: 1.8px solid #cbd5e1;
    border-radius: 6px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    background: #f9fbff;
    color: #333;
}

/* Focus style for inputs */
input[type="text"]:focus, input[type="url"]:focus {
    border-color: #007BFF;
    outline: none;
    box-shadow: 0 0 8px #007bff66;
}

/* Button style */
button {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    user-select: none;
}

/* Button hover */
button:hover {
    background-color: #218838;
    transform: scale(1.05);
}

/* Button focus */
button:focus {
    outline: 3px solid #218838;
    outline-offset: 2px;
}

/* Responsive tweaks */
@media (max-width: 700px) {
    table, table th, table td {
        font-size: 0.85rem;
    }

    input[type="text"], input[type="url"] {
        width: 100%;
    }
}

/* Animation for fade + slide in */
@keyframes fadeSlideIn {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<h2>📦 Update Tracking Details</h2>

<table>
    <tr>
        <th>Order ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Status</th>
        <th>Courier</th>
        <th>Tracking No</th>
        <th>Tracking URL</th>
        <th>Update</th>
    </tr>
    <?php while ($order = $orders->fetch_assoc()): ?>
        <tr>
            <form method="POST">
                <td><?= $order['orderid'] ?></td>
                <td><?= htmlspecialchars($order['name']) ?></td>
                <td><?= htmlspecialchars($order['email']) ?></td>
                <td><?= htmlspecialchars($order['status']) ?></td>
                <td>
                    <input type="text" name="courier_name" value="<?= htmlspecialchars($order['courier_name']) ?>">
                </td>
                <td>
                    <input type="text" name="tracking_number" value="<?= htmlspecialchars($order['tracking_number']) ?>">
                </td>
                <td>
                    <input type="url" name="tracking_url" value="<?= htmlspecialchars($order['tracking_url']) ?>">
                </td>
                <td>
                    <input type="hidden" name="orderid" value="<?= $order['orderid'] ?>">
                    <button type="submit">Save</button>
                </td>
            </form>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
