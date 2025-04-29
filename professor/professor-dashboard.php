<?php
session_start();
require '../include/connection.php';

// Check if professor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit();
}

$professor_id = $_SESSION['user_id'];


// Fetch the professor's assigned departments
$sqlDepartments = "SELECT d.name AS department_name 
                   FROM Departments d 
                   JOIN Professor_Departments pd ON d.department_id = pd.department_id 
                   WHERE pd.professor_id = ?";
$stmt = $conn->prepare($sqlDepartments);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$departments = $stmt->get_result();

// Fetch the professor's assigned sections
$sqlSections = "SELECT s.name AS section_name 
                FROM Sections s 
                JOIN Professor_Sections ps ON s.section_id = ps.section_id 
                WHERE ps.professor_id = ?";
$stmt = $conn->prepare($sqlSections);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$sections = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="professor-dashboard.php">Professor Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="professor-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="professor-assessments.php">Assessments</a></li>
                    <li class="nav-item"><a class="nav-link" href="professor-results.php">Student Results</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container mt-5">
        <h2>Welcome, Professor!</h2>

        <!-- Assigned Departments -->
        <div class="mb-4">
            <h4>Assigned Departments</h4>
            <ul class="list-group">
                <?php while ($row = $departments->fetch_assoc()): ?>
                    <li class="list-group-item"><?= htmlspecialchars($row['department_name']); ?></li>
                <?php endwhile; ?>
            </ul>
        </div>

        <!-- Assigned Sections -->
        <div class="mb-4">
            <h4>Assigned Sections</h4>
            <ul class="list-group">
                <?php while ($row = $sections->fetch_assoc()): ?>
                    <li class="list-group-item"><?= htmlspecialchars($row['section_name']); ?></li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
