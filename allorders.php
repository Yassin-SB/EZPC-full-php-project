<?php
// i4a mouch admin me to4horch
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require 'db_connection.php'; 

// delete
if (isset($_GET['delete']) && isset($_GET['id_commande'])) {
    $id_commande = intval($_GET['id_commande']); 
    $delete_query = "DELETE FROM commande WHERE id_commande = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $id_commande);
    $success = mysqli_stmt_execute($stmt);
    // message
    if ($success) {
        echo "<script>alert('Order deleted successfully!'); window.location.href = 'allorders.php';</script>";
    } else {
        echo "<script>alert('Error deleting order. Please try again.');</script>";
    }
}

// Fetch all orders 
$query_orders = "
    SELECT c.id_commande, c.id_user, u.username AS username, c.prix_total, c.date
    FROM commande c
    JOIN users u ON c.id_user = u.id_user
    ORDER BY c.date DESC
";
$orders_result = mysqli_query($conn, $query_orders);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Orders</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbars.php'; ?> 

    <div class="orders-container">
        <h2>All Orders</h2>

        <?php if (mysqli_num_rows($orders_result) > 0) { ?>
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Client ID</th>
                        <th>Username</th>
                        <th>Total Price ($)</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders_result)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id_commande']); ?></td>
                            <td><?php echo htmlspecialchars($order['id_user']); ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo number_format($order['prix_total'], 2); ?> €</td>
                            <td><?php echo htmlspecialchars($order['date']); ?></td>
                            <td>
                                <a href="orders.php?id_commande=<?php echo $order['id_commande']; ?>" class="btn btn-edit">Edit</a>
                                <a href="allorders.php?delete=true&id_commande=<?php echo $order['id_commande']; ?>" 
                                   class="btn btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this order?');">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p class="no-orders-message">No orders available.</p>
        <?php } ?>
    </div>
    
</body>
</html>
