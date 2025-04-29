<?php
// Start session
session_start();
require '../include/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash password for security
    $role = "professor"; // Default role for admin registration

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT email FROM Users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        $message = "Email already registered!";
        $alertType = "danger";
    } else {
        // Insert Admin User
        $stmt = $conn->prepare("INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);

        if ($stmt->execute()) {
            $message = "professor registered successfully!";
            $alertType = "success";
        } else {
            $message = "Registration failed. Please try again.";
            $alertType = "danger";
        }

        $stmt->close();
    }

    $checkEmail->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>professor Registration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-dark text-white">
                    <h3 class="text-center">professor Registration</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($message)) { ?>
                        <div class="alert alert-<?php echo $alertType; ?> text-center">
                            <?php echo $message; ?>
                        </div>
                    <?php } ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name:</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email:</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password:</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">Register</button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <a href="../login.php">Already have an account? Login here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
