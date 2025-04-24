<?php
session_start();
include 'db_connection.php';

// parameters
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 5000; 
$sortOption = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'datecreation';
$selectedCategoryId = isset($_GET['id_cat']) ? intval($_GET['id_cat']) : null; 

// Base query for all products
$productsQuery = "SELECT * FROM produits WHERE 1=1";
$params = [];
$types = "";


if ($selectedCategoryId) {
    $productsQuery .= " AND (id_cat = ? OR id_cat IN (SELECT id_cat FROM categories WHERE id_catparent = ?))";
    $params[] = $selectedCategoryId;
    $params[] = $selectedCategoryId;
    $types .= "ii";
}


if ($minPrice !== null) {
    $productsQuery .= " AND prix >= ?";
    $params[] = $minPrice;
    $types .= "d";
}
if ($maxPrice !== null) {
    $productsQuery .= " AND prix <= ?";
    $params[] = $maxPrice;
    $types .= "d";
}

// tri
$sortColumns = [
    'datecreation' => 'datecreation DESC',
    'prix' => 'prix ASC',
    'alphabet' => 'nom ASC',
    'nbvendues' => 'nbvendues DESC'
];
$productsQuery .= " ORDER BY " . ($sortColumns[$sortOption] ?? 'datecreation DESC');


$stmt = $conn->prepare($productsQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$productsResult = $stmt->get_result();

 

// Promotion section
$promotionQuery = "SELECT * FROM produits WHERE promotion > 0";
$promoParams = [];
$promoTypes = "";

if ($selectedCategoryId) {
    $promotionQuery .= " AND (id_cat = ? OR id_cat IN (SELECT id_cat FROM categories WHERE id_catparent = ?))";
    $promoParams[] = $selectedCategoryId;
    $promoParams[] = $selectedCategoryId;
    $promoTypes .= "ii";
}

if ($minPrice !== null) {
    $promotionQuery .= " AND prix >= ?";
    $promoParams[] = $minPrice;
    $promoTypes .= "d";
}
if ($maxPrice !== null) {
    $promotionQuery .= " AND prix <= ?";
    $promoParams[] = $maxPrice;
    $promoTypes .= "d";
}

$promotionQuery .= " ORDER BY " . ($sortColumns[$sortOption] ?? 'promotion DESC');

$promoStmt = $conn->prepare($promotionQuery);
if (!empty($promoParams)) {
    $promoStmt->bind_param($promoTypes, ...$promoParams);
}
$promoStmt->execute();
$promotionResult = $promoStmt->get_result();


$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && $_SESSION['role'] == 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Home</title>
</head>
<body>
    <?php include 'navbars.php'; ?> 

    <div class="content">
        <div class="filter-section">
            <div class="filter">
                <form action="home.php" method="GET" class="filter-form">
                    <div class="filter-item">
                        <label for="price-range">Price Range:</label>
                        <div class="range-inputs">
                            <input type="range" id="min_price" name="min_price" min="0" max="5000" step="0.01" value="<?php echo htmlspecialchars($minPrice); ?>" oninput="updateMinPriceLabel(value)">
                            <input type="range" id="max_price" name="max_price" min="0" max="5000" step="0.01" value="<?php echo htmlspecialchars($maxPrice); ?>" oninput="updateMaxPriceLabel(value)">
                            <div class="price-labelmin">
                                <span id="min-price-label"><?php echo htmlspecialchars($minPrice); ?></span>   
                            </div>
                            <div class="price-labelmax">
                                <span id="max-price-label"><?php echo htmlspecialchars($maxPrice); ?></span>
                            </div>

                        </div>
                    </div>

                    <div class="filter-item">
                        <label for="sort_by">Sort By:</label>
                        <select id="sort_by" name="sort_by">
                            <option value="datecreation" <?php echo $sortOption === 'datecreation' ? 'selected' : ''; ?>>Date</option>
                            <option value="prix" <?php echo $sortOption === 'prix' ? 'selected' : ''; ?>>Price</option>
                            <option value="alphabet" <?php echo $sortOption === 'alphabet' ? 'selected' : ''; ?>>Alphabetical</option>
                            <option value="nbvendues" <?php echo $sortOption === 'nbvendues' ? 'selected' : ''; ?>>Best Sellers</option>
                        </select>
                    </div>

               
                    <?php if ($selectedCategoryId): ?>
                        <input type="hidden" name="id_cat" value="<?php echo $selectedCategoryId; ?>">
                    <?php endif; ?>

                    <div class="filter-item">
                        <button type="submit">Apply</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="main-section">
            <div class="left-section">
                <?php if ($promotionResult->num_rows > 0): ?>
                    <div class="promotions">
                        <h2>Products on Promotion</h2>
                        <div class="product-grid">
                            <?php while ($promo = $promotionResult->fetch_assoc()): ?>
                                <div class="product-card">
                                    <img src="images/<?php echo $promo['image']; ?>" alt="<?php echo $promo['nom']; ?>">
                                    <div class="product-info">
                                        <h3><?php echo $promo['nom']; ?></h3>
                                        <p class="price">
                                            <span class="old-price">$<?php echo number_format($promo['prix'], 2); ?></span>
                                            <span class="new-price">$<?php echo number_format($promo['prix'] * (1 - $promo['promotion'] / 100), 2); ?></span>
                                        </p>
                                        <p class="stock">In stock: <?php echo $promo['stock']; ?></p>
                                        <?php if (!$isAdmin): ?> 
                                            <form action="add_to_cart.php" method="POST">
                                                <input type="hidden" name="product_id" value="<?php echo $promo['id_produit']; ?>">
                                                <input type="number" name="quantity" min="1" max="<?php echo $promo['stock']; ?>" value="1" class="quantity-input">
                                                <button type="submit">Add to Cart</button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="view_product.php" method="GET">
                                            <input type="hidden" name="product_id" value="<?php echo $promo['id_produit']; ?>">
                                            <button type="submit">View Product</button>
                                        </form>
                                        <?php if ($isAdmin): ?>
                                            <form action="edit.php" method="GET">
                                                <input type="hidden" name="product_id" value="<?php echo $promo['id_produit']; ?>">
                                                <button type="submit">Edit</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="all-products">
                    <h2>All Products</h2>
                    <div class="product-grid">
                        <?php if ($productsResult->num_rows > 0): ?>
                            <?php while ($product = $productsResult->fetch_assoc()): ?>
                                <div class="product-card">
                                    <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['nom']; ?>">
                                    <div class="product-info">
                                        <h3><?php echo $product['nom']; ?></h3>
                                        <?php if ($product['promotion'] > 0): ?>
                                            <p class="price">
                                                <span class="old-price">$<?php echo number_format($product['prix'], 2); ?></span>
                                                <span class="new-price">$<?php echo number_format($product['prix'] * (1 - $product['promotion'] / 100), 2); ?></span>
                                            </p>
                                        <?php else: ?>
                                            <p class="price">$<?php echo number_format($product['prix'], 2); ?></p>
                                        <?php endif; ?>
                                        <p class="stock">In stock: <?php echo $product['stock']; ?></p>
                                        <?php if (!$isAdmin): ?> 
                                            <form action="add_to_cart.php" method="POST">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id_produit']; ?>">
                                                <input type="number" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1" class="quantity-input">
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
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No products found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function updateMinPriceLabel(value) {
            document.getElementById("min-price-label").innerText = value;
        }
        function updateMaxPriceLabel(value) {
            document.getElementById("max-price-label").innerText = value;
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
