<?php
session_start(); 
require_once 'db_connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    // Fetch product details from the database
    $stmt = $conn->prepare("SELECT * FROM produits WHERE id_produit = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Product not found!");
    }
} else {
    die("Invalid request.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['nom']); ?> - Product Details</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
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
            
            <!-- Product Info -->
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['nom']); ?></h1>
                <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <p class="stock">Stock: <strong><?php echo htmlspecialchars($product['stock']); ?></strong></p>
                <p class="price">
                    <?php if ($product['promotion'] > 0): ?>
                        <span class="old-price">$<?php echo number_format($product['prix'], 2); ?></span>
                        <span class="new-price">$<?php echo number_format($product['prix'] * (1 - $product['promotion'] / 100), 2); ?></span>
                    <?php else: ?>
                        <span class="new-price">$<?php echo number_format($product['prix'], 2); ?></span>
                    <?php endif; ?>
                </p>
                <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id_produit']); ?>">
                    
                    
                    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] == 'user'): ?> 
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" min="1" max="<?php echo htmlspecialchars($product['stock']); ?>" value="0" class="quantity-input">
                        <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                    <?php endif; ?>
                </form>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <form action="edit.php" method="GET">
                        <input type="hidden" name="product_id" value="<?php echo $product['id_produit']; ?>">
                        <button type="submit">Edit</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
