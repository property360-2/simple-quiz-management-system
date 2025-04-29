<?php
session_start();
require '../include/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch student results along with the total number of questions in each assessment
$sql = "SELECT r.result_id, a.title AS assessment_title, r.score, 
               (SELECT COUNT(*) FROM Questions WHERE assessment_id = r.assessment_id) AS total_questions
        FROM Results r
        JOIN Assessments a ON r.assessment_id = a.assessment_id
        WHERE r.student_id = ?
        ORDER BY r.result_id DESC";

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
    <title>Student Results</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .score {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .high-score {
            background-color: #28a745;
            color: white;
        }
        .low-score {
            background-color: #dc3545;
            color: white;
        }
    </style>
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
                    <li class="nav-item"><a class="nav-link" href="student-assessments.php">Assessments</a></li>
                    <li class="nav-item"><a class="nav-link active" href="student-results.php">Results</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Results Section -->
    <div class="container mt-5">
        <div class="card p-4">
            <h2 class="text-center">Your Results</h2>
            <p class="text-muted text-center">Here are your latest assessment scores.</p>

            <table class="table table-bordered mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Assessment</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $results->fetch_assoc()): 
                        $total_questions = $row['total_questions'] ?: 1; // Avoid division by zero
                        $fraction_score = "{$row['score']}/{$total_questions}";
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['assessment_title']); ?></td>
                            <td>
                                <span class="score <?= ($row['score'] / $total_questions) >= 0.5 ? 'high-score' : 'low-score'; ?>">
                                    <?= $fraction_score; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <?php if ($results->num_rows == 0): ?>
                <p class="text-center text-muted">No results available yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>
