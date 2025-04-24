<?php
session_start();
include 'db_connection.php'; 

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    // Check if el product mawjoud fl panier
    $checkQuery = "SELECT * FROM panier_items WHERE id_user = ? AND id_produit = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // i4a mawjoud update the quantity
        $updateQuery = "UPDATE panier_items SET quantite = quantite + ? WHERE id_user = ? AND id_produit = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        $stmt->execute();
    } else {
        // sinon insert product fl cart
        $insertQuery = "INSERT INTO panier_items (id_user, id_produit, quantite) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $stmt->execute();
    }
    echo "<script>alert('Added to cart successfully!'); window.location.href = 'home.php';</script>";

    exit();
}
?>
