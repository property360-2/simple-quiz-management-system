<?php
session_start();
require '../include/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch users from the database
$sql = "SELECT Users.user_id, Users.name, Users.email, Users.role, 
            GROUP_CONCAT(DISTINCT Departments.name SEPARATOR ', ') AS departments, 
            GROUP_CONCAT(DISTINCT Sections.name SEPARATOR ', ') AS sections 
        FROM Users 
        LEFT JOIN Professor_Departments ON Users.user_id = Professor_Departments.professor_id 
        LEFT JOIN Departments ON Professor_Departments.department_id = Departments.department_id 
        LEFT JOIN Professor_Sections ON Users.user_id = Professor_Sections.professor_id 
        LEFT JOIN Sections ON Professor_Sections.section_id = Sections.section_id 
        WHERE Users.role != 'admin' 
        GROUP BY Users.user_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
                    <li class="nav-item"><a class="nav-link" href="admin-results.php">Results</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Users Table -->
    <div class="container mt-5">
        <h2>Users</h2>
        <a href="admin-add-user.php" class="btn btn-primary mb-3">Add User</a>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Departments</th>
                    <th>Sections</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?= $row['user_id']; ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= ucfirst($row['role']); ?></td>
                        <td><?= $row['departments'] ?? 'N/A'; ?></td>
                        <td><?= $row['sections'] ?? 'N/A'; ?></td>
                        <td>
                            <a href="admin-edit-user.php?id=<?= $row['user_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="admin-delete-user.php?id=<?= $row['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
