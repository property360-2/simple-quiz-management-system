<?php
session_start();
require '../include/connection.php';

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch assessments available for the student's department or section
$sql = "SELECT a.assessment_id, a.title, a.type, a.description, u.name AS professor_name 
        FROM Assessments a
        JOIN Users u ON a.professor_id = u.user_id
        WHERE a.department_id IN (SELECT department_id FROM Users WHERE user_id = ?) 
        OR a.assessment_id IN (SELECT assessment_id FROM Results WHERE student_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$assessments = $stmt->get_result();

// Fetch student's results
$sql = "SELECT r.result_id, a.title, r.score FROM Results r
        JOIN Assessments a ON r.assessment_id = a.assessment_id
        WHERE r.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$results = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="student-dashboard.php">Student Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="student-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="student-assessments.php">Assessments</a></li>
                    <li class="nav-item"><a class="nav-link" href="student-Results.php">Results</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Welcome Message -->
    <div class="container mt-4">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['name']); ?>!</h2>
        <p>Here are your available assessments and results.</p>

        <!-- Display Department and Section Names -->
        <div class="alert alert-info">
            <strong>Department:</strong> <?= htmlspecialchars($_SESSION['department_name']); ?> <br>
            <strong>Section:</strong> <?= htmlspecialchars($_SESSION['section_name']); ?>
        </div>
    </div>
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $conn->close(); ?>