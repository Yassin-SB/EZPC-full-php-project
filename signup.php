<?php

require_once 'db_connection.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']); 
    $login = trim($_POST['login']); 
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone_number = trim($_POST['phone_number']);
    $role = 'user'; // Default role for new users

    if (!empty($username) && !empty($login) && !empty($password) && !empty($confirm_password) && !empty($phone_number)) {
        if ($password === $confirm_password) {
            // Check if the login (email or phone number) already exists
            $query = "SELECT * FROM users WHERE login = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $login);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                // Check if the phone number already exists
                $query = "SELECT * FROM users WHERE phone_number = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $phone_number);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    // Hash the password before storing it in the database
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert the new user into the database with hashed password
                    $query = "INSERT INTO users (username, login, password, role, phone_number) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssss", $username, $login, $hashed_password, $role, $phone_number);

                    if ($stmt->execute()) {
                        $success = "Account created successfully! <a href='signin.php'>Sign in here</a>.";
                    } else {
                        $error = "Error occurred. Please try again.";
                    }
                } else {
                    $error = "Phone number already exists. Please choose another.";
                }
            } else {
                $error = "Login (email/phone) already exists. Please choose another.";
            }

            $stmt->close();
        } else {
            $error = "Passwords do not match.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body class="signup">
    <div class="signin-container">
        <img src="images/EZPC_LOGO" alt="Logo"> 
        <h1>Create Account</h1>
        <p>Sign up to start shopping with us!</p>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="signup.php" method="POST">
            <input type="text" name="username" class="form-input" placeholder="Full Name" required>
            <input type="text" name="login" class="form-input" placeholder="Login (Email or Phone)" required>
            <input type="password" name="password" class="form-input" placeholder="Password" minlength="8" required>
            <input type="password" name="confirm_password" class="form-input" placeholder="Confirm Password" minlength="8" required>
            <input type="text" name="phone_number" class="form-input" placeholder="Phone Number" pattern="\d{8}" title="Phone number must be exactly 8 digits and contain only numbers" required>
            <button type="submit" class="btn">Sign Up</button>
        </form>


        <div class="create-account">
            <p>Already have an account? <a href="signin.php">Sign In</a></p>
        </div>
    </div>
</body>
</html>
