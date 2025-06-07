
<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $item = [
        'proid' => $_POST['proid'],
        'productname' => $_POST['productname'],
        'price' => $_POST['price'],
        'image' => $_POST['image'],
        'size' => $_POST['size'],
        'quantity' => 1
    ];

    $_SESSION['cart'][] = $item;
    header('Location: cart.php');
    exit;
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Your Shopping Cart</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 20px;
        }

        .cart-container {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 25px 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border-radius: 12px;
            animation: fadeInUp 0.7s ease forwards;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            color: #222;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            border-radius: 8px;
            animation: slideInLeft 0.5s ease forwards;
        }

        .cart-item img {
            width: 90px;
            height: 90px;
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .cart-item > div {
            flex-grow: 1;
        }

        .cart-item strong {
            font-size: 1.1rem;
            color: #007BFF;
        }

        .qty-input {
            width: 60px;
            padding: 6px;
            text-align: center;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .btn-remove {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .btn-remove:hover {
            background-color: #b02a37;
        }

        .cart-summary {
            text-align: right;
            margin-top: 30px;
            font-size: 1.3rem;
            font-weight: 700;
            color: #111;
        }

        .btn-checkout {
            background-color: #007BFF;
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }

        .btn-checkout:hover {
            background-color: #0056b3;
        }

        .empty-msg {
            text-align: center;
            color: #666;
            font-size: 1.2rem;
            margin-top: 50px;
        }

        @keyframes fadeInUp {
            0% {opacity: 0; transform: translateY(20px);}
            100% {opacity: 1; transform: translateY(0);}
        }

        @keyframes slideInLeft {
            0% {opacity: 0; transform: translateX(-30px);}
            100% {opacity: 1; transform: translateX(0);}
        }
    </style>
</head>
<body>
    <?php include('navbar.php');?>

<div class="cart-container">
    <h2>Your Cart</h2>

    <?php if (!empty($_SESSION['cart'])): ?>
        <?php $total = 0; ?>
        <?php foreach ($_SESSION['cart'] as $proid => $item): ?>
            <?php $subtotal = $item['price'] * $item['quantity']; $total += $subtotal; ?>
            <div class="cart-item" data-proid="<?= $proid ?>">
                <img src="img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['productname']) ?>">
                <div>
                    <strong><?= htmlspecialchars($item['productname']) ?></strong><br>
                    Price: $<?= htmlspecialchars($item['price']) ?><br>
                    Quantity:
                    <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1">
                    <br>
                    Subtotal: $<span class="item-subtotal"><?= number_format($subtotal, 2) ?></span>
                </div>
                <form method="post" style="margin-left:auto;">
                    <input type="hidden" name="proid" value="<?= $proid ?>">
                    <button type="submit" name="remove" class="btn-remove">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>
        <div class="cart-summary">
            Total: $<span id="cart-total"><?= number_format($total, 2) ?></span><br>
            <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
        </div>
    <?php else: ?>
        <p class="empty-msg">Your cart is empty.</p>
    <?php endif; ?>
</div>

<!-- JavaScript for AJAX Quantity Update -->
<script>
document.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('change', function () {
        const cartItem = this.closest('.cart-item');
        const proid = cartItem.dataset.proid;
        const quantity = this.value;

        fetch('updt_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `proid=${proid}&quantity=${quantity}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                cartItem.querySelector('.item-subtotal').textContent = data.subtotal;
                document.getElementById('cart-total').textContent = data.total;
            }
        });
    });
});
</script>

</body>
</html>
