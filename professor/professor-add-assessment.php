<?php
session_start();
require '../include/connection.php';

// Check if professor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit();
}

$professor_id = $_SESSION['user_id'];

// Fetch assigned sections
$sec_result = $conn->query("SELECT s.section_id, s.name FROM Sections s 
    JOIN Professor_Sections ps ON s.section_id = ps.section_id
    WHERE ps.professor_id = $professor_id");
$sections = $sec_result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_assessment'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $type = $_POST['type'];
    $assessment_time = $_POST['assessment_time'];  // New field for assessment time
    $selected_sections = $_POST['sections'] ?? [];

    // Ensure selected sections belong to the professor
    $valid_sec_ids = array_column($sections, 'section_id');

    if (!empty($title) && !empty($type) && !empty($assessment_time) && !empty($selected_sections) && !array_diff($selected_sections, $valid_sec_ids)) {
        $stmt = $conn->prepare("INSERT INTO Assessments (title, description, type, professor_id, assessment_time) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $title, $description, $type, $professor_id, $assessment_time);

        if ($stmt->execute()) {
            $assessment_id = $stmt->insert_id;

            foreach ($selected_sections as $sec_id) {
                $conn->query("INSERT INTO Assessment_Sections (assessment_id, section_id) VALUES ($assessment_id, $sec_id)");
            }

            // ✅ Insert Questions and Answers
            if (!empty($_POST['questions'])) {
                foreach ($_POST['questions'] as $index => $question_text) {
                    $question_type = $_POST['question_types'][$index];

                    $stmt_question = $conn->prepare("INSERT INTO Questions (assessment_id, question_text, type) VALUES (?, ?, ?)");
                    $stmt_question->bind_param("iss", $assessment_id, $question_text, $question_type);

                    if ($stmt_question->execute()) {
                        $question_id = $stmt_question->insert_id;

                        // ✅ Insert Answers
                        if ($question_type === "true_false") {
                            $correctAnswer = $_POST['correct'][$index];

                            $answers = ["True", "False"];
                            foreach ($answers as $key => $answer_text) {
                                $is_correct = ($key == $correctAnswer) ? 1 : 0;

                                $stmt_answer = $conn->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
                                $stmt_answer->bind_param("isi", $question_id, $answer_text, $is_correct);
                                $stmt_answer->execute();
                                $stmt_answer->close();
                            }
                        } else {
                            if (!empty($_POST['answers'][$index])) {
                                foreach ($_POST['answers'][$index] as $answer_index => $answer_text) {
                                    $is_correct = isset($_POST['correct'][$index][$answer_index]) ? 1 : 0;

                                    $stmt_answer = $conn->prepare("INSERT INTO Answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
                                    $stmt_answer->bind_param("isi", $question_id, $answer_text, $is_correct);
                                    $stmt_answer->execute();
                                    $stmt_answer->close();
                                }
                            }
                        }
                    }
                    $stmt_question->close();
                }
            }

            $_SESSION['message'] = "Assessment and questions added successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding assessment.";
            $_SESSION['msg_type'] = "danger";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Invalid section selection!";
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: professor-assessments.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Assessment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2>Add New Assessment</h2>

        <?php if (isset($_SESSION['message'])) { ?>
            <div class="alert alert-<?php echo $_SESSION['msg_type']; ?>">
                <?php echo $_SESSION['message'];
                unset($_SESSION['message']); ?>
            </div>
        <?php } ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label>Title:</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description:</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label>Assessment Time (in minutes):</label>
                <input type="number" name="assessment_time" class="form-control" required min="1">
            </div>

            <div class="mb-3">
                <label>Type:</label>
                <select name="type" class="form-control">
                    <option value="exam">Exam</option>
                    <option value="quiz">Quiz</option>
                    <option value="assignment">Assignment</option>
                </select>
            </div>



            <!-- Multi-select for Assigned Sections -->
            <div class="mb-3">
                <label>Sections:</label>
                <select name="sections[]" class="form-select" multiple required>
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?= $sec['section_id']; ?>"><?= $sec['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="questions-container"></div>
            <button type="button" class="btn btn-secondary" onclick="addQuestion()">Add Question</button>
            <button type="submit" name="add_assessment" class="btn btn-primary w-100 mt-3">Save Assessment</button>
        </form>
    </div>
    <script>
        function addQuestion() {
            const questionIndex = document.querySelectorAll('.question-item').length;
            const questionHTML = `
                <div class="question-item mb-3 p-3 bg-light rounded-3 border position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="removeQuestion(this)"></button>
                    <input type="text" name="questions[]" class="form-control mb-2" placeholder="Question" required>
                    <select name="question_types[]" class="form-control mb-2" onchange="updateAnswers(this, ${questionIndex})" id="question_type_${questionIndex}">
                        <option value="mcq">Multiple Choice</option>
                        <option value="short_answer">Short Answer</option>
                    </select>
                    <div class="answers-container" id="answers-container-${questionIndex}"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2 add-answer-btn" data-index="${questionIndex}">
                        + Add Answer
                    </button>
                </div>
            `;
            document.getElementById('questions-container').insertAdjacentHTML('beforeend', questionHTML);
        }

        document.addEventListener("click", function (event) {
            if (event.target.classList.contains("add-answer-btn")) {
                const questionIndex = event.target.getAttribute("data-index");
                const answerContainer = document.getElementById(`answers-container-${questionIndex}`);
                let answerHTML = `
                    <div class="answer-item p-2 bg-warning-subtle rounded-2 border mt-2">
                        <input type="text" name="answers[${questionIndex}][]" class="form-control d-inline w-75" required>
                        <label class="ms-2">
                            <input type="radio" name="correct[${questionIndex}]" value="${answerContainer.children.length}" required> Correct
                        </label>
                        <button type="button" class="btn btn-danger btn-sm ms-2 remove-answer">❌</button>
                    </div>
                `;
                answerContainer.insertAdjacentHTML('beforeend', answerHTML);
            }
            if (event.target.classList.contains("remove-answer")) {
                event.target.parentElement.remove();
            }
        });


        // Updates answer fields based on selected question type
        function updateAnswers(selectElement, questionIndex) {
            const answerContainer = document.getElementById(`answers-container-${questionIndex}`);
            answerContainer.innerHTML = ""; // Clear previous answers

            if (selectElement.value === "true_false") {
                answerContainer.innerHTML = `
        <div class="answer-item p-2 bg-warning-subtle rounded-2 border mt-2">
            <input type="text" value="True" class="form-control d-inline w-75" readonly>
            <label class="ms-2">
                <input type="radio" name="correct[${questionIndex}]" value="0" required> Correct
            </label>
        </div>
        <div class="answer-item p-2 bg-warning-subtle rounded-2 border mt-2">
            <input type="text" value="False" class="form-control d-inline w-75" readonly>
            <label class="ms-2">
                <input type="radio" name="correct[${questionIndex}]" value="1" required> Correct
            </label>
        </div>
    `;
            }
            else {
                // Show "Add Answer" button for MCQ & Short Answer
                answerContainer.innerHTML = "";
            }
        }




        // Adds a new answer (only for MCQ type)
        function addAnswer(button, questionIndex) {
            const answerContainer = button.previousElementSibling;
            const questionType = document.querySelector(`select[name="question_types[]"]:nth-of-type(${questionIndex + 1})`).value;

            let answerHTML = `
        <div class="answer-item p-2 bg-warning-subtle rounded-2 border mt-2">
            <input type="text" name="answers[${questionIndex}][]" class="form-control d-inline w-75" required>
            <label class="ms-2">
                <input type="${questionType === 'mcq' ? 'radio' : 'checkbox'}" name="correct[${questionIndex}]" value="1" required> Correct
            </label>
            <button type="button" class="btn btn-danger btn-sm ms-2" onclick="removeAnswer(this)">❌</button>
        </div>
    `;

            answerContainer.insertAdjacentHTML('beforeend', answerHTML);
        }


        // Removes an answer field
        function removeAnswer(button) {
            button.parentElement.remove();
        }

        // Removes a question
        function removeQuestion(button) {
            button.parentElement.remove();
        }

    </script>

</body>

</html>
<?php $conn->close(); ?>