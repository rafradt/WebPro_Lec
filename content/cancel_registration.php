<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

if (isset($_GET['event_id'])) {
    $user_id = $_SESSION['user_id'];
    $event_id = $_GET['event_id'];

    $deleteQuery = "DELETE FROM event_registrations WHERE user_id = ? AND event_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("ii", $user_id, $event_id);

    if ($stmt->execute()) {
        // Cek keberhasilan eksekusi
        header('Location: ../dashboard/user_dashboard.php?message=Cancellation successful');
    } else {
        echo "Error: " . $stmt->error; // Tampilkan error jika ada
    }    

    $stmt->close();
} else {
    echo "No event specified for cancellation.";
}
?>
