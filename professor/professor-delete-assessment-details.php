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

// Verify that the professor owns the assessment
$sql = "SELECT * FROM Assessments WHERE assessment_id = ? AND professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $assessment_id, $professor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: professor-assessments.php");
    exit();
}

// Start deletion process
$conn->begin_transaction();

try {
    // Delete answers linked to questions
    $sql = "DELETE FROM Answers WHERE question_id IN (SELECT question_id FROM Questions WHERE assessment_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();

    // Delete questions linked to the assessment
    $sql = "DELETE FROM Questions WHERE assessment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();

    // Delete the assessment
    $sql = "DELETE FROM Assessments WHERE assessment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Redirect to assessments page
    header("Location: professor-assessments.php?message=deleted");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
