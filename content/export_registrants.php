<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);

    $event_query = $conn->prepare("SELECT name FROM events WHERE id = ?");
    $event_query->bind_param("i", $event_id);
    $event_query->execute();
    $event_result = $event_query->get_result();

    if ($event_result->num_rows > 0) {
        $event = $event_result->fetch_assoc();
        $event_name = $event['name'];
        $event_name = preg_replace('/[^a-zA-Z0-9-_]/', '_', $event_name); 
    } else {
        die("Event not found.");
    }

    $sql = "SELECT u.username, u.email
            FROM event_registrations er
            JOIN users u ON er.user_id = u.id
            WHERE er.event_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $event_name . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Username', 'Email']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['username'], $row['email']]);
    }

    fclose($output);
    exit;
} else {
    die("Event ID not specified.");
}
?>
