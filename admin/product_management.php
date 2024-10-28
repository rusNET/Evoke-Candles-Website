<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Function to sanitize image filenames
function sanitize_image_name($string) {
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($string));
}

// Handle product addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $productName = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $discount_price = isset($_POST['discount_price']) && $_POST['discount_price'] !== '' ? $_POST['discount_price'] : 'NULL';
    $inStock = isset($_POST['inStock']) ? $_POST['inStock'] : 'yes';

    // Insert the product into the database
    $sql = "INSERT INTO products (name, description, price, discount_price, inStock) VALUES ('$productName', '$description', $price, $discount_price, '$inStock')";
    if ($conn->query($sql) === TRUE) {
        $product_id = $conn->insert_id; // Get the product ID

        // Upload images with sanitized filenames
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] == 0) {
                $target_dir = "../assets/images/";
                
                // Generate sanitized image name: e.g., "product_name_1.jpg"
                $originalFilename = pathinfo($_FILES['images']['name'][$key], PATHINFO_FILENAME);
                $extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                
                $sanitizedName = sanitize_image_name($productName);
                $imageName = "{$sanitizedName}_" . ($key + 1) . ".{$extension}";
                $target_file = $target_dir . $imageName;

                // Move the uploaded file to the target directory
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $conn->query("INSERT INTO product_images (product_id, image_path) VALUES ($product_id, '$imageName')");
                }
            }
        }
        echo "<div class='notification is-success'>Product added successfully!</div>";
    } else {
        echo "<div class='notification is-danger'>Error: " . $conn->error . "</div>";
    }
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    $conn->query("DELETE FROM products WHERE id = $product_id");
}

// Handle inStock toggle
if (isset($_GET['toggle_stock'])) {
    $product_id = $_GET['toggle_stock'];
    $currentStock = $_GET['current_stock'];
    $newStock = ($currentStock === 'yes') ? 'no' : 'yes';
    $conn->query("UPDATE products SET inStock = '$newStock' WHERE id = $product_id");
}

// Retrieve products
$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management - Evoke Candles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.4/css/bulma.min.css">
    <style>
        .image-preview {
            max-width: 100px;
            margin: 10px;
            border: 1px solid #ccc;
        }
    </style>
    <script>
        function previewImages() {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = ''; // Clear previous previews
            const files = document.getElementById('image-input').files;

            for (let i = 0; i < files.length; i++) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.classList.add('image-preview');
                    preview.appendChild(img);
                }
                reader.readAsDataURL(files[i]);
            }
        }
    </script>
</head>
<body>
    <section class="hero is-info">
        <div class="hero-body">
            <div class="container">
                <h1 class="title">Product Management</h1>
                <a class="button is-danger" href="logout.php">Logout</a>
            </div>
        </div>
    </section>

    <div class="container">
        <h2 class="subtitle">Add New Product</h2>
        <form action="product_management.php" method="POST" enctype="multipart/form-data" class="box">
            <div class="field">
                <label class="label">Product Name</label>
                <div class="control">
                    <input class="input" type="text" name="name" placeholder="Product Name" required>
                </div>
            </div>
            <div class="field">
                <label class="label">Description</label>
                <div class="control">
                    <textarea class="textarea" name="description" placeholder="Description" required></textarea>
                </div>
            </div>
            <div class="field">
                <label class="label">Price</label>
                <div class="control">
                    <input class="input" type="number" name="price" placeholder="Price" required>
                </div>
            </div>
            <div class="field">
                <label class="label">Discount Price</label>
                <div class="control">
                    <input class="input" type="number" name="discount_price" placeholder="Discount Price (optional)">
                </div>
            </div>
            <div class="field">
                <label class="label">In Stock</label>
                <div class="control">
                    <div class="select">
                        <select name="inStock">
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="field">
                <label class="label">Product Images</label>
                <div class="control">
                    <input class="input" type="file" name="images[]" id="image-input" accept="image/*" multiple onchange="previewImages()" required>
                </div>
            </div>
            <div id="image-preview" class="field">
                <!-- Image previews will be shown here -->
            </div>
            <div class="field">
                <div class="control">
                    <button class="button is-link" name="add_product">Add Product</button>
                </div>
            </div>
        </form>

        <h2 class="subtitle">Existing Products</h2>
        <table class="table is-fullwidth is-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Discount Price</th>
                    <th>In Stock</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $result->fetch_assoc()) { 
                    $firstImageResult = $conn->query("SELECT image_path FROM product_images WHERE product_id = " . $product['id'] . " LIMIT 1");
                    $firstImage = $firstImageResult->fetch_assoc(); ?>
                    <tr>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['description']; ?></td>
                        <td>₹<?php echo $product['price']; ?></td>
                        <td><?php echo $product['discount_price'] ? '₹' . $product['discount_price'] : '-'; ?></td>
                        <td>
                            <?php echo $product['inStock']; ?>
                            <a href="product_management.php?toggle_stock=<?php echo $product['id']; ?>&current_stock=<?php echo $product['inStock']; ?>" class="button is-small"><?php echo $product['inStock'] === 'yes' ? 'Mark Out of Stock' : 'Mark In Stock'; ?></a>
                        </td>
                        <td><img src="../assets/images/<?php echo $firstImage['image_path']; ?>" alt="<?php echo $product['name']; ?>" style="width: 100px; height: auto;"></td>
                        <td>
                            <a class="button is-warning is-small" href="update_product.php?id=<?php echo $product['id']; ?>">Update</a>
                            <a class="button is-danger is-small" href="product_management.php?delete=<?php echo $product['id']; ?>">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
