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

// Fetch product details if an ID is provided
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();
    if (!$product) {
        echo "Product not found.";
        exit();
    }
} else {
    echo "No product ID specified.";
    exit();
}

// Update product details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $productName = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $discount_price = $_POST['discount_price'] ?: null; // Allow null for discount price
    $inStock = $_POST['inStock'];

    // Update product in the database
    $sql = "UPDATE products SET name = '$productName', description = '$description', price = $price, discount_price = $discount_price, inStock = '$inStock' WHERE id = $product_id";
    if ($conn->query($sql) === TRUE) {
        // Handle image updates
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            // Remove existing images
            $conn->query("DELETE FROM product_images WHERE product_id = $product_id");

            // Upload new images
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] == 0) {
                    $target_dir = "../assets/images/";
                    $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($productName));
                    $extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $imageName = "{$sanitizedName}_" . ($key + 1) . ".{$extension}";
                    $target_file = $target_dir . $imageName;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $conn->query("INSERT INTO product_images (product_id, image_path) VALUES ($product_id, '$imageName')");
                    }
                }
            }
        }
        echo "<div class='notification is-success'>Product updated successfully!</div>";
    } else {
        echo "<div class='notification is-danger'>Error: " . $conn->error . "</div>";
    }
}

// Fetch existing images for display
$imagesResult = $conn->query("SELECT image_path FROM product_images WHERE product_id = $product_id");
$images = [];
while ($row = $imagesResult->fetch_assoc()) {
    $images[] = $row['image_path'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Product - Evoke Candles</title>
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
                <h1 class="title">Update Product</h1>
                <a class="button is-danger" href="product_management.php">Back to Product Management</a>
            </div>
        </div>
    </section>

    <div class="container">
        <form action="update_product.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data" class="box">
            <div class="field">
                <label class="label">Product Name</label>
                <div class="control">
                    <input class="input" type="text" name="name" value="<?php echo $product['name']; ?>" required>
                </div>
            </div>
            <div class="field">
                <label class="label">Description</label>
                <div class="control">
                    <textarea class="textarea" name="description" required><?php echo $product['description']; ?></textarea>
                </div>
            </div>
            <div class="field">
                <label class="label">Price</label>
                <div class="control">
                    <input class="input" type="number" name="price" value="<?php echo $product['price']; ?>" required>
                </div>
            </div>
            <div class="field">
                <label class="label">Discount Price</label>
                <div class="control">
                    <input class="input" type="number" name="discount_price" value="<?php echo $product['discount_price']; ?>" placeholder="Discount Price (optional)">
                </div>
            </div>
            <div class="field">
                <label class="label">In Stock</label>
                <div class="control">
                    <div class="select">
                        <select name="inStock" required>
                            <option value="yes" <?php echo $product['inStock'] === 'yes' ? 'selected' : ''; ?>>Yes</option>
                            <option value="no" <?php echo $product['inStock'] === 'no' ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="field">
                <label class="label">Product Images</label>
                <div class="control">
                    <input class="input" type="file" name="images[]" id="image-input" accept="image/*" multiple onchange="previewImages()">
                </div>
                <p class="help">Leave empty to keep current images. Otherwise, new images will replace current ones.</p>
            </div>
            <div id="image-preview" class="field">
                <?php foreach ($images as $image): ?>
                    <img src="../assets/images/<?php echo $image; ?>" alt="Current image" class="image-preview">
                <?php endforeach; ?>
            </div>
            <div class="field">
                <div class="control">
                    <button class="button is-link" name="update_product">Update Product</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
