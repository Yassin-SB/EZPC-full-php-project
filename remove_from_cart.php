<?php
session_start();
include 'db_connection.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

// Remove product mel cart
$query = "DELETE FROM panier_items WHERE id_user = ? AND id_produit = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();


header("Location: cart.php");
exit();
?>
