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

// Fetch assessment details
$sql = "SELECT title, type, description FROM Assessments WHERE assessment_id = ? AND professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $assessment_id, $professor_id);
$stmt->execute();
$assessment = $stmt->get_result()->fetch_assoc();

if (!$assessment) {
    header("Location: professor-assessments.php");
    exit();
}

// Fetch questions and answers in one go
$sql = "SELECT q.question_id, q.question_text, a.answer_id, a.answer_text, a.is_correct 
        FROM Questions q 
        LEFT JOIN Answers a ON q.question_id = a.question_id 
        WHERE q.assessment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[$row['question_id']]['question_text'] = $row['question_text'];
    $questions[$row['question_id']]['answers'][] = [
        'answer_id' => $row['answer_id'],
        'answer_text' => $row['answer_text'],
        'is_correct' => $row['is_correct']
    ];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];

    // Update assessment
    $sql = "UPDATE Assessments SET title = ?, description = ?, type = ? WHERE assessment_id = ? AND professor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $title, $description, $type, $assessment_id, $professor_id);
    $stmt->execute();

    // Update questions and answers
    foreach ($_POST['questions'] as $question_id => $question_text) {
        $sql = "UPDATE Questions SET question_text = ? WHERE question_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $question_text, $question_id);
        $stmt->execute();
    }
    
    foreach ($_POST['answers'] as $answer_id => $answer_text) {
        $is_correct = isset($_POST['correct'][$answer_id]) ? 1 : 0;
        $sql = "UPDATE Answers SET answer_text = ?, is_correct = ? WHERE answer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $answer_text, $is_correct, $answer_id);
        $stmt->execute();
    }

    header("Location: professor-view-assessment-details.php?id=$assessment_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assessment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Assessment</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($assessment['title']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Type</label>
                <select class="form-control" name="type" required>
                    <option value="quiz" <?= $assessment['type'] == 'quiz' ? 'selected' : ''; ?>>Quiz</option>
                    <option value="exam" <?= $assessment['type'] == 'exam' ? 'selected' : ''; ?>>Exam</option>
                    <option value="assignment" <?= $assessment['type'] == 'assignment' ? 'selected' : ''; ?>>Assignment</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($assessment['description']); ?></textarea>
            </div>
            <h4>Questions</h4>
            <?php foreach ($questions as $question_id => $question): ?>
                <div class="mb-3">
                    <label class="form-label">Question</label>
                    <input type="text" class="form-control" name="questions[<?= $question_id; ?>]" value="<?= htmlspecialchars($question['question_text']); ?>" required>
                </div>
                <h5>Answers</h5>
                <?php foreach ($question['answers'] as $answer): ?>
                    <div class="mb-2">
                        <input type="text" class="form-control d-inline-block w-75" name="answers[<?= $answer['answer_id']; ?>]" value="<?= htmlspecialchars($answer['answer_text']); ?>" required>
                        <input type="checkbox" name="correct[<?= $answer['answer_id']; ?>]" value="1" <?= $answer['is_correct'] ? 'checked' : ''; ?>> Correct
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-success mt-3">Save Changes</button>
            <a href="professor-view-assessment-details.php?id=<?= $assessment_id; ?>" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
