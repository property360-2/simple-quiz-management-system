<?php
session_start();
require '../include/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}


$student_id = $_SESSION['user_id'];
$assessment_id = $_POST['assessment_id'];

// Insert into Submissions Table (Records that student took the assessment)
$sql = "INSERT INTO Submissions (student_id, assessment_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $assessment_id);
$stmt->execute();
$submission_id = $stmt->insert_id;
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_id'])) {
    $assessment_id = intval($_POST['assessment_id']);
    $score = 0;

    // ✅ Get all correct answers
    $sql = "SELECT q.question_id, q.type, a.answer_text AS correct_answer, a.answer_id, a.is_correct 
            FROM Questions q 
            LEFT JOIN Answers a ON q.question_id = a.question_id 
            WHERE q.assessment_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // ✅ Store correct answers in an array
    $correct_answers = [];
    while ($row = $result->fetch_assoc()) {
        $question_id = $row['question_id'];

        if (!isset($correct_answers[$question_id])) {
            $correct_answers[$question_id] = [
                'type' => $row['type'],
                'correct_answer' => $row['correct_answer'],  // For short answers
                'correct_options' => [] // For MCQ/True-False
            ];
        }

        if ($row['is_correct'] == 1) {
            $correct_answers[$question_id]['correct_options'][] = $row['answer_id'];
        }
    }
    $stmt->close();

    // ✅ Compute Score
    foreach ($_POST['answer'] as $question_id => $answer) {
        if (!isset($correct_answers[$question_id])) continue;

        $question_type = $correct_answers[$question_id]['type'];

        // ✅ FIXED True/False Checking
        if ($question_type === 'true_false') {
            // Convert values properly
            $correct_value = strtolower(trim($correct_answers[$question_id]['correct_answer'])); 
            $user_value = strtolower(trim($answer));
            
            // ✅ Normalize values (convert "1"/"0" to "true"/"false" for comparison)
            if ($correct_value === "1") $correct_value = "true";
            if ($correct_value === "0") $correct_value = "false";
            if ($user_value === "1") $user_value = "true";
            if ($user_value === "0") $user_value = "false";
            
            // ✅ Compare normalized values
            if ($user_value === $correct_value) {  
                $score++;
            }
            
        } 
        // ✅ MCQ Checking
        else if ($question_type === 'mcq') {
            if (in_array($answer, $correct_answers[$question_id]['correct_options'])) {
                $score++;
            }
        } 
        // ✅ Short Answer Checking
        else if ($question_type === 'short_answer') {
            $correct_answer = isset($correct_answers[$question_id]['correct_answer']) ? trim(strtolower($correct_answers[$question_id]['correct_answer'])) : '';
            $student_answer = trim(strtolower($answer));

            if ($student_answer === $correct_answer) {
                $score++;
            }
        }
    }

    // ✅ Store result in the database
    $sql = "INSERT INTO Results (student_id, assessment_id, score) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $student_id, $assessment_id, $score);
    $stmt->execute();
    $stmt->close();

    // ✅ Redirect to results page
    header("Location: student-results.php");
    exit();
} else {
    die("Invalid request.");
}
?>
