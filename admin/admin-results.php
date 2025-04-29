<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Include database connection
require '../include/connection.php';

// Query to fetch data for analytics
// Total Assessments
$totalAssessmentsQuery = "SELECT COUNT(*) AS total_assessments FROM Assessments";
$totalAssessmentsResult = $conn->query($totalAssessmentsQuery);
$totalAssessments = $totalAssessmentsResult->fetch_assoc()['total_assessments'];

// Total Students
$totalStudentsQuery = "SELECT COUNT(*) AS total_students FROM Users WHERE role = 'student'";
$totalStudentsResult = $conn->query($totalStudentsQuery);
$totalStudents = $totalStudentsResult->fetch_assoc()['total_students'];

// Total Professors
$totalProfessorsQuery = "SELECT COUNT(*) AS total_professors FROM Users WHERE role = 'professor'";
$totalProfessorsResult = $conn->query($totalProfessorsQuery);
$totalProfessors = $totalProfessorsResult->fetch_assoc()['total_professors'];

// Total Sections
$totalSectionsQuery = "SELECT COUNT(*) AS total_sections FROM Sections";
$totalSectionsResult = $conn->query($totalSectionsQuery);
$totalSections = $totalSectionsResult->fetch_assoc()['total_sections'];

// Total Results
$totalResultsQuery = "SELECT COUNT(*) AS total_results FROM Results";
$totalResultsResult = $conn->query($totalResultsQuery);
$totalResults = $totalResultsResult->fetch_assoc()['total_results'];

// Average Scores
$averageScoresQuery = "SELECT AVG(score) AS average_score FROM Results";
$averageScoresResult = $conn->query($averageScoresQuery);
$averageScore = round($averageScoresResult->fetch_assoc()['average_score'], 2);

// Highest Score
$highestScoreQuery = "SELECT MAX(score) AS highest_score FROM Results";
$highestScoreResult = $conn->query($highestScoreQuery);
$highestScore = $highestScoreResult->fetch_assoc()['highest_score'];

// Lowest Score
$lowestScoreQuery = "SELECT MIN(score) AS lowest_score FROM Results";
$lowestScoreResult = $conn->query($lowestScoreQuery);
$lowestScore = $lowestScoreResult->fetch_assoc()['lowest_score'];

// Assessments per Department
$assessmentsPerDepartmentQuery = "SELECT d.name AS department_name, COUNT(a.assessment_id) AS assessments_count
                                  FROM Assessments a
                                  LEFT JOIN Departments d ON a.department_id = d.department_id
                                  GROUP BY d.department_id";
$assessmentsPerDepartmentResult = $conn->query($assessmentsPerDepartmentQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Results Overview</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="admin-dashboard.php">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a
                            class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : ''; ?>"
                            href="admin-dashboard.php">Home</a></li>
                    <li class="nav-item"><a
                            class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin-manage-users.php' ? 'active' : ''; ?>"
                            href="admin-manage-users.php">View Users</a></li>
                    <li class="nav-item"><a
                            class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'departments.php' ? 'active' : ''; ?>"
                            href="departments.php">Departments</a></li>
                    <li class="nav-item"><a
                            class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'sections.php' ? 'active' : ''; ?>"
                            href="sections.php">Sections</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin-results.php">Results</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Results Content -->
    <div class="container mt-5">
        <h2>Overall System Report</h2>
        <p>Below is the detailed report for analytics and KPIs across various system parameters.</p>

        <!-- Dashboard Stats -->
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Total Assessments</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $totalAssessments; ?></h5>
                        <p class="card-text">Total assessments created across all departments.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Total Students</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $totalStudents; ?></h5>
                        <p class="card-text">Total number of students enrolled in the system.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">Total Professors</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $totalProfessors; ?></h5>
                        <p class="card-text">Total number of professors registered in the system.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Total Sections</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $totalSections; ?></h5>
                        <p class="card-text">Total number of sections available in the system.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- More KPIs -->
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-dark mb-3">
                    <div class="card-header">Total Results</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $totalResults; ?></h5>
                        <p class="card-text">Total results recorded for all assessments.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-header">Average Score</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $averageScore; ?>%</h5>
                        <p class="card-text">The average score across all assessments.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Highest Score</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $highestScore; ?>%</h5>
                        <p class="card-text">The highest score achieved by any student.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-header">Lowest Score</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $lowestScore; ?>%</h5>
                        <p class="card-text">The lowest score achieved by any student.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assessments per Department -->
        <!-- <h4>Assessments per Department</h4>

        todo: ayaw gumana ng department name: sa pag fetch lang ng data ata yan baka sa $sql
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Department</th>
                    <th>Number of Assessments</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $assessmentsPerDepartmentResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['department_name']); ?></td>
                        <td><?= htmlspecialchars($row['assessments_count']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table> -->

    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php
// Close connection
$conn->close();
?>