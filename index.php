<?php
session_start();
include('db.php');

$profileImage = 'default-user.png'; // fallback image

if (isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];
    $query = $conn->prepare("SELECT image FROM users WHERE id = ?");
    $query->bind_param("i", $userid);
    $query->execute();
    $query->bind_result($image);
    if ($query->fetch() && !empty($image) && file_exists("profile_images/$image")) {
        $profileImage = $image;
    }
    $query->close();

    // Handle Add to Cart
    if (isset($_POST['add_to_cart'])) {
        $proid = intval($_POST['proid']);
        $productname = $_POST['productname'];
        $price = $_POST['price'];
        $image = $_POST['image'];
        $quantity = intval($_POST['quantity']);

        if (isset($_SESSION['cart'][$proid])) {
            $_SESSION['cart'][$proid]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$proid] = [
                'productname' => $productname,
                'price' => $price,
                'image' => $image,
                'quantity' => $quantity
            ];
        }
        header("Location: cart.php");
        exit;
    }
}

// ----- Order Tracking Feature -----
$track_error = '';
$track_order = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['track_order'])) {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $track_error = "Please enter a valid email.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE email = ? ORDER BY order_date DESC LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $track_order = $result->fetch_assoc();
        } else {
            $track_error = "No recent order found for this email.";
        }
        $stmt->close();
    }
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Fashion Product Gallery</title>
    <link rel="stylesheet" href="style1.css" />
    <style>
       /* --- Reset & Base Styles --- */
* {
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f4f7fb;
  margin: 0;
  padding: 0 20px 40px;
  color: #333;
  animation: fadeInBody 1s ease forwards;
}

main {
  max-width: 1200px;
  margin: 0 auto;
}

/* --- Header & Hero Section --- */
header {
  position: relative;
  padding: 30px 0 50px;
  text-align: center;
}



.hero h1 {
  font-size: 3rem;
  color: #007BFF;
  margin-bottom: 10px;
  animation: fadeSlideInDown 0.8s ease forwards;
}

.hero p {
  font-size: 1.3rem;
  color: #555;
  margin-bottom: 20px;
  animation: fadeSlideInDown 1s ease forwards;
}

.btn-primary {
  display: inline-block;
  padding: 12px 30px;
  background-color: #007BFF;
  color: white;
  text-decoration: none;
  font-weight: 600;
  border-radius: 30px;
  transition: background-color 0.3s ease;
  animation: fadeSlideInDown 1.2s ease forwards;
}

.btn-primary:hover {
  background-color: #0056b3;
  cursor: pointer;
}

/* --- Profile Container & Dropdown --- */
.profile-container {
  position: absolute;
  top: 10px;
  left: 20px;
  z-index: 1000;
}

.profile-pic {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
  border: 2.5px solid #007BFF;
  cursor: pointer;
  transition: box-shadow 0.3s ease;
}

.profile-pic:hover {
  box-shadow: 0 0 10px #007BFFaa;
}

.dropdown-menu {
  position: absolute;
  top: 56px;
  left: 0;
  background-color: #fff;
  border: 1px solid #ccc;
  border-radius: 10px;
  min-width: 180px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  opacity: 0;
  transform: translateY(-10px);
  pointer-events: none;
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.dropdown-menu.show {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

.dropdown-menu a {
  display: block;
  padding: 12px 18px;
  color: #333;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.2s ease;
}

.dropdown-menu a:hover {
  background-color: #007BFF;
  color: white;
}

/* --- Order Tracking Section --- */
#order-tracking {
  max-width: 420px;
  margin: 40px auto 70px;
  padding: 30px 25px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0, 123, 255, 0.1);
  animation: fadeSlideIn 0.8s ease forwards;
}

#order-tracking h2 {
  text-align: center;
  margin-bottom: 25px;
  color: #007BFF;
  font-weight: 700;
}

#order-tracking form input[type="email"] {
  width: 100%;
  padding: 12px 15px;
  font-size: 1rem;
  border: 2px solid #007BFF;
  border-radius: 8px;
  outline: none;
  transition: border-color 0.3s ease;
}

#order-tracking form input[type="email"]:focus {
  border-color: #0056b3;
}

#order-tracking form button {
  width: 100%;
  margin-top: 15px;
  padding: 12px;
  background-color: #007BFF;
  color: white;
  border: none;
  font-size: 1.1rem;
  border-radius: 30px;
  cursor: pointer;
  font-weight: 600;
  transition: background-color 0.3s ease;
}

#order-tracking form button:hover {
  background-color: #0056b3;
}

#order-tracking .error {
  color: #d93025;
  text-align: center;
  margin-bottom: 15px;
  font-weight: 600;
}

#order-tracking .order-info {
  background: #e0f7fa;
  padding: 20px 18px;
  border-radius: 10px;
  color: #004d40;
  line-height: 1.5;
  font-weight: 600;
  box-shadow: inset 0 0 12px #81d4fa88;
}

#order-tracking .order-info h4 {
  margin: 15px 0 8px;
  color: #007BFF;
}

/* --- Product Gallery --- */
/* Gallery Section */
#gallery {
  max-width: 1200px;
  margin: 40px auto;
  padding: 0 20px 60px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
  background: #fafafa;
  border-radius: 12px;
  box-shadow: 0 10px 20px rgba(0,0,0,0.08);
}

#gallery h2 {
  font-size: 2.6rem;
  margin-bottom: 20px;
  color: #222;
  letter-spacing: 0.05em;
  user-select: none;
}

/* Category Titles */
#gallery h3 {
  font-size: 1.8rem;
  margin: 40px 0 20px 0;
  font-weight: 600;
  color: #444;
  border-left: 5px solid #007BFF;
  padding-left: 12px;
  text-transform: uppercase;
  letter-spacing: 0.07em;
}

/* Container for products in each category */
.category-container {
  display: flex;
  flex-wrap: wrap;
  gap: 22px;
  justify-content: flex-start;
}

/* Each product card */
.card {
  background: #fff;
  width: 230px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgb(0 0 0 / 0.07);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  cursor: pointer;
  position: relative;
}

.card:hover,
.card:focus-within {
  transform: translateY(-8px);
  box-shadow: 0 14px 30px rgb(0 0 0 / 0.15);
  z-index: 3;
}

/* Product image */
.card img {
  width: 100%;
  height: 230px;
  object-fit: cover;
  border-bottom: 1px solid #ddd;
  transition: transform 0.4s ease;
}

.card:hover img {
  transform: scale(1.05);
}

/* Product details */
.card h3 {
  font-size: 1.2rem;
  margin: 14px 15px 6px;
  color: #111;
  font-weight: 700;
  user-select: none;
}

.card p {
  font-size: 0.9rem;
  margin: 4px 15px;
  color: #555;
  user-select: none;
}

/* Button container */
.card > div {
  margin: 12px 15px 18px;
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

/* Order and Cart buttons */
.order-btn {
  background: transparent;
  border: 2px solid #007BFF;
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.9rem;
  color: #007BFF;
  cursor: pointer;
  transition: background-color 0.25s ease, color 0.25s ease;
  user-select: none;
  min-width: 100px;
  text-align: center;
}

.order-btn:hover,
.order-btn:focus {
  background-color: #007BFF;
  color: #fff;
  outline: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .category-container {
    justify-content: center;
  }
  .card {
    width: 80vw;
    max-width: 350px;
  }
}

@media (max-width: 480px) {
  #gallery {
    padding-bottom: 40px;
  }
  .card img {
    height: 180px;
  }
}

/* Slideshow Container */
.slideshow-container {
  position: relative;
  max-width: 1000px;
  height: 580px; /* Increased height */
  margin: 40px auto;
  overflow: hidden;
  border-radius: 16px;
  box-shadow: 0 12px 30px rgba(0,0,0,0.1);
  background-color: #fff;
}

/* Each Slide */
.slide {
  display: none;
  position: relative;
  width: 100%;
  height: 100%;
  animation: fadeInSlide 1s ease-in-out forwards;
}

@keyframes fadeInSlide {
  from {opacity: 0;}
  to {opacity: 1;}
}

/* Slide Image */
.slide img {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Ensures the image covers the area */
  border-radius: 16px;
  transition: transform 0.6s ease;
}

.slide:hover img {
  transform: scale(1.03);
}

/* Arrows */
.prev, .next {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  font-size: 34px;
  color: #fff;
  background-color: rgba(0,0,0,0.5);
  padding: 10px;
  border-radius: 50%;
  cursor: pointer;
  z-index: 5;
  transition: background 0.3s ease;
}

.prev:hover, .next:hover {
  background-color: rgba(0,0,0,0.8);
}

.prev {
  left: 15px;
}

.next {
  right: 15px;
}

/* Dots */
.dots {
  text-align: center;
  margin-top: 16px;
}

.dot {
  height: 14px;
  width: 14px;
  margin: 0 6px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.dot.active, .dot:hover {
  background-color: #007BFF;
}

/* Admin Overlay (Image info and Delete) */
.slide > div {
  position: absolute;
  bottom: 15px;
  left: 15px;
  background-color: rgba(0,0,0,0.65);
  color: #fff;
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 12px;
}

.slide > div a {
  color: #ff4d4d;
  text-decoration: none;
  font-weight: bold;
}

.slide > div a:hover {
  text-decoration: underline;
}

/* Upload Section */
.upload-section {
  max-width: 1000px;
  margin: 30px auto;
  background-color: #f1f1f1;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 8px 18px rgba(0,0,0,0.08);
  font-family: 'Segoe UI', sans-serif;
}

.upload-section label {
  font-weight: 600;
  margin-bottom: 8px;
  display: block;
  font-size: 1.05rem;
}

.upload-section input[type="file"] {
  margin: 10px 0 20px;
  font-size: 1rem;
}

.upload-section button {
  padding: 10px 20px;
  background-color: #007BFF;
  border: none;
  color: white;
  font-weight: bold;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease;
}

.upload-section button:hover {
  background-color: #0056b3;
}

/* Messages */
.message, .error {
  padding: 12px;
  margin-bottom: 20px;
  border-radius: 8px;
  font-weight: 600;
}

.message {
  background-color: #d4edda;
  color: #155724;
}

.error {
  background-color: #f8d7da;
  color: #721c24;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .slideshow-container {
    height: 400px;
  }

  .slide img {
    object-position: center;
  }
}

@media (max-width: 480px) {
  .slideshow-container {
    height: 280px;
  }

  .prev, .next {
    font-size: 26px;
    padding: 8px;
  }

  .slide > div {
    font-size: 0.8rem;
    padding: 6px 12px;
  }
}



/* --- Footer --- */
footer {
  text-align: center;
  padding: 15px 0;
  background-color: #007BFF;
  color: white;
  font-weight: 600;
  font-size: 0.95rem;
  border-radius: 0 0 15px 15px;
}

/* --- Animations --- */
@keyframes fadeInBody {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes fadeSlideIn {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeSlideInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

    .profile {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
    }

    .profile img {
        border: 2px solid #3b8d99;
    }


    </style>
</head>

<body>
    <?php include('navbar.php'); ?>
<main>
<header style="position: relative;">
    

    <!-- Profile Image with Dropdown -->
    <div class="profile-container">
        <img src="img/<?= htmlspecialchars($profileImage) ?>" 
             alt="Profile Image" 
             class="profile-pic" id="profilePic" />

        <div id="profileDropdown" class="dropdown-menu">
            <a href="profile.php">👤 View Profile</a>
            


            <?php
session_start();
?>

<?php if (isset($_SESSION['username'])): ?>
    <div class="profile">
        <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
        <?php if (!empty($_SESSION['image'])): ?>
            <img src="img/<?= htmlspecialchars($_SESSION['image']) ?>" alt="Profile Picture" style="width:60px; height:60px; border-radius:50%; object-fit:cover;">
        <?php endif; ?>
    </div>
<?php else: ?>
    <p><a href="login.php">Login</a> to see your profile picture.</p>
<?php endif; ?>

        </div>
    </div>

    <section class="hero">
        <h1>Explore Our Fashion Collection</h1>
        <p>Stylish, Affordable, and Unique</p>
        <a href="#gallery" class="btn-primary">View Gallery</a>
    </section>
</header>


<?php


$uploadDir = 'img/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$uploadMessage = '';

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$username = $_SESSION['username'] ?? 'guest';

// Handle image deletion by admin
if ($isAdmin && isset($_GET['delete'])) {
    $fileToDelete = basename($_GET['delete']);
    $path = $uploadDir . $fileToDelete;
    $metaFile = $uploadDir . 'data.json';

    if (file_exists($path)) {
        unlink($path);
        $uploadMessage = "Image deleted successfully.";

        if (file_exists($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true);
            unset($meta[$fileToDelete]);
            file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT));
        }
    }
}

// Handle image upload by admin
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = basename($_FILES['image']['name']);
    $fileType = mime_content_type($fileTmpPath);
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (in_array($fileType, $allowedTypes)) {
        $fileName = preg_replace("/[^a-zA-Z0-9\._-]/", "", $fileName);
        $destPath = $uploadDir . $fileName;
        if (file_exists($destPath)) {
            $fileName = time() . '_' . $fileName;
            $destPath = $uploadDir . $fileName;
        }

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $uploadMessage = "Image uploaded successfully!";

            $imageDataFile = $uploadDir . 'data.json';
            $imageMeta = [];

            if (file_exists($imageDataFile)) {
                $imageMeta = json_decode(file_get_contents($imageDataFile), true);
            }

            $imageMeta[$fileName] = [
                'uploader' => $username,
                'timestamp' => date("Y-m-d H:i:s")
            ];

            file_put_contents($imageDataFile, json_encode($imageMeta, JSON_PRETTY_PRINT));
        } else {
            $uploadMessage = "Error moving the uploaded file.";
        }
    } else {
        $uploadMessage = "Invalid file type. Only JPG, PNG, GIF allowed.";
    }
} elseif (!$isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadMessage = "You do not have permission to upload images.";
}

// Load images
$uploadedImages = [];
$meta = [];
$metaFile = $uploadDir . 'data.json';
if (file_exists($metaFile)) {
    $meta = json_decode(file_get_contents($metaFile), true);
}
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $uploadedImages[] = [
                'src' => $uploadDir . $file,
                'file' => $file,
                'uploader' => $meta[$file]['uploader'] ?? 'Unknown'
            ];
        }
    }
}



$allImages = array_merge( $uploadedImages);
?>




<div class="slideshow-container">
  <?php foreach ($allImages as $idx => $img): ?>
    <div class="slide fade">
      <img src="<?php echo htmlspecialchars($img['src']); ?>" alt="Image <?php echo $idx + 1; ?>" />
      <?php if ($isAdmin && isset($img['file'])): ?>
        <div style="position:absolute;bottom:10px;left:10px;background:rgba(0,0,0,0.5);color:#fff;padding:5px 10px;border-radius:5px;">
          Uploaded by: <?php echo htmlspecialchars($img['uploader']); ?>
          <a href="?delete=<?php echo urlencode($img['file']); ?>" onclick="return confirm('Delete this image?')" style="color: red; margin-left: 10px;">Delete</a>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <a class="prev">&#10094;</a>
  <a class="next">&#10095;</a>
</div>

<div class="dots">
  <?php foreach ($allImages as $idx => $_): ?>
    <span class="dot" data-slide="<?php echo $idx; ?>"></span>
  <?php endforeach; ?>
</div>

<?php if ($isAdmin): ?>
<div class="upload-section">
  <?php if ($uploadMessage): ?>
    <p class="<?php echo strpos($uploadMessage, 'success') !== false ? 'message' : 'error'; ?>">
      <?php echo htmlspecialchars($uploadMessage); ?>
    </p>
  <?php endif; ?>
  <form action="" method="post" enctype="multipart/form-data">
    <label for="image">Choose an image to upload:</label>
    <input type="file" name="image" id="image" accept="image/*" required />
    <button type="submit">Upload Image</button>
  </form>
</div>
<?php endif; ?>

  
  <script>
  let slideIndex = 0;
  const slides = document.querySelectorAll('.slide');
  const dots = document.querySelectorAll('.dot');
  const prev = document.querySelector('.prev');
  const next = document.querySelector('.next');

  function showSlide(index) {
    if (index >= slides.length) slideIndex = 0;
    if (index < 0) slideIndex = slides.length - 1;

    slides.forEach((slide, i) => {
      slide.style.display = (i === slideIndex) ? 'block' : 'none';
    });

    dots.forEach((dot, i) => {
      dot.className = (i === slideIndex) ? 'dot active' : 'dot';
    });
  }

  function nextSlide() {
    slideIndex++;
    showSlide(slideIndex);
  }

  function prevSlide() {
    slideIndex--;
    showSlide(slideIndex);
  }

  // Initialize
  showSlide(slideIndex);

  // Event listeners for buttons
  next.addEventListener('click', () => {
    nextSlide();
    resetTimer();
  });
  prev.addEventListener('click', () => {
    prevSlide();
    resetTimer();
  });

  // Event listeners for dots
  dots.forEach((dot, i) => {
    dot.addEventListener('click', () => {
      slideIndex = i;
      showSlide(slideIndex);
      resetTimer();
    });
  });

  // Auto slideshow
  let slideTimer = setInterval(nextSlide, 4000);

  function resetTimer() {
    clearInterval(slideTimer);
    slideTimer = setInterval(nextSlide, 4000);
  }
</script>


<!-- Order Tracking Section -->
<section id="order-tracking">
    <h2>Track Your Order</h2>

    <?php if ($track_error): ?>
        <p class="error"><?= htmlspecialchars($track_error) ?></p>
    <?php endif; ?>

    <?php if ($track_order): ?>
        <div class="order-info">
            <p><strong>Order ID:</strong> <?= htmlspecialchars($track_order['orderid']) ?></p>
            <p><strong>Name:</strong> <?= htmlspecialchars($track_order['name']) ?></p>
            <p><strong>Status:</strong> <?= ucfirst(htmlspecialchars($track_order['status'])) ?></p>
            <p><strong>Order Date:</strong> <?= htmlspecialchars($track_order['order_date']) ?></p>

            <?php if (!empty($track_order['tracking_courier']) || !empty($track_order['tracking_number']) || !empty($track_order['tracking_url'])): ?>
                <h4>Tracking Information</h4>
                <p><strong>Courier:</strong> <?= htmlspecialchars($track_order['tracking_courier']) ?: 'N/A' ?></p>
                <p><strong>Tracking Number:</strong> <?= htmlspecialchars($track_order['tracking_number']) ?: 'N/A' ?></p>
                <?php if (!empty($track_order['tracking_url'])): ?>
                    <p><a href="<?= htmlspecialchars($track_order['tracking_url']) ?>" target="_blank" rel="noopener">Track Shipment</a></p>
                <?php endif; ?>
            <?php else: ?>
                <p>Tracking information is not yet available.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
<form method="POST" action="track.php">
    <input type="hidden" name="track_order" value="1">
    <input type="email" name="email" placeholder="Enter Your Email" required>
    <button type="submit">Track Order</button>
</form>

    <?php endif; ?>
</section>

<!-- Product Gallery -->
<section id="gallery" class="gallery">
    <h2 style="text-align:center; font-size: 2em; margin-bottom: 40px;">🛍️ Fashion Product Gallery</h2>
    <style>
        .category-title {
            margin-top: 40px;
            color: #2c3e50;
            font-size: 1.5em;
            border-bottom: 2px solid #ddd;
            padding-bottom: 8px;
        }

        .category-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px 10px;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            width: 220px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card img {
            width: 100%;
            height: 230px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }

        .card h3 {
            font-size: 1.1em;
            margin: 10px 0 5px;
        }

        .card p {
            margin: 5px 0;
            color: #555;
            font-size: 0.95em;
        }

        .order-btn {
            margin: 10px 0 15px;
            padding: 8px 16px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .order-btn:hover {
            background: #0056b3;
        }

        @media (max-width: 768px) {
            .card {
                width: 90%;
            }
        }
    </style>

    <?php
    $categories = ['Formal Dress', 'Boho Dress', 'Elegant Dress', 'Beach Dress', 'Leather Dress'];

    foreach ($categories as $categoryName):
        echo "<h3 class='category-title'>$categoryName</h3>";
        echo "<div class='category-container'>";

        // Fetch products for each category
        $stmt = $conn->prepare("SELECT products.*, categories.categoryname 
                                FROM products 
                                LEFT JOIN categories ON products.catid = categories.catid 
                                WHERE categories.categoryname = ?
                                ORDER BY proid DESC");
        $stmt->bind_param("s", $categoryName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
    ?>
                <div class="card">
                    <a href="product.php?id=<?= $row['proid'] ?>">
                        <img src="img/<?= !empty($row['image']) && file_exists("img/{$row['image']}") ? htmlspecialchars($row['image']) : 'no-image.png' ?>" alt="<?= htmlspecialchars($row['productname']) ?>">
                    </a>
                    <h3><?= htmlspecialchars($row['productname']) ?></h3>
                    <p>Price: $<?= htmlspecialchars($row['price']) ?></p>
                    <p>Size: <?= htmlspecialchars($row['size']) ?></p>
                    <p>Category: <?= htmlspecialchars($row['categoryname']) ?></p>

                    <form action="cart.php" method="post">
                        <input type="hidden" name="add_to_cart" value="1">
                        <input type="hidden" name="proid" value="<?= $row['proid'] ?>">
                        <input type="hidden" name="productname" value="<?= htmlspecialchars($row['productname']) ?>">
                        <input type="hidden" name="price" value="<?= htmlspecialchars($row['price']) ?>">
                        <input type="hidden" name="image" value="<?= htmlspecialchars($row['image']) ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="order-btn">🛒 Add to Cart</button>
                    </form>
                </div>
    <?php
            endwhile;
        else:
            echo "<p style='color:#888;'>No products available in this category.</p>";
        endif;

        echo "</div>"; // End of category container
    endforeach;
    ?>
</section>

</main>

<footer>
    <p>&copy; 2025 MyProduct. All rights reserved.</p>
</footer>

<!-- Profile Dropdown Toggle Script -->
<script>
    const profilePic = document.getElementById('profilePic');
    const profileDropdown = document.getElementById('profileDropdown');

    profilePic.addEventListener('click', () => {
        profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', function(event) {
        if (!profilePic.contains(event.target) && !profileDropdown.contains(event.target)) {
            profileDropdown.style.display = 'none';
        }
    });


</script>
</body>
</html>
