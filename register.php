<?php
require 'db.php';

$success = '';
$error = '';

if (isset($_POST['submit'])) {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = "public";

    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $imageName = uniqid() . "." . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "img/" . $imageName);
        } else {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    if (empty($error)) {
        if (!empty($name) && !empty($email) && !empty($username) && !empty($password)) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
            $stmt->bind_param("ss", $email, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email or Username already exists.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, username, password, role, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $name, $email, $username, $hashedPassword, $role, $imageName);
                if ($stmt->execute()) {
                    $success = "Registration successful. <a href='login.php'>Click here to login</a>.";
                } else {
                    $error = "Database error: " . $stmt->error;
                }
            }
        } else {
            $error = "Please fill in all fields.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Fashion Gallery</title>
    <link rel="stylesheet" href="style1.css">
    <style>
        body {
        margin: 0;
        padding-top: 80px;
        background: linear-gradient(to right, #90caf9, #e3f2fd);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }


        .container {
            background: rgba(255, 255, 255, 0.15);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.25);
            max-width: 450px;
            width: 90%;
            color: #fff;
            animation: popIn 0.5s ease;
        }

        h2 {
        margin-bottom: 25px;
        font-size: 1.8rem;
        color: #0d47a1;
        text-align: center;
    }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 18px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        input[type="file"] {
            background: #f3f3f3;
        }

        input:focus {
            outline: 2px solid #3b8d99;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #ffffff;
            color: #3b8d99;
            border: none;
            border-radius: 15px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #3b8d99;
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

        .msg.success {
            color: #2ecc71;
        }

        .msg.error {
            color: #e74c3c;
        }

        header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }

        @keyframes popIn {
            from {opacity: 0; transform: scale(0.9);}
            to {opacity: 1; transform: scale(1);}
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
    <h2>Register</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit" name="submit">Create Account</button>
    </form>

    <?php if ($error): ?>
        <div class="msg error"><?= $error ?></div>
    <?php elseif ($success): ?>
        <div class="msg success"><?= $success ?></div>
    <?php endif; ?>
</div>

</body>
</html>
