<?php
session_start();
require_once 'db_connection.php'; 


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    // Fetch product details mel database
    $stmt = $conn->prepare("SELECT * FROM produits WHERE id_produit = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Product not found!");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && !isset($_POST['delete'])) {
    // Update the product details
    $product_id = intval($_POST['product_id']);
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = intval($_POST['category']);
    $stock = intval($_POST['stock']);
    $price = floatval($_POST['price']);
    $promotion = floatval($_POST['promotion']);
    $nbvendues = intval($_POST['nbvendues']); 
    // Update product in the database
    $updateQuery = "
        UPDATE produits 
        SET nom = ?, description = ?, id_cat = ?, stock = ?, prix = ?, promotion = ?, nbvendues = ? 
        WHERE id_produit = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssiiidii", $name, $description, $category, $stock, $price, $promotion, $nbvendues, $product_id);
    if ($stmt->execute()) {
 
        header("Location: view_product.php?product_id=$product_id");
        exit();
    } else {
        die("Error updating product: " . $stmt->error);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    // Delete product
    $productId = $_POST['product_id'];
    $deleteQuery = "DELETE FROM produits WHERE id_produit = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $productId);
    if ($deleteStmt->execute()) {
        header("Location: home.php"); 
        exit();
    } else {
        die("Error deleting product: " . $deleteStmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - <?php echo htmlspecialchars($product['nom']); ?></title>
    <link rel="stylesheet" href="styles.css"> 
<body>
    <?php include 'navbars.php'; ?> 
    <div class="product-details-wrapper">
        <div class="product-details-card">
            <!-- Close Button -->
            <button class="close-btn" onclick="history.back()">&#x2715;</button>
            
            <!-- Product Image -->
            <div class="product-image">
                <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['nom']); ?>">
            </div>
            
            <!-- Product Info Form -->
            <div class="product-info">
                <h1>Edit Product</h1>

                <form action="edit.php" method="POST" class="edit-product-form">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id_produit']); ?>">

                    <label for="name">Product Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['nom']); ?>" required >

                    <label for="description">Product Description:</label>
                    <textarea id="description" name="description" required rows="10" cols="70" ><?php echo htmlspecialchars($product['description']); ?></textarea>

                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <?php
                        $categoryQuery = "SELECT * FROM categories";
                        $categoryResult = $conn->query($categoryQuery);
                        while ($category = $categoryResult->fetch_assoc()) {
                            echo "<option value='{$category['id_cat']}'" . ($category['id_cat'] == $product['id_cat'] ? ' selected' : '') . ">{$category['nom']}</option>";
                        }
                        ?>
                    </select>

                    <label for="stock">Stock Quantity:</label>
                    <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required>

                    <label for="price">Price ($):</label>
                    <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($product['prix']); ?>" step="0.01" required>

                    <label for="promotion">Promotion (%):</label>
                    <input type="number" id="promotion" name="promotion" value="<?php echo htmlspecialchars($product['promotion']); ?>" step="0.01">


                    <label for="nbvendues">Number of Items Sold:</label>
                    <input type="number" id="nbvendues" name="nbvendues" value="<?php echo htmlspecialchars($product['nbvendues']); ?>" required>

                    <button type="submit" class="confirm-btn">Confirm</button>
                </form>
                
                <!-- Delete Product Form -->
                <form action="edit.php" method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this product?');">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id_produit']); ?>">
                    <button type="submit" name="delete" class="delete-btn">Delete Product</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
