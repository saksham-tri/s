<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

$userid = $_SESSION['userid'];
$stmt = $conn->prepare("SELECT name, email, address, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userid);
$stmt->execute();
$stmt->bind_result($name, $email, $address, $profile_image);
$stmt->fetch();
$stmt->close();

$profileImage = (!empty($profile_image) && file_exists("profile_images/$profile_image")) ? $profile_image : 'default-user.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
    <style>
       /* profile.css */
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(135deg, #e0f7fa, #80deea);
  margin: 0;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

.profile-container {
  background: #ffffffdd;
  max-width: 400px;
  width: 100%;
  padding: 30px 25px;
  border-radius: 15px;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
  text-align: center;
  animation: fadeScaleIn 0.8s ease forwards;
}

.profile-container img {
  border-radius: 50%;
  width: 120px;
  height: 120px;
  object-fit: cover;
  margin-bottom: 20px;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
  transition: transform 0.3s ease;
}

.profile-container img:hover {
  transform: scale(1.05);
}

.profile-container h2 {
  margin: 0 0 10px;
  font-weight: 700;
  color: #00796b;
  font-size: 1.8rem;
}

.profile-container p {
  color: #004d40;
  font-size: 1rem;
  margin: 8px 0;
  line-height: 1.5;
  white-space: pre-wrap;
}

a.button {
  display: inline-block;
  margin-top: 25px;
  padding: 12px 30px;
  background-color: #00796b;
  color: white;
  font-weight: 600;
  text-decoration: none;
  border-radius: 30px;
  box-shadow: 0 8px 20px rgba(0, 121, 107, 0.4);
  transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
  user-select: none;
}

a.button:hover {
  background-color: #004d40;
  box-shadow: 0 12px 25px rgba(0, 77, 64, 0.6);
  transform: translateY(-3px);
}

@keyframes fadeScaleIn {
  0% {
    opacity: 0;
    transform: scale(0.85);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

/* Responsive for smaller devices */
@media (max-width: 480px) {
  .profile-container {
    padding: 25px 15px;
  }

  .profile-container img {
    width: 100px;
    height: 100px;
    margin-bottom: 15px;
  }

  .profile-container h2 {
    font-size: 1.5rem;
  }

  a.button {
    width: 100%;
    padding: 12px;
  }
}

    </style>
</head>
<body>

<div class="profile-container">
    <img src="profile_images/<?= htmlspecialchars($profileImage) ?>" alt="Profile Image">
    <h2><?= htmlspecialchars($name) ?></h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
    <p><strong>Address:</strong><br><?= nl2br(htmlspecialchars($address)) ?></p>
    <a href="edit-profile.php" class="button">Edit Profile</a>
</div>

</body>
</html>
