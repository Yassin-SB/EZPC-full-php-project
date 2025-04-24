<?php

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require 'db_connection.php'; 


if (!isset($_GET['id_commande']) || !is_numeric($_GET['id_commande'])) {
    echo "Invalid Order ID.";
    exit();
}

$id_commande = intval($_GET['id_commande']); 

// delete
if (isset($_POST['delete_item'])) {
    $id_ci = intval($_POST['id_ci']);
    $delete_query = "DELETE FROM commande_items WHERE id_ci = $id_ci AND id_commande = $id_commande";
    mysqli_query($conn, $delete_query);
    header("Location: orders.php?id_commande=$id_commande");
    exit();
}

// edit
if (isset($_POST['edit_item'])) {
    $id_ci = intval($_POST['id_ci']);
    $new_quantity = intval($_POST['new_quantity']);
    if ($new_quantity > 0) {
        $update_query = "UPDATE commande_items SET quantite = $new_quantity WHERE id_ci = $id_ci AND id_commande = $id_commande";
        mysqli_query($conn, $update_query);
        header("Location: orders.php?id_commande=$id_commande");
        exit();
    }
}

// select orders
$order_query = "SELECT * FROM commande WHERE id_commande = $id_commande";
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    echo "Order not found.";
    exit();
}

// select order items
$order_items_query = "
    SELECT ci.*, p.nom, p.description, p.prix 
    FROM commande_items ci
    JOIN produits p ON ci.id_produit = p.id_produit
    WHERE ci.id_commande = $id_commande
";
$order_items_result = mysqli_query($conn, $order_items_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <?php include 'navbars.php'; ?> 

    <div class="orders-container">
        <h2>Order Items - Order ID: <?php echo htmlspecialchars($id_commande); ?></h2>
        <p><strong>Total Price:</strong> <?php echo number_format($order['prix_total'], 2); ?> €</p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($order['date']); ?></p>

        <?php if (mysqli_num_rows($order_items_result) > 0) { ?>
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($order_items_result)) { ?>
                        <tr>
                            <td><?=$item['nom'];?></td>
                            <td class="description-column"><?php echo htmlspecialchars($item['description']); ?></td>
                            <td><?php echo number_format($item['prix'], 2); ?> €</td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id_ci" value="<?php echo $item['id_ci']; ?>">
                                    <input type="number" name="new_quantity" value="<?=$item['quantite']; ?>" class="quantity-input" min="1" required>
                                    <button type="submit" name="edit_item" class="btn btn-edit">Update</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id_ci" value="<?php echo $item['id_ci']; ?>">
                                    <button type="submit" name="delete_item" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p class="no-orders-message">No items found for this order.</p>
        <?php } ?>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
