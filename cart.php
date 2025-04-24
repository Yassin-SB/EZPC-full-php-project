<?php
session_start();
include 'db_connection.php'; 

// check i4a fama user 
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php"); // Redirect to signin page if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's cart items
$query = "SELECT p.id_produit, p.nom, p.prix, p.promotion, pi.quantite, p.image, p.stock, p.nbvendues FROM panier_items pi JOIN produits p ON pi.id_produit = p.id_produit WHERE pi.id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();


$total_price = 0;

// confirm order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_order'])) {
   
    $query_cart_items = "SELECT pi.id_produit, pi.quantite, p.prix, p.promotion, p.stock, p.nbvendues FROM panier_items pi JOIN produits p ON pi.id_produit = p.id_produit WHERE pi.id_user = ?";
    $stmt_cart = $conn->prepare($query_cart_items);
    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $cart_items_result = $stmt_cart->get_result();

    while ($item = $cart_items_result->fetch_assoc()) {
        $total_price += $item['prix'] * (1 - $item['promotion'] / 100) * $item['quantite'];
    }

    $conn->begin_transaction();

    try {
        // Insert order into 'commande' table
        $query_order = "INSERT INTO commande (id_user, prix_total, date) VALUES (?, ?, NOW())";
        $stmt_order = $conn->prepare($query_order);
        $stmt_order->bind_param("id", $user_id, $total_price);
        $stmt_order->execute();

        // Get the last inserted order ID
        $order_id = $stmt_order->insert_id;

  
        $cart_items_result->data_seek(0); // Reset ll pointer
        while ($item = $cart_items_result->fetch_assoc()) {
            $product_id = $item['id_produit'];
            $quantity = $item['quantite'];
            $price = $item['prix'];
            $promotion = $item['promotion'];
            $stock = $item['stock'];
            $nbvendues = $item['nbvendues'];

            // Check stock 
            if ($stock < $quantity) {
                throw new Exception("Not enough stock for product ID: $product_id");
            }

            // Insert product into 'commande_items' table
            $query_order_item = "INSERT INTO commande_items (id_commande, id_produit, quantite, date) VALUES (?, ?, ?, NOW())";
            $stmt_order_item = $conn->prepare($query_order_item);
            $stmt_order_item->bind_param("iii", $order_id, $product_id, $quantity);
            $stmt_order_item->execute();

            // Update product stock in 'produits' table
            $new_stock = $stock - $quantity;
            $new_nbvendues = $nbvendues + $quantity; // Increase nbvendues by the quantity ordered
            $query_update_stock = "UPDATE produits SET stock = ?, nbvendues = ? WHERE id_produit = ?";
            $stmt_update_stock = $conn->prepare($query_update_stock);
            $stmt_update_stock->bind_param("iii", $new_stock, $new_nbvendues, $product_id);
            $stmt_update_stock->execute();
        }

        $conn->commit();

        // Delete products melcart
        $query_delete_cart = "DELETE FROM panier_items WHERE id_user = ?";
        $stmt_delete_cart = $conn->prepare($query_delete_cart);
        $stmt_delete_cart->bind_param("i", $user_id);
        $stmt_delete_cart->execute();

        //message
        echo "<script>alert('Your order has been placed successfully!'); window.location.href = 'cart.php';</script>";
    } catch (Exception $e) {
        // Rollback i4a fama erreur
        $conn->rollback();
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Cart</title>
</head>
<body>
    <?php include 'navbars.php'; ?>

    <div class="content">
        <div class="promotions">
            <h2>Your Cart</h2>

            <?php if (isset($success_message)): ?>
                <div class="success-message">
                    <p><?php echo $success_message; ?></p>
                </div>
            <?php elseif (isset($error_message)): ?>
                <div class="error-message">
                    <p><?php echo $error_message; ?></p>
                </div>
            <?php endif; ?>

            <?php if ($result->num_rows > 0): ?>
                <div class="product-grid">
                    <?php while ($item = $result->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['nom']; ?>">
                            <div class="product-info">
                                <h3><?php echo $item['nom']; ?></h3>
                                <p class="price">$<?php echo number_format($item['prix'] * (1 - $item['promotion'] / 100), 2); ?></p>
                                <p class="quantity">Quantity: <?php echo $item['quantite']; ?></p>
                                <p class="total">Total: $<?php echo number_format($item['prix'] * (1 - $item['promotion'] / 100) * $item['quantite'], 2); ?></p>
                                <form action="remove_from_cart.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id_produit']; ?>">
                                    <button type="submit" class="remove-btn">Remove from Cart</button>
                                </form>
                            </div>
                        </div>
                        <?php 
                            $total_price += $item['prix'] * (1 - $item['promotion'] / 100) * $item['quantite'];
                        ?>
                    <?php endwhile; ?>
                </div>

                <div class="cart-summary">
                    <p>Total Price: $<?php echo number_format($total_price, 2); ?></p>
                    <form action="cart.php" method="POST">
                        <button type="submit" name="confirm_order" class="confirm-order-btn">Confirm Order</button>
                    </form>
                </div>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
