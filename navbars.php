<?php

include 'db_connection.php'; 

// Fetch top-level categories (id_catparent = NULL)
$categoriesQuery = "SELECT * FROM categories WHERE id_catparent IS NULL";
$categoriesResult = $conn->query($categoriesQuery);


$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css"> 
</head>
<body>
    <!-- First Navbar -->
    <div class="navbar-top">
        <div class="logo">
            <a href="home.php"> <img src="images/EZPC_LOGO (1).png" alt="Logo" class="logo-img"></a>
        </div>
        <div class="search-bar">
            <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="Search for products...">
                <?php if (isset($_GET['id_cat'])): ?>
                    <input type="hidden" name="id_cat" value="<?php echo intval($_GET['id_cat']); ?>">
                <?php endif; ?>
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="user-options">
            <?php if ($isLoggedIn): ?>
                <span>Welcome, <?php echo $_SESSION['username']; ?>!</span>
                <a href="logout.php" class="signin-button" onclick="return confirm('Are you sure you want to logout ?');">Logout</a>
                <?php if ($isAdmin): ?> 
                    <a href="allorders.php" class="orders-button">Orders</a>
                <?php else: ?>
                    <a href="cart.php" class="cart-button">Cart</a> 
                <?php endif; ?>
            <?php else: ?>
                <a href="signin.php" class="signin-button">Sign In</a>
                <a href="cart.php" class="cart-button">Cart</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Second Navbar -->
    <div class="navbar-bottom">
        <ul class="categories-menu">
            <?php while ($category = $categoriesResult->fetch_assoc()) { ?>
                <li class="dropdown">
                    <a href="home.php?id_cat=<?php echo $category['id_cat']; ?>">
                        <?php echo $category['nom']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <?php
                        // select subcategories 
                        $subcategoriesQuery = "SELECT * FROM categories WHERE id_catparent = " . $category['id_cat'];
                        $subcategoriesResult = $conn->query($subcategoriesQuery);
                        if ($subcategoriesResult->num_rows > 0) {
                            
                            while ($subcategory = $subcategoriesResult->fetch_assoc()) {
                                echo "<li><a href='home.php?id_cat={$subcategory['id_cat']}'>{$subcategory['nom']}</a></li>";
                            }
                        } else {
                            echo "<li>No subcategories</li>";
                        }
                        ?>
                    </ul>
                </li>
            <?php } ?>
            <li><a href="top_sellers.php">Best-Sellings</a></li>
        </ul>

        <!-- Admin-only  -->
        <?php if ($isAdmin): ?>
            <div class="admin-buttons">
                <a href="addproduct.php" class="admin-btn">Add Product</a>
                <a href="editcat.php" class="admin-btn">Edit Categories</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
