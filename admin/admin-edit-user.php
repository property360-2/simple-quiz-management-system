<?php
session_start();
require '../include/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get user ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin-manage-users.php");
    exit();
}

$user_id = $_GET['id'];

// Fetch user data
$sql = "SELECT Users.*, 
            GROUP_CONCAT(DISTINCT Professor_Departments.department_id) AS department_ids, 
            GROUP_CONCAT(DISTINCT Professor_Sections.section_id) AS section_ids 
        FROM Users 
        LEFT JOIN Professor_Departments ON Users.user_id = Professor_Departments.professor_id 
        LEFT JOIN Professor_Sections ON Users.user_id = Professor_Sections.professor_id 
        WHERE Users.user_id = ? 
        GROUP BY Users.user_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: admin-manage-users.php");
    exit();
}

// Fetch all departments and sections for dropdowns
$departments = $conn->query("SELECT * FROM Departments ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$sections = $conn->query("SELECT * FROM Sections ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Convert department & section IDs to an array for pre-selection
$user_department_ids = explode(",", $user['department_ids'] ?? '');
$user_section_ids = explode(",", $user['section_ids'] ?? '');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $selected_departments = $_POST['departments'] ?? [];
    $selected_sections = $_POST['sections'] ?? [];

    // Update user table
    $update_user = $conn->prepare("UPDATE Users SET name = ?, email = ?, role = ? WHERE user_id = ?");
    $update_user->bind_param("sssi", $name, $email, $role, $user_id);
    $update_user->execute();

    // Update Professor_Departments
    if ($role === 'professor') {
        $conn->query("DELETE FROM Professor_Departments WHERE professor_id = $user_id");
        foreach ($selected_departments as $dept_id) {
            $conn->query("INSERT INTO Professor_Departments (professor_id, department_id) VALUES ($user_id, $dept_id)");
        }
    }

    // Update Professor_Sections
    if ($role === 'professor') {
        $conn->query("DELETE FROM Professor_Sections WHERE professor_id = $user_id");
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
    <title>Edit User</title>
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

    <!-- Edit User Form -->
    <div class="container mt-5">
        <h2>Edit User</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role:</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="professor" <?= $user['role'] === 'professor' ? 'selected' : ''; ?>>Professor</option>
                    <option value="student" <?= $user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                </select>
            </div>

            <!-- Departments (Only for Professors) -->
            <div class="mb-3" id="departmentSection">
                <label for="departments" class="form-label">Departments:</label>
                <select class="form-select" id="departments" name="departments[]" multiple>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['department_id']; ?>" 
                            <?= in_array($dept['department_id'], $user_department_ids) ? 'selected' : ''; ?>>
                            <?= $dept['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sections (Only for Professors) -->
            <div class="mb-3" id="sectionSection">
                <label for="sections" class="form-label">Sections:</label>
                <select class="form-select" id="sections" name="sections[]" multiple>
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?= $sec['section_id']; ?>" 
                            <?= in_array($sec['section_id'], $user_section_ids) ? 'selected' : ''; ?>>
                            <?= $sec['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Update User</button>
            <a href="admin-manage-users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
