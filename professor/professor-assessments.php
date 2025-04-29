<?php
session_start();
require '../include/connection.php';

// Check if professor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit();
}

$professor_id = $_SESSION['user_id'];

$sql = "SELECT a.assessment_id, a.title, a.type, a.description, a.assessment_time, d.name AS department_name, d.department_id
        FROM Assessments a
        LEFT JOIN Departments d ON a.department_id = d.department_id
        WHERE a.professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$assessments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Assessments</title>
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
                    <li class="nav-item"><a class="nav-link" href="professor-results.php">Student Results</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Assessments Table -->
    <div class="container mt-5">
        <h2>Your Assessments</h2>
        <a href="professor-add-assessment.php" class="btn btn-primary mb-3">+ Create New Assessment</a>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Time (Minutes)</th> <!-- New column for assessment time -->
                    <th>Sections</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $assessments->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']); ?></td>
                        <td><?= ucfirst($row['type']); ?></td>
                        <td><?= htmlspecialchars($row['description']); ?></td>
                        <td><?= htmlspecialchars($row['assessment_time']); ?> minutes</td> <!-- Display assessment time -->
                        <td>
                            <?php
                            // Fetch sections for this assessment
                            $sql = "SELECT s.name FROM Sections s 
                                    JOIN Assessment_Sections asx ON s.section_id = asx.section_id 
                                    WHERE asx.assessment_id = ?";
                            $stmtSec = $conn->prepare($sql);
                            $stmtSec->bind_param("i", $row['assessment_id']);
                            $stmtSec->execute();
                            $sections = $stmtSec->get_result();
                            $sectionNames = [];
                            while ($sec = $sections->fetch_assoc()) {
                                $sectionNames[] = htmlspecialchars($sec['name']);
                            }
                            echo implode(", ", $sectionNames);
                            ?>
                        </td>
                        <td>
                            <a href="professor-view-assessment-details.php?id=<?= $row['assessment_id']; ?>" class="btn btn-sm btn-primary">View</a>
                            <a href="professor-edit-assessment.php?id=<?= $row['assessment_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="professor-delete-assessment-details.php?id=<?= $row['assessment_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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
