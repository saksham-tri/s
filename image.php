<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$userid = $_SESSION['userid'];
$stmt = $conn->prepare("SELECT name, email, username, image FROM users WHERE id = ?");
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | Fashion Gallery</title>
    <link rel="stylesheet" href="style1.css">
    <style>
        body {
            background: linear-gradient(to right top, #3b8d99, #6b6b83, #aa4b6b);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            color: #fff;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .profile-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            margin-bottom: 20px;
        }

        .profile-card h2 {
            margin: 10px 0 5px;
            font-size: 1.8rem;
        }

        .profile-card p {
            margin: 5px 0;
            font-size: 1rem;
        }

        .profile-card a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #ffffff;
            color: #3b8d99;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .profile-card a:hover {
            background: #3b8d99;
            color: #fff;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }
    </style>
</head>
<body>

<header>
    <?php include('navbar.php'); ?>
</header>

<div class="profile-card">
    <?php if (!empty($user['image'])): ?>
        <img src="img/<?= htmlspecialchars($user['image']) ?>" alt="Profile Picture">
    <?php else: ?>
        <img src="img/default.png" alt="Default Profile Picture">
    <?php endif; ?>

    <h2><?= htmlspecialchars($user['name']) ?></h2>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
    <p>Username: <?= htmlspecialchars($user['username']) ?></p>

    <a href="edit-profile.php">Edit Profile</a>
</div>

</body>
</html>
