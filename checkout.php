<?php
session_start();

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize form inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    // Basic validation
    if (empty($name)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($phone)) $errors[] = "Phone is required.";

    if (empty($errors)) {
        // Normally, you'd save order info to DB here.

        // Clear the cart
        $_SESSION['cart'] = [];

        // Show success message
        $success = "Thank you, your order has been placed!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <link rel="stylesheet" href="style1.css">
    <style>
        /* Container styling */
.checkout-container {
    max-width: 700px;
    margin: 40px auto;
    padding: 30px 35px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
    animation: fadeInUp 0.7s ease forwards;
}

/* Heading */
.checkout-container h2 {
    text-align: center;
    font-weight: 700;
    font-size: 2rem;
    color: #222;
    margin-bottom: 30px;
    letter-spacing: 1px;
}

/* Form styles */
.checkout-container form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.checkout-container input,
.checkout-container textarea {
    padding: 12px 15px;
    font-size: 1.05em;
    border: 2px solid #ccc;
    border-radius: 10px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    resize: vertical;
}

.checkout-container input:focus,
.checkout-container textarea:focus {
    border-color: #28a745;
    box-shadow: 0 0 8px rgba(40, 167, 69, 0.5);
    outline: none;
}

/* Submit button */
.btn-submit {
    background-color: #28a745;
    border: none;
    color: white;
    padding: 14px;
    font-size: 1.2em;
    cursor: pointer;
    border-radius: 10px;
    font-weight: 700;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
}

.btn-submit:hover {
    background-color: #218838;
    box-shadow: 0 8px 22px rgba(33, 136, 56, 0.7);
}

/* Error and success messages */
.checkout-container div[style*="color: red"] {
    background: #ffe6e6;
    border: 1px solid #e3342f;
    color: #e3342f;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    animation: shake 0.4s ease;
}

.checkout-container div[style*="color: green"] {
    background: #d4edda;
    border: 1px solid #28a745;
    color: #155724;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
    animation: fadeIn 0.6s ease forwards;
}

.checkout-container div[style*="color: green"] h3 {
    margin-bottom: 10px;
}

/* Order summary */
.cart-summary {
    margin-top: 30px;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
}

.cart-summary h3 {
    font-weight: 700;
    margin-bottom: 15px;
    color: #007BFF;
}

.cart-summary ul {
    list-style: none;
    padding-left: 0;
    margin-bottom: 15px;
}

.cart-summary ul li {
    padding: 7px 0;
    border-bottom: 1px solid #ddd;
    font-size: 1.05em;
    color: #555;
}

.cart-summary ul li:last-child {
    border-bottom: none;
}

.cart-summary p {
    font-weight: 700;
    font-size: 1.25em;
    color: #222;
}

/* Link button */
.checkout-container a {
    display: inline-block;
    margin-top: 15px;
    padding: 12px 30px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 700;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.checkout-container a:hover {
    background-color: #0056b3;
    box-shadow: 0 6px 15px rgba(0, 86, 179, 0.6);
}

/* Animations */

@keyframes fadeInUp {
    0% {
        opacity: 0;
        transform: translateY(30px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
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

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-8px); }
    40%, 80% { transform: translateX(8px); }
}

    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="checkout-container">
    <h2>Checkout</h2>

    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <?= implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div style="color: green;">
        <h3><?= $success ?></h3>
        <p>We’ll contact you soon for delivery updates.</p>
        <a href="index.php" style="display:inline-block; margin-top:15px; padding:10px 20px; background-color:#007bff; color:white; text-decoration:none; border-radius:5px;">⬅ Back to Shop</a>
    </div>
<?php else: ?>

<form method="post">
    <input type="text" name="name" placeholder="Full Name" value="<?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '' ?>" required>
    <input type="email" name="email" placeholder="Email Address" value="<?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '' ?>" required>
    <textarea name="address" placeholder="Shipping Address" required></textarea>
    <input type="text" name="phone" placeholder="Phone Number" required>
    <button type="submit" class="btn-submit">Place Order</button>
</form>


        <div class="cart-summary">
            <h3>Your Order Summary:</h3>
            <ul>
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $item):
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                    <li><?= htmlspecialchars($item['productname']) ?> (x<?= $item['quantity'] ?>) - $<?= number_format($subtotal, 2) ?></li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Total: $<?= number_format($total, 2) ?></strong></p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
