<?php
session_start();
require '../include/connection.php';

// Check if professor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit();
}

$professor_id = $_SESSION['user_id'];

// Fetch the results for each section and add section info in the result
$resultsQuery = "SELECT r.result_id, u.name AS student_name, a.title AS assessment_title, r.score, a.assessment_time, s.name AS section_name
                 FROM Results r
                 JOIN Users u ON r.student_id = u.user_id
                 JOIN Assessments a ON r.assessment_id = a.assessment_id
                 JOIN Assessment_Sections asct ON a.assessment_id = asct.assessment_id
                 JOIN Sections s ON asct.section_id = s.section_id
                 WHERE asct.section_id IN (SELECT section_id FROM Professor_Sections WHERE professor_id = ?)
                 GROUP BY r.student_id, r.assessment_id
                 ORDER BY s.name, u.name, a.title";

$stmt2 = $conn->prepare($resultsQuery);
$stmt2->bind_param("i", $professor_id);
$stmt2->execute();
$results = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Results</title>
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
                    <li class="nav-item"><a class="nav-link" href="professor-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="professor-assessments.php">Assessments</a></li>
                    <li class="nav-item"><a class="nav-link active" href="professor-results.php">Results</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Results Content -->
    <div class="container mt-5">
        <h2>Assessment Results</h2>

        <!-- Display Results -->
        <div class="mb-4">
            <h4>Assessment Results</h4>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Section</th>
                        <th>Student Name</th>
                        <th>Assessment Title</th>
                        <th>Score</th>
                        <th>Assessment Time (minutes)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $results->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['section_name']); ?></td>
                            <td><?= htmlspecialchars($row['student_name']); ?></td>
                            <td><?= htmlspecialchars($row['assessment_title']); ?></td>
                            <td><?= htmlspecialchars($row['score']); ?></td>
                            <td><?= htmlspecialchars($row['assessment_time']); ?> minutes</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt2->close();
$conn->close();
?>
