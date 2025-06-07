<?php
include 'db.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<h2 style='text-align:center;'>Invalid product ID.</h2>";
    exit;
}

$proid = (int)$_GET['id'];

// Fetch product
$query = $conn->query("SELECT products.*, categories.categoryname 
                       FROM products 
                       LEFT JOIN categories ON products.catid = categories.catid 
                       WHERE proid = $proid");
if ($query->num_rows == 0) {
    echo "<h2 style='text-align:center;'>Product not found.</h2>";
    exit;
}
$product = $query->fetch_assoc();

// Fetch related products
$catid = $product['catid'];
$relatedQuery = $conn->query("SELECT * FROM products WHERE catid = $catid AND proid != $proid LIMIT 4");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['productname']) ?> | Product Details</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding-top: 80px;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        }

        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .product-container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px 40px;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
            animation: fadeSlideUp 0.8s ease forwards;
            text-align: center;
        }

        .product-container img {
            max-width: 100%;
            border-radius: 15px;
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .product-container img:hover {
            transform: scale(1.05);
        }

        .details {
            margin-top: 25px;
        }

        .details h2 {
            color: #1e3a8a;
        }

        .details p {
            font-size: 1.1rem;
            margin: 8px 0;
        }

        form, .btn-action {
            margin-top: 30px;
            display: inline-block;
        }

        button, .btn-action {
            padding: 12px 28px;
            margin: 0 10px;
            border: none;
            background-color: #1e88e5;
            color: white;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        button:hover, .btn-action:hover {
            background-color: #1565c0;
            transform: translateY(-3px);
        }

        .related-section {
            max-width: 1100px;
            margin: 50px auto;
        }

        .related-section h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #0d47a1;
        }

        .related-products {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .related-card {
            background: #fff;
            width: 220px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .related-card:hover {
            transform: scale(1.03);
        }

        .related-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .related-card h4 {
            margin: 10px;
        }

        .related-card a {
            display: block;
            margin: 15px;
            padding: 8px 12px;
            background-color: #1e88e5;
            color: white;
            border-radius: 25px;
            text-decoration: none;
        }

        @keyframes fadeSlideUp {
            0% { opacity: 0; transform: translateY(40px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .related-products {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
<header>
    <?php include('navbar.php'); ?>
</header>

<div class="product-container">
    <img src="img/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['productname']) ?>">
    <div class="details">
        <h2><?= htmlspecialchars($product['productname']) ?></h2>
        <p><strong>Price:</strong> $<?= htmlspecialchars($product['price']) ?></p>
        <p><strong>Size:</strong> <?= htmlspecialchars($product['size']) ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($product['categoryname']) ?></p>
    </div>

    <!-- Add to Cart -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="POST" action="cart.php">
            <input type="hidden" name="add_to_cart" value="1">
            <input type="hidden" name="proid" value="<?= htmlspecialchars($proid) ?>">
            <input type="hidden" name="productname" value="<?= htmlspecialchars($product['productname']) ?>">
            <input type="hidden" name="price" value="<?= htmlspecialchars($product['price']) ?>">
            <input type="hidden" name="image" value="<?= htmlspecialchars($product['image']) ?>">
            <input type="hidden" name="size" value="<?= htmlspecialchars($product['size']) ?>">
            <input type="hidden" name="category" value="<?= htmlspecialchars($product['categoryname']) ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit">🛒 Add to Cart</button>
        </form>
    <?php else: ?>
        <a class="btn-action" href="login.php?redirect=product.php&id=<?= $proid ?>">🛒 Add to Cart</a>
    <?php endif; ?>

    <!-- Buy Now -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="POST" action="order.php">
            <input type="hidden" name="proid" value="<?= htmlspecialchars($proid) ?>">
            <input type="hidden" name="productname" value="<?= htmlspecialchars($product['productname']) ?>">
            <input type="hidden" name="price" value="<?= htmlspecialchars($product['price']) ?>">
            <input type="hidden" name="image" value="<?= htmlspecialchars($product['image']) ?>">
            <input type="hidden" name="size" value="<?= htmlspecialchars($product['size']) ?>">
            <input type="hidden" name="category" value="<?= htmlspecialchars($product['categoryname']) ?>">
            <button type="submit" name="buy_now">💳 Buy Now</button>
        </form>
    <?php else: ?>
        <a class="btn-action" href="login.php?redirect=product.php&id=<?= $proid ?>">💳 Buy Now</a>
    <?php endif; ?>
</div>

<!-- Related Products Section -->
<div class="related-section">
    <h3>Related Products</h3>
    <div class="related-products">
        <?php while ($related = $relatedQuery->fetch_assoc()): ?>
            <div class="related-card">
                <img src="img/<?= htmlspecialchars($related['image']) ?>" alt="<?= htmlspecialchars($related['productname']) ?>">
                <h4><?= htmlspecialchars($related['productname']) ?></h4>
                <p>$<?= htmlspecialchars($related['price']) ?></p>
                <a href="product.php?id=<?= $related['proid'] ?>">View</a>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
