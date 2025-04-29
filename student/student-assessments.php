<?php
session_start();
require '../include/connection.php';

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$department_name = $_SESSION['department_name'];
$section_name = $_SESSION['section_name'];

$sql = "SELECT a.assessment_id, a.title, a.type, a.description, a.assessment_time, 
               u.name AS professor_name, 
               (SELECT COUNT(*) FROM Submissions s WHERE s.student_id = ? AND s.assessment_id = a.assessment_id) AS taken
        FROM Assessments a
        JOIN Users u ON a.professor_id = u.user_id
        LEFT JOIN Assessment_Departments ad ON a.assessment_id = ad.assessment_id
        LEFT JOIN Assessment_Sections asct ON a.assessment_id = asct.assessment_id
        WHERE ad.department_id = (SELECT department_id FROM Users WHERE user_id = ?)
        OR asct.section_id = (SELECT section_id FROM Users WHERE user_id = ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $student_id, $student_id, $student_id);
$stmt->execute();
$assessments = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Assessments</title>
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
                    <li class="nav-item"><a class="nav-link" href="student-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="student-assessments.php">Assessments</a></li>
                    <li class="nav-item"><a class="nav-link" href="student-results.php">Results</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container mt-4">
        <h2>Available Assessments</h2>
        <p>Here are the assessments available for your department (<?= htmlspecialchars($department_name); ?>) and section (<?= htmlspecialchars($section_name); ?>).</p>
        
        <table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Description</th>
            <th>Professor</th>
            <th>Time</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $assessments->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']); ?></td>
                <td><?= ucfirst($row['type']); ?></td>
                <td><?= nl2br(htmlspecialchars($row['description'])); ?></td>
                <td><?= htmlspecialchars($row['professor_name']); ?></td>
                <td><?= $row['assessment_time']; ?> minutes</td>
                <td>
                    <?php if ($row['taken'] > 0): ?>
                        <button class="btn btn-sm btn-secondary" disabled>Already Taken</button>
                    <?php else: ?>
                        <a href="student-take-assessment.php?id=<?= $row['assessment_id']; ?>" class="btn btn-sm btn-primary">Take Assessment</a>
                    <?php endif; ?>
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
