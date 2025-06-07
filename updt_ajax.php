<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proid'], $_POST['quantity'])) {
    $proid = $_POST['proid'];
    $quantity = max(1, intval($_POST['quantity']));

    if (isset($_SESSION['cart'][$proid])) {
        $_SESSION['cart'][$proid]['quantity'] = $quantity;
        $price = $_SESSION['cart'][$proid]['price'];
        $subtotal = $price * $quantity;

        // Calculate total
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        echo json_encode([
            'success' => true,
            'subtotal' => number_format($subtotal, 2),
            'total' => number_format($total, 2)
        ]);
        exit;
    }
}

echo json_encode(['success' => false]);
