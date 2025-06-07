<?php
session_start();
include 'db.php';

$error = '';
$new_orders = [];
$old_orders = [];

// Generate CAPTCHA initially
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['captcha_sum'])) {
    $a = rand(1, 9);
    $b = rand(1, 9);
    $_SESSION['captcha_sum'] = $a + $b;
} else {
    $a = $b = 0;
}

$prefill_email = isset($_GET['email']) ? filter_var($_GET['email'], FILTER_SANITIZE_EMAIL) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $captcha_answer = intval($_POST['captcha_answer'] ?? -1);

    if (!$email) {
        $error = "Please enter a valid Email.";
    } elseif ($captcha_answer !== $_SESSION['captcha_sum']) {
        $error = "Incorrect CAPTCHA answer. Please try again.";
        $a = rand(1, 9);
        $b = rand(1, 9);
        $_SESSION['captcha_sum'] = $a + $b;
    } else {
        unset($_SESSION['captcha_sum']);

        $stmt = $conn->prepare("SELECT * FROM orders WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if (strtolower($row['status']) === 'delivered') {
                    $old_orders[] = $row;
                } else {
                    $new_orders[] = $row;
                }
            }
        } else {
            $error = "No orders found for this email.";
            $a = rand(1, 9);
            $b = rand(1, 9);
            $_SESSION['captcha_sum'] = $a + $b;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Track Your Order</title>
    <style>
       body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #121212;
    color: #f0f0f0;
    margin: 0;
    padding: 20px;
}

h1, h2 {
    text-align: center;
    color: #00bfff;
    user-select: none;
}

form, .tabs {
    max-width: 500px;
    margin: 0 auto;
    background: #1e1e1e;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px #00bfff44;
}

input[type="email"], input[type="number"] {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    background: #2c2c2c;
    color: white;
    border: 1px solid #333;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

input[type="email"]:focus,
input[type="number"]:focus {
    border-color: #00bfff;
    outline: none;
    box-shadow: 0 0 8px #00bfff88;
}

label {
    display: block;
    margin: 10px 0 5px;
    font-weight: 600;
    user-select: none;
}

button {
    background: #00bfff;
    color: black;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    width: 100%;
    transition: background 0.3s ease, transform 0.2s ease;
    font-size: 1.1rem;
    user-select: none;
}

button:hover {
    background: #0099cc;
    transform: scale(1.05);
}

button:focus {
    outline: 3px solid #0099cc;
    outline-offset: 2px;
}

.order-details {
    background: #1e1e1e;
    margin: 15px auto;
    padding: 15px;
    border-left: 5px solid #00bfff;
    border-radius: 8px;
    max-width: 700px;
    animation: fadeSlideIn 0.5s ease forwards;
    opacity: 0;
    transform: translateY(10px);
    /* animation-fill-mode: forwards set by JS */
}

/* Animation will trigger when JS adds a class */
.order-details.visible {
    opacity: 1;
    transform: translateY(0);
    animation-name: fadeSlideIn;
}

.tabs {
    text-align: center;
    margin-top: 20px;
    user-select: none;
}

.tab-btn {
    padding: 10px 20px;
    margin: 0 5px;
    cursor: pointer;
    border: none;
    background: #333;
    color: white;
    font-weight: bold;
    border-radius: 6px;
    transition: background 0.3s ease, transform 0.2s ease;
    font-size: 1rem;
}

.tab-btn:hover:not(.active) {
    background: #555;
    transform: scale(1.05);
}

.tab-btn:focus {
    outline: 3px solid #00bfff;
    outline-offset: 2px;
}

.tab-btn.active {
    background: #00bfff;
    color: black;
    animation: pulse 2s infinite;
}

.tab-content {
    display: none;
    animation: fadeIn 0.4s ease forwards;
    user-select: text;
}

.tab-content.active {
    display: block;
}

.error {
    color: #ff4d4d;
    text-align: center;
    font-weight: bold;
    margin-bottom: 20px;
    user-select: none;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 10px #00bfff99;
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 20px #00bfffcc;
        transform: scale(1.05);
    }
}

/* Links inside orders */
.order-details a {
    color: #00bfff;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.order-details a:hover,
.order-details a:focus {
    color: #0099cc;
    text-decoration: underline;
    outline: none;
}

    </style>
</head>
<body>

<h1>Track Your Order</h1>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if (empty($new_orders) && empty($old_orders)): ?>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Enter Your Email" required
            value="<?= htmlspecialchars($prefill_email ?: ($_POST['email'] ?? '')) ?>" />

        <label>Solve: <?= $a ?> + <?= $b ?> = ?</label>
        <input type="number" name="captcha_answer" placeholder="CAPTCHA Answer" required />

        <button type="submit">Track Order</button>
    </form>
<?php else: ?>
  

    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('new')">🆕 New Orders</button>
        <button class="tab-btn" onclick="showTab('old')">📦 Past Orders</button>
    </div>

  <h2>Your Orders</h2>
    <div id="new-orders" class="tab-content active">
        <?php foreach ($new_orders as $order): ?>
            <div class="order-details">
                <h3>Order ID: <?= htmlspecialchars($order['orderid']) ?></h3>
                <p><strong>Status:</strong> <?= ucfirst(htmlspecialchars($order['status'])) ?></p>
                <p><strong>Product:</strong> <?= htmlspecialchars($order['productname']) ?> (<?= htmlspecialchars($order['size']) ?>)</p>
                <p><strong>Quantity:</strong> <?= htmlspecialchars($order['quantity']) ?></p>
                <p><strong>Price:</strong> $<?= htmlspecialchars($order['price']) ?></p>
                <p><strong>Order Date:</strong> <?= htmlspecialchars($order['order_date']) ?></p>

                <?php if (!empty($order['tracking_url'])): ?>
                    <p><strong>Tracking:</strong> <a href="<?= htmlspecialchars($order['tracking_url']) ?>" target="_blank">Track</a></p>
                <?php else: ?>
                    <p>Tracking info not available.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="old-orders" class="tab-content">
        <?php foreach ($old_orders as $order): ?>
            <div class="order-details">
                <h3>Order ID: <?= htmlspecialchars($order['orderid']) ?></h3>
                <p><strong>Status:</strong> <?= ucfirst(htmlspecialchars($order['status'])) ?></p>
                <p><strong>Product:</strong> <?= htmlspecialchars($order['productname']) ?> (<?= htmlspecialchars($order['size']) ?>)</p>
                <p><strong>Quantity:</strong> <?= htmlspecialchars($order['quantity']) ?></p>
                <p><strong>Price:</strong> $<?= htmlspecialchars($order['price']) ?></p>
                <p><strong>Order Date:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tab + '-orders').classList.add('active');
    document.querySelector('.tab-btn[onclick="showTab(\'' + tab + '\')"]').classList.add('active');
}
</script>

</body>
</html>
