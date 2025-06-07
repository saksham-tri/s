<?php
// placeorder.php (after successful order processing)

session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $productid = intval($_POST['productid'] ?? 0);
    $productname = trim($_POST['productname'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $catid = intval($_POST['catid'] ?? 0);

    if (!$productid || !$name || !$email || !$address) {
        // Basic validation fail, redirect or show error
        header("Location: index.php");
        exit();
    }

    // Save order to database
    $stmt = $conn->prepare("INSERT INTO orders (proid, productname, price, size, image, catid, user_email, customer_name, delivery_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters: i = int, s = string, d = double (float)
    $stmt->bind_param("isdssisss", $proid, $productname, $price, $size, $image, $catid, $email, $name, $address);

    if ($stmt->execute()) {
        // Send email
        $subject = "Order Confirmation - Fashion Gallery";
        $message = "
        <html>
        <head><title>Your Order Confirmation</title></head>
        <body>
            <h2>Hello " . htmlspecialchars($name) . ",</h2>
            <p>Thank you for your order!</p>
            <p><strong>Product:</strong> " . htmlspecialchars($productname) . "</p>
            <p><strong>Price:</strong> $" . htmlspecialchars(number_format($price, 2)) . "</p>
            <p><strong>Size:</strong> " . htmlspecialchars($size) . "</p>
            <p><strong>Shipping Address:</strong> " . nl2br(htmlspecialchars($address)) . "</p>
            <p>We’ll contact you soon with shipping details.</p>
        </body>
        </html>
        ";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: no-reply@yourdomain.com\r\n";

        mail($email, $subject, $message, $headers);

    } else {
        die("Error placing order: " . $stmt->error);
    }

    $stmt->close();

} else {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Order Confirmation</title>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
    height: 100vh;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #102a43;
  }
  .container {
    background: white;
    padding: 40px 50px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(16, 42, 67, 0.15);
    max-width: 450px;
    width: 90%;
    text-align: center;
    animation: slideUpFade 0.8s ease forwards;
  }
  h1 {
    color: #3b8d99;
    margin-bottom: 15px;
    font-size: 2.2rem;
  }
  p {
    font-size: 1.1rem;
    line-height: 1.5;
    margin-bottom: 15px;
  }
  .order-details {
    text-align: left;
    margin-top: 25px;
    border-top: 1px solid #ccc;
    padding-top: 20px;
  }
  .order-details p {
    margin: 6px 0;
  }
  .btn-home {
    margin-top: 30px;
    display: inline-block;
    background-color: #3b8d99;
    color: white;
    padding: 12px 30px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.3s ease;
  }
  .btn-home:hover {
    background-color: #2e6671;
  }

  @keyframes slideUpFade {
    0% {
      opacity: 0;
      transform: translateY(30px);
    }
    100% {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>
</head>
<body>

<div class="container">
  <h1>Thank You, <?= htmlspecialchars($name) ?>!</h1>
  <p>Your order has been placed successfully.</p>

  <div class="order-details">
    <p><strong>Product:</strong> <?= htmlspecialchars($productname) ?></p>
    <p><strong>Price:</strong> $<?= htmlspecialchars(number_format($price, 2)) ?></p>
    <p><strong>Size:</strong> <?= htmlspecialchars($size) ?></p>
    <p><strong>Shipping Address:</strong> <?= nl2br(htmlspecialchars($address)) ?></p>
  </div>

  <a href="index.php" class="btn-home">Back to Home</a>
</div>

</body>
</html>
