<?php
require 'db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['image'] = $user['image'];

                $redirect = $_GET['redirect'] ?? null;
                $productId = $_GET['id'] ?? null;

                if (!empty($redirect)) {
                    $url = $redirect;
                    if (!empty($productId)) {
                        $url .= '?id=' . $productId;
                    }
                    header("Location: $url");
                    exit;
                }

                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } elseif ($user['role'] === 'user') {
                    header("Location: user.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
        }
    } else {
        $error = "Please enter username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | Fashion Gallery</title>
  <style>
    body {
        margin: 0;
        padding-top: 80px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background: linear-gradient(-45deg, #1e88e5, #6dd5fa, #e3f2fd, #90caf9);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
        overflow: hidden;
    }

    @keyframes gradientBG {
        0% {background-position: 0% 50%;}
        50% {background-position: 100% 50%;}
        100% {background-position: 0% 50%;}
    }

    .container {
        background: rgba(255, 255, 255, 0.1);
        padding: 40px;
        border-radius: 20px;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
        max-width: 450px;
        width: 90%;
        color: #fff;
        animation: fadeIn 0.7s ease;
    }

    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(30px);}
        to {opacity: 1; transform: translateY(0);}
    }

    h2 {
        margin-bottom: 25px;
        font-size: 1.8rem;
        color: #0d47a1;
        text-align: center;
    }
    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 12px 14px;
        margin-bottom: 18px;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        box-sizing: border-box;
    }

    input:focus {
        outline: 2px solid #3b8d99;
    }

    button {
        width: 100%;
        padding: 14px;
        background: #fff;
        color: #1e88e5;
        border: none;
        border-radius: 15px;
        font-weight: bold;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    button:hover {
        background: #1e88e5;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    .msg {
        margin-top: 20px;
        text-align: center;
        font-size: 1rem;
        font-weight: bold;
    }

    .msg.error {
        color: #ffbaba;
        background: #ff4d4d;
        padding: 10px;
        border-radius: 8px;
    }

    .links {
        margin-top: 20px;
        text-align: center;
    }

    .links a {
        display: block;
        margin: 6px 0;
        color: #fff;
        text-decoration: underline;
    }

    header {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
    }

    @media (max-width: 480px) {
        .container {
            padding: 30px 20px;
        }

        h2 {
            font-size: 1.5rem;
        }
    }
  </style>
</head>
<body>
<header>
    <?php include('navbar.php'); ?>
</header>

<div class="container">
    <h2>Login</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username or Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <?php if ($error): ?>
        <div class="msg error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="links">
        <a href="forgotpass.php">Forgot Password?</a>
        <a href="register.php">Don't have an account? Register</a>
    </div>
</div>
</body>
</html>
