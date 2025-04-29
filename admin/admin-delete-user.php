<?php
session_start();
require '../include/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if user ID is set
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Prevent deleting admin users
    $checkRole = $conn->prepare("SELECT role FROM Users WHERE user_id = ?");
    $checkRole->bind_param("i", $user_id);
    $checkRole->execute();
    $result = $checkRole->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['role'] !== 'admin') {
        // Delete user
        $stmt = $conn->prepare("DELETE FROM Users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            echo "<script>alert('User deleted successfully!'); window.location.href='admin-manage-users.php';</script>";
        } else {
            echo "<script>alert('Failed to delete user.'); window.location.href='admin-manage-users.php';</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Cannot delete an admin user!'); window.location.href='admin-manage-users.php';</script>";
    }

    $checkRole->close();
}

$conn->close();
?>
