<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evoke Candles</title>

    <!-- Bulma CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">

    <!-- Custom CSS -->
    <style>
        body, html {
            margin: 0;
            overflow-x: hidden;
            background-color: #f4f4f4;
        }

        /* Hero Section */
        .hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
            url('../assets/images/landing-bg.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            color: white;
        }

        .hero-body {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .hero .title {
            font-size: 4rem;
        }

        .hero .subtitle {
            font-size: 1.5rem;
        }

        /* Navbar Styling */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: -70px;
            left: 0;
            width: 100%;
            transition: top 0.4s ease-in-out;
            z-index: 1000;
        }

        .navbar-item img {
            max-height: 40px;
        }

        /* Product Section Styling */
        .product-list {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 4 cards per row */
            gap: 20px;
            margin-top: 50px;
            justify-items: center;
        }

        .product-list .card {
            width: 300px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-list .card-image .image img {
            width: 100%;
            height: 320px;
            object-fit: cover;
        }

        /* Align "View Product" button at the bottom */
        .card-content {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        /* Custom styling for discount price */
        .original-price {
            text-decoration: line-through;
            color: red;
            margin-right: 5px;
        }

        /* Footer */
        .footer {
            background-color: #222;
            color: white;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav id="navbar" class="navbar">
        <div class="navbar-brand">
            <a class="navbar-item" href="index.php">
                <img src="../assets/images/logo.png" alt="Evoke Candles Logo">
            </a>
            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarMenu">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarMenu" class="navbar-menu">
            <div class="navbar-start">
                <a href="index.php" class="navbar-item">Home</a>
                <a href="cart.php" class="navbar-item">Cart</a>
                <a href="wishlist.php" class="navbar-item">Wishlist</a>
            </div>
            <div class="navbar-end">
                <a href="login.php" class="navbar-item">Login</a>
                <a href="register.php" class="navbar-item">Register</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero is-fullheight">
        <div class="hero-body">
            <div>
                <h1 class="title">Evoke Candles</h1>
                <p class="subtitle">Illuminate your moments with scents that inspire.</p>
            </div>
        </div>
    </section>

    <!-- Product Section -->
    <div class="container">
        <section class="product-list" id="productSection">
            <?php
            // Query to fetch in-stock products with optional discount
            $query = "
                SELECT p.id, p.name, p.description, p.price, p.discount_price, pi.image_path 
                FROM products p
                LEFT JOIN product_images pi ON pi.product_id = p.id
                WHERE p.inStock = 'yes'
                GROUP BY p.id
            ";
            $result = $conn->query($query);

            while ($product = $result->fetch_assoc()) {
                $image = $product['image_path'] ? $product['image_path'] : 'default.jpg';
                $price = "₹ " . number_format($product['price'], 2);
                $discount_price = $product['discount_price'] ? "₹ " . number_format($product['discount_price'], 2) : null;

                echo "
                    <div class='card'>
                        <div class='card-image'>
                            <figure class='image'>
                                <img src='../assets/images/{$image}' alt='{$product['name']}'>
                            </figure>
                        </div>
                        <div class='card-content'>
                            <p class='title is-4'>{$product['name']}</p>
                            <div class='content'>
                                {$product['description']}
                            </div>";

                // Display original and discount price
                if ($discount_price) {
                    echo "
                        <p class='subtitle is-6'>
                            <span class='original-price'>{$price}</span>
                            <span>{$discount_price}</span>
                        </p>";
                } else {
                    echo "<p class='subtitle is-6'>{$price}</p>";
                }

                echo "
                            <a href='product.php?id={$product['id']}' class='button is-link mt-3'>View Product</a>
                        </div>
                    </div>
                ";
            }
            ?>
        </section>
    </div>

    <footer class="footer mt-5">
        <div class="content has-text-centered">
            <p><strong>Evoke Candles</strong> &copy; 2024. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- JavaScript to Show Navbar After Product Section is in View -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const navbar = document.getElementById('navbar');
            const productSection = document.getElementById('productSection');
            const productSectionPosition = productSection.getBoundingClientRect().top + window.scrollY;

            window.addEventListener('scroll', () => {
                if (window.scrollY >= productSectionPosition - window.innerHeight + 100) {
                    navbar.style.top = '0'; // Show navbar
                } else {
                    navbar.style.top = '-70px'; // Hide navbar
                }
            });

            const burger = document.querySelector('.navbar-burger');
            const menu = document.querySelector('#navbarMenu');

            burger.addEventListener('click', () => {
                burger.classList.toggle('is-active');
                menu.classList.toggle('is-active');
            });
        });
    </script>

</body>
</html>
