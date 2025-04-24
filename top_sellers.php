<?php

session_start();
include 'db_connection.php'; 


$topSellersQuery = "SELECT * FROM produits ORDER BY nbvendues DESC LIMIT 10";
$topSellersResult = $conn->query($topSellersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css"> 
    <title>Top Sellers</title>
</head>
<body>
    <?php include 'navbars.php'; ?> 

    <div class="content">
        <div class="promotions">
            <h2>Top 10 Best-Selling Products</h2>
            <div class="product-grid">
                <?php while ($product = $topSellersResult->fetch_assoc()) { ?>
                    <div class="product-card">
                        <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['nom']; ?>">
                        <div class="product-info">
                            <h3><?php echo $product['nom']; ?></h3>
                            <?php if ($product['promotion'] > 0) { ?>
                                        <p class="price">
                                            <span class="old-price">$<?php echo number_format($product['prix'], 2); ?></span>
                                            <span class="new-price">$<?php echo number_format($product['prix'] * (1 - $product['promotion'] / 100), 2); ?></span>
                                        </p>
                                    <?php } else { ?>
                                        <p class="price">$<?php echo number_format($product['prix'], 2); ?></p>
                                    <?php } ?>
                            <p class="sold">Sold: <?php echo $product['nbvendues']; ?> units</p>
                            <?php if (!$isAdmin): ?> 
                                            <form action="add_to_cart.php" method="POST">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id_produit']; ?>">
                                                <input type="number" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="0" class="quantity-input">
                                                <button type="submit">Add to Cart</button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="view_product.php" method="GET">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id_produit']; ?>">
                                            <button type="submit">View Product</button>
                                        </form>
                                        <?php if ($isAdmin): ?>
                                            <form action="edit.php" method="GET">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id_produit']; ?>">
                                                <button type="submit">Edit</button>
                                            </form>
                                        <?php endif; ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
