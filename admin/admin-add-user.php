<?php
session_start();
require '../include/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch departments and sections
$departments = $conn->query("SELECT * FROM Departments ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$sections = $conn->query("SELECT * FROM Sections ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $selected_departments = $_POST['departments'] ?? [];
    $selected_sections = $_POST['sections'] ?? [];

    // Insert into Users table
    $insert_user = $conn->prepare("INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $insert_user->bind_param("ssss", $name, $email, $password, $role);
    $insert_user->execute();
    $user_id = $insert_user->insert_id; // Get newly created user ID

    // Assign to departments if professor
    if ($role === 'professor') {
        foreach ($selected_departments as $dept_id) {
            $conn->query("INSERT INTO Professor_Departments (professor_id, department_id) VALUES ($user_id, $dept_id)");
        }
        foreach ($selected_sections as $sec_id) {
            $conn->query("INSERT INTO Professor_Sections (professor_id, section_id) VALUES ($user_id, $sec_id)");
        }
    }

    header("Location: admin-manage-users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="admin-dashboard.php">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin-dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin-manage-users.php">View Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="departments.php">Departments</a></li>
                    <li class="nav-item"><a class="nav-link" href="sections.php">Sections</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Add User Form -->
    <div class="container mt-5">
        <h2>Add User</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role:</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="professor">Professor</option>
                    <option value="student">Student</option>
                </select>
            </div>

            <!-- Departments (Only for Professors) -->
            <div class="mb-3" id="departmentSection">
                <label for="departments" class="form-label">Departments:</label>
                <select class="form-select" id="departments" name="departments[]" multiple>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['department_id']; ?>"><?= $dept['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sections (Only for Professors) -->
            <div class="mb-3" id="sectionSection">
                <label for="sections" class="form-label">Sections:</label>
                <select class="form-select" id="sections" name="sections[]" multiple>
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?= $sec['section_id']; ?>"><?= $sec['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Add User</button>
            <a href="admin-manage-users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
