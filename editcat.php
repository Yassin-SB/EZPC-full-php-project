<?php
session_start();
require_once 'db_connection.php';

$category_id = "";
$name = "";
$parent_category = "";

// Fetch all categories 
$categories = [];
$categoryQuery = "SELECT * FROM categories";
$categoryResult = $conn->query($categoryQuery);
while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_category'])) {
    $category_id = intval($_POST['selected_category']);

    $stmt = $conn->prepare("SELECT * FROM categories WHERE id_cat = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
        $name = $category['nom'];
        $parent_category = $category['id_catparent'];
    }
}

// confirm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $name = $_POST['name'];
    $parent_category = ($_POST['parent_category'] == "") ? NULL : intval($_POST['parent_category']);

    if (isset($_POST['category_id']) && $_POST['category_id'] !== "") {
        // Edit existing category
        $category_id = intval($_POST['category_id']);
        $updateQuery = "UPDATE categories SET nom = ?, id_catparent = ? WHERE id_cat = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sii", $name, $parent_category, $category_id);
        $stmt->execute();
    } else {
        // Add new category
        $insertQuery = "INSERT INTO categories (nom, id_catparent) VALUES (?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("si", $name, $parent_category);
        $stmt->execute();
    }

    header("Location: home.php"); 
    exit();
}

// delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $category_id_to_delete = intval($_POST['delete_category']);

    // Delete category
    $deleteQuery = "DELETE FROM categories WHERE id_cat = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $category_id_to_delete);
    $stmt->execute();

    header("Location: home.php"); // Redirect to home or category list page after deletion
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($category_id) && $category_id !== "" ? 'Edit Category' : 'Add Category'; ?></title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body>
    <?php include 'navbars.php'; ?>
    <div class="product-details-wrapper">
        <div class="product-details-card">
            <!-- Close Button -->
            <button class="close-btn" onclick="history.back()">&#x2715;</button>
            
            <!-- Category Selection Form -->
            <div class="product-info">
                <h1>Select a Category to Edit</h1>
                <form action="editcat.php" method="POST">
                    <label for="selected_category">Choose Category:</label>
                    <select id="selected_category" name="selected_category" onchange="this.form.submit()">
                        <option value="">-- Select a category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id_cat']; ?>" <?php echo ($cat['id_cat'] == $category_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button type="submit">Submit</button></noscript>
                </form>
            </div>

            <!-- Category Form -->
            <div class="product-info">
                <h1><?php echo isset($category_id) && $category_id !== "" ? 'Edit Category' : 'Add New Category'; ?></h1>
                <form action="editcat.php" method="POST" class="edit-category-form">
               
                    <?php if (isset($category_id) && $category_id !== ""): ?>
                        <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category_id); ?>">
                    <?php endif; ?>

                    <label for="name">Category Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter category name" value="<?php echo htmlspecialchars($name); ?>" required>

                    <label for="parent_category">Parent Category:</label>
                    <select id="parent_category" name="parent_category">
                        <option value="">None</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id_cat']; ?>" <?php echo ($cat['id_cat'] == $parent_category) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="form-buttons">
                        <button type="reset" class="reset-btn">Reset</button>
                        <button type="submit" name="confirm" class="confirm-btn">Confirm</button>
                    </div>
                </form>

                <!-- Delete Button (yo4her ken ki lcategory selected) -->
                <?php if (isset($category_id) && $category_id !== ""): ?>
                    <form action="editcat.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
                        <input type="hidden" name="delete_category" value="<?php echo htmlspecialchars($category_id); ?>">
                        <button type="submit" class="delete-btn">Delete Category</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
