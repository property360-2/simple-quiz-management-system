<?php
session_start();
require '../include/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid assessment ID.");
}

$assessment_id = intval($_GET['id']);

// Fetch assessment details, including time limit
$sql = "SELECT title, description, type, assessment_time FROM Assessments WHERE assessment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Assessment not found.");
}

$assessment = $result->fetch_assoc();
$stmt->close();

// Check if the student has already submitted the assessment
$check_sql = "SELECT COUNT(*) AS taken FROM Submissions WHERE student_id = ? AND assessment_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $student_id, $assessment_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result()->fetch_assoc();

if ($check_result['taken'] > 0) {
    die("You have already taken this assessment.");
}

// Fetch questions
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
    <title>Take Assessment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; }
        .card { border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .btn-primary { background-color: #007bff; border: none; }
        .btn-primary:hover { background-color: #0056b3; }
        .form-check-input:checked { background-color: #007bff; border-color: #007bff; }
        #timer { font-size: 1.5em; font-weight: bold; color: red; text-align: center; }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card p-4">
            <h2 class="text-center"><?= htmlspecialchars($assessment['title']); ?></h2>
            <p class="text-muted text-center"><?= htmlspecialchars($assessment['description']); ?></p>
            <p class="text-center" id="timer"></p>

            <form action="submit-assessment.php" method="POST">
                <input type="hidden" name="assessment_id" value="<?= $assessment_id; ?>">

                <?php while ($question = $questions->fetch_assoc()): ?>
                    <div class="mb-4">
                        <h5><?= htmlspecialchars($question['question_text']); ?></h5>

                        <?php if ($question['type'] == 'mcq'): ?>
                            <?php
                            $sql = "SELECT answer_id, answer_text FROM Answers WHERE question_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $question['question_id']);
                            $stmt->execute();
                            $answers = $stmt->get_result();
                            ?>
                            <?php while ($answer = $answers->fetch_assoc()): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer[<?= $question['question_id']; ?>]" 
                                           value="<?= $answer['answer_id']; ?>" required>
                                    <label class="form-check-label"><?= htmlspecialchars($answer['answer_text']); ?></label>
                                </div>
                            <?php endwhile; ?>
                            <?php $stmt->close(); ?>

                        <?php elseif ($question['type'] == 'short_answer'): ?>
                            <input type="text" class="form-control" name="answer[<?= $question['question_id']; ?>]" required>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>

                <button type="submit" class="btn btn-primary w-100">Submit Assessment</button>
            </form>
        </div>
    </div>

    <script>
        var assessmentTime = <?= $assessment['assessment_time']; ?>;
        var timeInSeconds = assessmentTime * 60;

        function formatTime(seconds) {
            var minutes = Math.floor(seconds / 60);
            var seconds = seconds % 60;
            return minutes + ":" + (seconds < 10 ? "0" + seconds : seconds);
        }

        function startTimer() {
            var timerInterval = setInterval(function () {
                timeInSeconds--;
                document.getElementById("timer").innerHTML = "Time Left: " + formatTime(timeInSeconds);

                if (timeInSeconds <= 0) {
                    clearInterval(timerInterval);
                    document.getElementById("timer").innerHTML = "Time's Up!";
                    document.forms[0].submit();
                }
            }, 1000);
        }

        startTimer();
    </script>

</body>
</html>

<?php
$conn->close();
?>
