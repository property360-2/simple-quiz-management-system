<?php
session_start();
require '../include/connection.php';

// Check if department ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Invalid department!";
    $_SESSION['msg_type'] = "danger";
    header("Location: departments.php");
    exit();
}

$dept_id = $_GET['id'];

// Fetch department details
$stmt = $conn->prepare("SELECT * FROM Departments WHERE department_id = ?");
$stmt->bind_param("i", $dept_id);
$stmt->execute();
$result = $stmt->get_result();
$department = $result->fetch_assoc();
$stmt->close();

if (!$department) {
    $_SESSION['message'] = "Department not found!";
    $_SESSION['msg_type'] = "danger";
    header("Location: departments.php");
    exit();
}

// Handle Department Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_department'])) {
    $dept_name = trim($_POST['department_name']);

    if (!empty($dept_name)) {
        $update_stmt = $conn->prepare("UPDATE Departments SET name = ? WHERE department_id = ?");
        $update_stmt->bind_param("si", $dept_name, $dept_id);

        if ($update_stmt->execute()) {
            $_SESSION['message'] = "Department updated successfully!";
            $_SESSION['msg_type'] = "success";
            header("Location: departments.php");
            exit();
        } else {
            $_SESSION['message'] = "Error updating department.";
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
    <title>Edit Department</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">Edit Department</h2>

    <!-- Display Messages -->
    <?php if (isset($_SESSION['message'])) { ?>
        <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> text-center">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php } ?>

    <!-- Edit Department Form -->
    <div class="card">
        <div class="card-header bg-primary text-white">Update Department</div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">Department Name:</label>
                    <input type="text" name="department_name" class="form-control" value="<?php echo htmlspecialchars($department['name']); ?>" required>
                </div>
                <button type="submit" name="update_department" class="btn btn-success w-100">Update Department</button>
            </form>
        </div>
    </div>

    <div class="text-center mt-3">
        <a href="departments.php" class="btn btn-secondary">Back to Departments</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
