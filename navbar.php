<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
/* General Reset */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* --- Navbar Styling --- */
.navbar {
    background: linear-gradient(90deg, #007BFF, #00B4D8);
    padding: 12px 20px;
    position: sticky;
    top: 0;
    z-index: 999;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    animation: fadeInDown 0.5s ease forwards;
}

.nav-container {
    max-width: 1200px;
    margin: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
}

/* Logo */
.nav-logo {
    font-size: 2rem;
    font-weight: 800;
    color: white;
    animation: slideInLeft 1s ease;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.15);
    cursor: pointer;
}

/* Nav Links */
.nav-links {
    display: flex;
    gap: 20px;
    list-style: none;
    animation: fadeIn 1.2s ease;
}

.nav-links a {
    text-decoration: none;
    color: white;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 4px;
    transition: background 0.3s, transform 0.3s;
}

.nav-links a:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: scale(1.05);
}

/* Mobile Menu Toggle */
.nav-toggle {
    display: none;
    font-size: 28px;
    color: white;
    cursor: pointer;
}

/* Responsive */
@media (max-width: 768px) {
    .nav-links {
        display: none;
        flex-direction: column;
        background: white;
        position: absolute;
        top: 70px;
        right: 20px;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        z-index: 999;
        animation: fadeInDown 0.4s ease;
    }

    .nav-links.show {
        display: flex;
    }

    .nav-links a {
        color: #007BFF;
    }

    .nav-toggle {
        display: block;
    }
}

/* Search + Cart + Profile */
.right-items {
    display: flex;
    align-items: center;
    gap: 20px;
    animation: slideInRight 1s ease;
}

/* Search */
.search-box {
    position: relative;
}

.search-box input {
    padding: 6px 12px;
    border-radius: 20px;
    border: none;
    font-size: 0.95rem;
    transition: 0.3s ease;
}

.search-box input:focus {
    outline: none;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
}

/* Search Results */
.search-results {
    position: absolute;
    top: 36px;
    left: 0;
    background: white;
    color: black;
    width: 220px;
    max-height: 200px;
    overflow-y: auto;
    border-radius: 8px;
    box-shadow: 0 5px 18px rgba(0, 0, 0, 0.2);
    display: none;
    z-index: 1000;
}

.search-results div {
    padding: 10px;
    cursor: pointer;
}

.search-results div:hover {
    background-color: #007BFF;
    color: white;
}

/* Cart */
.cart-button {
    background: white;
    color: #007BFF;
    padding: 6px 14px;
    border: none;
    border-radius: 20px;
    font-weight: 700;
    position: relative;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    transition: transform 0.3s;
}

.cart-button:hover {
    transform: scale(1.05);
}

.cart-badge {
    position: absolute;
    top: -6px;
    right: -10px;
    background: red;
    color: white;
    font-size: 12px;
    font-weight: bold;
    border-radius: 50%;
    padding: 3px 7px;
}

/* Profile */
.profile-img {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    cursor: pointer;
    transition: transform 0.3s;
}

.profile-img:hover {
    transform: scale(1.1);
}

/* Animations */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-40px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(40px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo" onclick="location.href='index.php'">MySite</div>

        <div class="nav-toggle" onclick="toggleMenu()">☰</div>

        <ul class="nav-links" id="navMenu">
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['username'])): ?>
                
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="register.php">Register</a>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </ul>

        <div class="right-items">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search...">
                <div class="search-results" id="searchResults"></div>
            </div>

            <button class="cart-button" onclick="location.href='cart.php'">
                🛒 Cart
                <?php
                $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                if ($cartCount > 0) {
                    echo "<span class='cart-badge'>$cartCount</span>";
                }
                ?>
            </button>

            <?php
            $navProfileImage = 'default-user.png';
            if (isset($_SESSION['userid'])) {
                include_once('db.php');
                $userid = $_SESSION['userid'];
                $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
                $stmt->bind_param("i", $userid);
                $stmt->execute();
                $stmt->bind_result($img);
                if ($stmt->fetch() && !empty($img) && file_exists("img/$img")) {
                    $navProfileImage = $img;
                }
                $stmt->close();
            }
            ?>
            <?php if (isset($_SESSION['userid'])): ?>
                <a href="image.php">
                    <img src="img/<?= htmlspecialchars($navProfileImage) ?>" class="profile-img" alt="Profile">
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- JS: Toggle Menu + Live Search -->
<script>
function toggleMenu() {
    document.getElementById("navMenu").classList.toggle("show");
}

document.getElementById("searchInput").addEventListener("keyup", function () {
    const query = this.value;
    const resultsBox = document.getElementById("searchResults");

    if (query.length < 2) {
        resultsBox.style.display = "none";
        return;
    }

    fetch("search.php?q=" + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            resultsBox.innerHTML = "";
            if (data.length > 0) {
                data.forEach(item => {
                    const div = document.createElement("div");
                    div.textContent = item.productname;
                    div.onclick = () => window.location.href = "product.php?id=" + item.proid;
                    resultsBox.appendChild(div);
                });
            } else {
                resultsBox.innerHTML = "<div>No results found</div>";
            }
            resultsBox.style.display = "block";
        });
});
</script>
