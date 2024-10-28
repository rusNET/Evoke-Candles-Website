<?php
session_start();
include '../includes/db.php';

// Check if the user is already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: product_management.php");
    exit();
}

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['admin_logged_in'] = true; 
        header("Location: product_management.php"); 
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Evoke Candles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.4/css/bulma.min.css">
</head>
<body>
    <section class="hero is-primary is-fullheight">
        <div class="hero-body">
            <div class="container has-text-centered">
                <h1 class="title">Admin Login</h1>
                <form action="login.php" method="POST" class="box">
                    <div class="field">
                        <div class="control">
                            <input class="input" type="text" name="username" placeholder="Username" required>
                        </div>
                    </div>
                    <div class="field">
                        <div class="control">
                            <input class="input" type="password" name="password" placeholder="Password" required>
                        </div>
                    </div>
                    <div class="field">
                        <div class="control">
                            <button class="button is-link">Login</button>
                        </div>
                    </div>
                </form>
                <?php if (isset($error)) { echo "<p class='has-text-danger'>$error</p>"; } ?>
            </div>
        </div>
    </section>
</body>
</html>

