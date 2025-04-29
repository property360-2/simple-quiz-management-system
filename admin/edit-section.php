<?php
session_start();
require '../include/connection.php';

// Check if section ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Invalid Section ID!";
    $_SESSION['msg_type'] = "danger";
    header("Location: sections.php");
    exit();
}

$section_id = $_GET['id'];

// Fetch Section Data
$stmt = $conn->prepare("SELECT * FROM Sections WHERE section_id = ?");
$stmt->bind_param("i", $section_id);
$stmt->execute();
$result = $stmt->get_result();
$section = $result->fetch_assoc();

if (!$section) {
    $_SESSION['message'] = "Section not found!";
    $_SESSION['msg_type'] = "danger";
    header("Location: sections.php");
    exit();
}

$stmt->close();

// Fetch All Departments for Dropdown
$departments = $conn->query("SELECT * FROM Departments");

// Handle Section Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $section_name = trim($_POST['section_name']);
    $department_id = !empty($_POST['department_id']) ? $_POST['department_id'] : NULL;

    if (!empty($section_name)) {
        $update_stmt = $conn->prepare("UPDATE Sections SET name = ?, department_id = ? WHERE section_id = ?");
        $update_stmt->bind_param("sii", $section_name, $department_id, $section_id);

        if ($update_stmt->execute()) {
            $_SESSION['message'] = "Section updated successfully!";
            $_SESSION['msg_type'] = "success";
            header("Location: sections.php");
            exit();
        } else {
            $_SESSION['message'] = "Error updating section.";
            $_SESSION['msg_type'] = "danger";
        }

        $update_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Section</title>
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
                <li class="nav-item"><a class="nav-link" href="results.php">Results</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="text-center mb-4">Edit Section</h2>

    <!-- Display Messages -->
    <?php if (isset($_SESSION['message'])) { ?>
        <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> text-center">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php } ?>

    <!-- Edit Section Form -->
    <div class="card">
        <div class="card-header bg-warning text-white">Update Section Details</div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">Section Name:</label>
                    <input type="text" name="section_name" class="form-control" value="<?php echo htmlspecialchars($section['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Department:</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- Select Department --</option>
                        <?php while ($dept = $departments->fetch_assoc()) { ?>
                            <option value="<?php echo $dept['department_id']; ?>" 
                                <?php echo ($section['department_id'] == $dept['department_id']) ? 'selected' : ''; ?>>
                                <?php echo $dept['name']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Section</button>
                <a href="sections.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
