<?php
include 'db.php';
session_start();



// Get user info if logged in
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';

// Get product details from POST
$product = [
    'proid' => $_POST['proid'] ?? '',
    'productname' => $_POST['productname'] ?? '',
    'price' => $_POST['price'] ?? '',
    'size' => $_POST['size'] ?? '',
    'image' => $_POST['image'] ?? '',
    'catid' => $_POST['catid'] ?? ''
];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Place Order - <?= htmlspecialchars($product['productname']) ?></title>
    <style>
        /* General Styles */
     /* Reset & base */
* {
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f2f2f2, #e6e6e6);
    margin: 0;
    padding: 0;
    color: #333;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Navbar animation */
.navbar {
    width: 100%;
    padding: 15px 30px;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    animation: slideDown 0.6s ease-out forwards;
}

@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.logo {
    font-size: 28px;
    font-weight: 700;
    color: #3b8d99;
    user-select: none;
}

/* Main section styling */
section {
    background: #fff;
    max-width: 600px;
    width: 90%;
    margin: 100px auto 50px auto; /* leave space for navbar */
    padding: 30px 35px;
    border-radius: 20px;
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    animation: fadeInUp 0.8s ease forwards;
    opacity: 0;
}

/* Fade in up */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Product info */
.product-info {
    text-align: center;
    margin-bottom: 30px;
    animation: scaleIn 0.6s ease forwards;
    opacity: 0;
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.product-info img {
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    max-width: 150px;
    height: auto;
    margin-top: 10px;
}

.product-info img:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

/* Form styling */
form {
    background: #fafafa;
    padding: 25px 30px;
    border-radius: 15px;
    box-shadow: inset 0 0 15px rgba(0,0,0,0.05);
    text-align: left;
    font-size: 16px;
    animation: fadeIn 1s ease forwards;
    opacity: 0;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #444;
    user-select: none;
}

form input[type="text"],
form input[type="email"],
form textarea {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border: 1.8px solid #ccc;
    border-radius: 12px;
    font-size: 15px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    font-family: inherit;
    resize: vertical;
}

form input[type="text"]:focus,
form input[type="email"]:focus,
form textarea:focus {
    border-color: #3b8d99;
    box-shadow: 0 0 8px rgba(59, 141, 153, 0.5);
    outline: none;
}

/* Submit Button */
button[type="submit"] {
    background: linear-gradient(135deg, #3b8d99, #30a0b0);
    color: white;
    padding: 14px 30px;
    border: none;
    border-radius: 30px;
    font-size: 17px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 6px 12px rgba(48, 160, 176, 0.6);
    transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
    user-select: none;
}

button[type="submit"]:hover {
    background: linear-gradient(135deg, #2a6870, #1f5256);
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(31, 82, 86, 0.8);
}

button[type="submit"]:active {
    transform: translateY(0);
    box-shadow: 0 6px 12px rgba(31, 82, 86, 0.7);
}

/* Responsive tweaks */
@media (max-width: 640px) {
    section {
        padding: 25px 20px;
        margin: 100px 15px 50px 15px;
    }
    form {
        padding: 20px 15px;
    }
    .logo {
        font-size: 22px;
    }
}

    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<section>
    <h2 style="text-align:center; margin-bottom: 25px;">Order Product</h2>

    <div class="product-info" style="margin-bottom:20px;">
        <h3><?= htmlspecialchars($product['productname']) ?></h3>
        <img src="img/<?= htmlspecialchars($product['image']) ?>" alt="Product Image" width="150"><br>
        <p><strong>Price:</strong> $<?= htmlspecialchars($product['price']) ?></p>
        <p><strong>Size:</strong> <?= htmlspecialchars($product['size']) ?></p>
        <p><strong>Category ID:</strong> <?= htmlspecialchars($product['catid']) ?></p>
    </div>

    <form method="POST" action="placeorder.php">
        <!-- Hidden product details -->
        <input type="hidden" name="productid" value="<?= htmlspecialchars($product['proid']) ?>">
        <input type="hidden" name="productname" value="<?= htmlspecialchars($product['productname']) ?>">
        <input type="hidden" name="price" value="<?= htmlspecialchars($product['price']) ?>">
        <input type="hidden" name="size" value="<?= htmlspecialchars($product['size']) ?>">
        <input type="hidden" name="image" value="<?= htmlspecialchars($product['image']) ?>">
        <input type="hidden" name="catid" value="<?= htmlspecialchars($product['catid']) ?>">

        <!-- User Info -->
        <label for="name">Your Name:</label><br>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($username) ?>"><br>

        <label for="email">Your Email:</label><br>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email) ?>"><br>

        <label for="address">Delivery Address:</label><br>
        <textarea id="address" name="address" required rows="4"></textarea><br>

        <button type="submit" name="submit_order">Submit Order</button>
    </form>
</section>

</body>
</html>
