<?php
require 'db.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            // You can add password reset logic here (e.g. send email with token)
            $success = "Password reset link has been sent to your email.";
        } else {
            $error = "Email not found in our records.";
        }
    } else {
        $error = "Please enter your email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #3b8d99, #6b6b83, #aa4b6b);
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 1s ease-in-out forwards;
        }

        .container {
            background: white;
            padding: 35px 45px;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: slideUp 0.6s ease-out forwards;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 14px;
            margin: 12px 0;
            border: 2px solid #ccc;
            border-radius: 12px;
            font-size: 1rem;
        }

        input:focus {
            border-color: #3b8d99;
            box-shadow: 0 0 10px rgba(59, 141, 153, 0.5);
            outline: none;
        }

        button {
            padding: 14px 0;
            width: 100%;
            background: linear-gradient(45deg, #3b8d99, #2f6a7e);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background: linear-gradient(45deg, #2f6a7e, #24606f);
        }

        .msg {
            margin-top: 20px;
            font-weight: 600;
        }

        .msg.success {
            color: #27ae60;
        }

        .msg.error {
            color: #e74c3c;
        }

        .navbar {
            width: 100%;
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 10;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #3b8d99;
        }

        .nav-links a {
            margin-left: 20px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .nav-links a:hover {
            color: #3b8d99;
        }

        @keyframes slideUp {
            0% { transform: translateY(40px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">MySite</div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="register.php">Register</a>
            <a href="login.php">Login</a>
        </div>
    </nav>

    <div class="container">
        <h2>Forgot Password</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
        </form>

        <?php if ($error): ?>
            <div class="msg error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="msg success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
