<?php
session_start();
require '../include/connection.php';

// Fetch All Departments for Dropdown
$departments = $conn->query("SELECT * FROM Departments");

// Handle Section Addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_section'])) {
    $section_name = trim($_POST['section_name']);
    $department_id = !empty($_POST['department_id']) ? $_POST['department_id'] : NULL;

    if (!empty($section_name)) {
        $stmt = $conn->prepare("INSERT INTO Sections (name, department_id) VALUES (?, ?)");
        $stmt->bind_param("si", $section_name, $department_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Section added successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding section.";
            $_SESSION['msg_type'] = "danger";
        }
        $stmt->close();
    }
}

// Handle Section Deletion
if (isset($_GET['delete'])) {
    $section_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Sections WHERE section_id = ?");
    $stmt->bind_param("i", $section_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Section deleted successfully!";
        $_SESSION['msg_type'] = "warning";
    } else {
        $_SESSION['message'] = "Error deleting section.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
}

// Fetch All Sections with Department Names
$result = $conn->query("
    SELECT Sections.section_id, Sections.name AS section_name, Departments.name AS department_name 
    FROM Sections 
    LEFT JOIN Departments ON Sections.department_id = Departments.department_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sections</title>
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
                <li class="nav-item"><a class="nav-link" href="departments.php">Departments</a></li>
                <li class="nav-item"><a class="nav-link active" href="sections.php">Sections</a></li>
                <li class="nav-item"><a class="nav-link" href="admin-results.php">Results</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="text-center mb-4">Manage Sections</h2>

    <!-- Display Messages -->
    <?php if (isset($_SESSION['message'])) { ?>
        <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> text-center">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php } ?>

    <!-- Add Section Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Add New Section</div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">Section Name:</label>
                    <input type="text" name="section_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Department:</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">-- Select Department --</option>
                        <?php while ($dept = $departments->fetch_assoc()) { ?>
                            <option value="<?php echo $dept['department_id']; ?>"><?php echo $dept['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" name="add_section" class="btn btn-success w-100">Add Section</button>
            </form>
        </div>
    </div>

    <!-- Section List -->
    <div class="card">
        <div class="card-header bg-dark text-white">Existing Sections</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Section Name</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['section_id']; ?></td>
                            <td><?php echo $row['section_name']; ?></td>
                            <td><?php echo $row['department_name'] ?? 'Unassigned'; ?></td>
                            <td>
                                <a href="edit-section.php?id=<?php echo $row['section_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="sections.php?delete=<?php echo $row['section_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
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
