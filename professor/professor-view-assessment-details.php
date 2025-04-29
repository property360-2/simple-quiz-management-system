<?php
session_start();
require '../include/connection.php';

// Check if professor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit();
}

$professor_id = $_SESSION['user_id'];

// Check if an assessment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: professor-assessments.php");
    exit();
}

$assessment_id = $_GET['id'];

// Fetch assessment details including the assessment_time
$sql = "SELECT title, type, description, assessment_time FROM Assessments WHERE assessment_id = ? AND professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $assessment_id, $professor_id);
$stmt->execute();
$assessment = $stmt->get_result()->fetch_assoc();

// Redirect if the assessment doesn't exist or doesn't belong to the professor
if (!$assessment) {
    header("Location: professor-assessments.php");
    exit();
}

// Fetch questions related to the assessment
$sql = "SELECT question_id, question_text, type FROM Questions WHERE assessment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$questions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Details</title>
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
                    <li class="nav-item"><a class="nav-link active" href="professor-assessments.php">Assessments</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Assessment Details -->
    <div class="container mt-5">
        <h2><?= htmlspecialchars($assessment['title']); ?></h2>
        <p><strong>Type:</strong> <?= ucfirst($assessment['type']); ?></p>
        <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($assessment['description'])); ?></p>
        <p><strong>Duration:</strong> <?= htmlspecialchars($assessment['assessment_time']); ?> minutes</p> <!-- Display assessment time -->

        <h4>Questions</h4>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Question</th>
                    <th>Type</th>
                    <th>Answers</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($question = $questions->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($question['question_text']); ?></td>
                        <td><?= ucfirst($question['type']); ?></td>
                        <td>
                            <?php
                            // Fetch answers for the question
                            $sql = "SELECT answer_text, is_correct FROM Answers WHERE question_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $question['question_id']);
                            $stmt->execute();
                            $answers = $stmt->get_result();

                            while ($answer = $answers->fetch_assoc()) {
                                echo htmlspecialchars($answer['answer_text']) . ($answer['is_correct'] ? " <strong>(Correct)</strong>" : "") . "<br>";
                            }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="professor-assessments.php" class="btn btn-secondary">Back to Assessments</a>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
