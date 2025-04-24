<?php

session_start();
require_once 'db_connection.php';

$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    // Check if the username and password are not empty
    if (!empty($login) && !empty($password)) {
        // Prepare the SQL query to find the user
        $query = "SELECT * FROM users WHERE login = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // select the user data
            $user = $result->fetch_assoc();

            // compare el password 
            if (password_verify($password, $user['password'])) {
                // Store user info in the session
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['login'] = $user['login'];
                $_SESSION['role'] = $user['role'];

                header("Location: home.php");
                exit();
            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "User not found. Please check your credentials.";
        }

        $stmt->close();
    } else {
        $error = "Both fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body class="signin">
    <div class="signin-container">
        <img src="images/EZPC_LOGO.png" alt="Logo"> 
        <h1>Sign In</h1>
        <p>Shopped with us before? Use the information you provided in store.</p>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="signin.php" method="POST">
            <input type="text" name="login" class="form-input" placeholder="Email or Mobile Phone*" required>
            <input type="password" name="password" class="form-input" placeholder="Password" required>
            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="create-account">
            <p>Don't have an account? <a href="signup.php">Create Account</a></p>
        </div>
    </div>
</body>
</html>
