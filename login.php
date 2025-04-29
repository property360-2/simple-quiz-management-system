<?php
session_start();
require 'include/connection.php'; // Database connection

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/admin-dashboard.php");
    } elseif ($_SESSION['role'] == 'professor') {
        header("Location: professor/professor-dashboard.php");
    } else {
        header("Location: student/student-dashboard.php");
    }
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT u.user_id, u.name, u.password, u.role, 
                               d.name, s.name
                        FROM Users u
                        LEFT JOIN Departments d ON u.department_id = d.department_id
                        LEFT JOIN Sections s ON u.section_id = s.section_id
                        WHERE u.email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $name, $hashed_password, $role, $department_name, $section_name);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;
            $_SESSION['department_name'] = $department_name ?: 'N/A'; // Store department name
            $_SESSION['section_name'] = $section_name ?: 'N/A';       // Store section name

            // Redirect based on role
            if ($role == "admin") {
                header("Location: admin/admin-dashboard.php");
            } elseif ($role == "professor") {
                header("Location: professor/professor-dashboard.php");
            } else {
                header("Location: student/student-dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }


    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

</head>

<body>

    <div class="container mt-5">
        <form action="login.php" method="POST" class="w-50 mx-auto p-4 border rounded shadow">
            <h2 class="text-center">Login</h2>
            <?php if (!empty($error))
                echo "<p class='alert alert-danger'>$error</p>"; ?>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
            <p>dont have an account?<a href="student/student-registration.php">Register</a></p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>