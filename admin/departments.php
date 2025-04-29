<?php
session_start();
require '../include/connection.php';

// Handle Department Addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_department'])) {
    $dept_name = trim($_POST['department_name']);

    if (!empty($dept_name)) {
        $stmt = $conn->prepare("INSERT INTO Departments (name) VALUES (?)");
        $stmt->bind_param("s", $dept_name);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Department added successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding department.";
            $_SESSION['msg_type'] = "danger";
        }
        $stmt->close();
    }
}

// Handle Department Deletion
if (isset($_GET['delete'])) {
    $dept_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Departments WHERE department_id = ?");
    $stmt->bind_param("i", $dept_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Department deleted successfully!";
        $_SESSION['msg_type'] = "warning";
    } else {
        $_SESSION['message'] = "Error deleting department.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
}

// Fetch All Departments
$result = $conn->query("SELECT * FROM Departments");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="admin-dashboard.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="admin-manage-users.php">View Users</a></li>
                <li class="nav-item"><a class="nav-link active" href="departments.php">Departments</a></li>
                <li class="nav-item"><a class="nav-link" href="sections.php">Sections</a></li>
                <li class="nav-item"><a class="nav-link" href="admin-results.php">Results</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="text-center mb-4">Manage Departments</h2>

    <!-- Display Messages -->
    <?php if (isset($_SESSION['message'])) { ?>
        <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> text-center">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php } ?>

    <!-- Add Department Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Add New Department</div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">Department Name:</label>
                    <input type="text" name="department_name" class="form-control" required>
                </div>
                <button type="submit" name="add_department" class="btn btn-success w-100">Add Department</button>
            </form>
        </div>
    </div>

    <!-- Department List -->
    <div class="card">
        <div class="card-header bg-dark text-white">Existing Departments</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Department Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['department_id']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td>
                                <a href="edit-department.php?id=<?php echo $row['department_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="departments.php?delete=<?php echo $row['department_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
