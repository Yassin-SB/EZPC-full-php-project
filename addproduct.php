<?php
session_start();
require_once 'db_connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // retrive
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = intval($_POST['category']);
    $stock = intval($_POST['stock']);
    $price = floatval($_POST['price']);
    $promotion = floatval($_POST['promotion']);
    $nbvendues = intval($_POST['nbvendues']);

    // image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imageExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageExtension, $allowedExtensions)) {
            $newImageName = uniqid("prod_", true) . "." . $imageExtension; 
            $destinationPath = "images/" . $newImageName; 

            if (move_uploaded_file($imageTmpPath, $destinationPath)) {
                $image = $newImageName; 
            } else {
                die("Error moving uploaded file.");
            }
        } else {
            die("Invalid image format. Allowed: JPG, JPEG, PNG, GIF.");
        }
    } else {
        $image = "default.png"; 
    }

    // Insert product fel database
    $insertQuery = "
        INSERT INTO produits (nom, description, id_cat, stock, prix, promotion, nbvendues, image, datecreation) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssiiidis", $name, $description, $category, $stock, $price, $promotion, $nbvendues, $image);

    if ($stmt->execute()) {
        echo "<script>alert('Product added successfully!'); window.location.href = 'home.php';</script>";
    } else {
        die("Error adding product: " . $stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body>
    <?php include 'navbars.php'; ?>
    <div class="product-details-wrapper">
        <div class="product-details-card">
            <!-- Close Button -->
            <button class="close-btn" onclick="history.back()">&#x2715;</button>      
            <!-- Product Info Form -->
            <div class="product-info">
                <h1>Add New Product</h1>

                <form action="addproduct.php" method="POST" enctype="multipart/form-data" class="add-product-form">
                    <label for="name">Product Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter product name" required>

                    <label for="description">Product Description:</label>
                    <textarea id="description" name="description" placeholder="Enter product description" required  rows="10" cols="70"></textarea>

                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="" disabled selected>Select a category</option>
                        <?php
                        // Fetch el categories mel database
                        $categoryQuery = "SELECT * FROM categories";
                        $categoryResult = $conn->query($categoryQuery);
                        while ($category = $categoryResult->fetch_assoc()) {
                            echo "<option value='{$category['id_cat']}'>{$category['nom']}</option>";
                        }
                        ?>
                    </select>

                    <label for="stock">Stock Quantity:</label>
                    <input type="number" id="stock" name="stock" placeholder="Enter stock quantity" required>

                    <label for="price">Price ($):</label>
                    <input type="number" id="price" name="price" placeholder="Enter price" step="0.01" required>

                    <label for="promotion">Promotion (%):</label>
                    <input type="number" id="promotion" name="promotion" placeholder="Enter promotion %" step="0.01">

                    <label for="nbvendues">Number of Items Sold:</label>
                    <input type="number" id="nbvendues" name="nbvendues" placeholder="Enter items sold count" required>

                    <!-- Image Upload -->
                    <label for="image">Product Image:</label>
                    <input type="file" id="image" name="image" accept="image/*">

                    <div class="form-buttons">
                        <button type="reset" class="reset-btn">Reset</button>
                        <button type="submit" name="confirm" class="confirm-btn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
