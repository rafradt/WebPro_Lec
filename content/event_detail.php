<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

require '../config.php'; // Database connection

// Fetch event details based on the event ID
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Prepare the query to get event details
    $query = "SELECT id, name, image, description, date, lokasi FROM events WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
    } else {
        echo "Event not found.";
        exit;
    }
    $stmt->close();
} else {
    echo "Invalid event ID.";
    exit;
}

// Check if the user is registered for this event
$user_id = $_SESSION['user_id'];
$registered = false;

$checkQuery = "SELECT * FROM event_registrations WHERE user_id = ? AND event_id = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("ii", $user_id, $event_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $registered = true; // User is registered
}
$checkStmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['name']); ?> - Event Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .wrapper {
            max-width: 800px; /* Mengatur lebar maksimum */
            margin: 20px auto; /* Mengatur posisi tengah */
            background: white;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px; /* Menambahkan padding untuk estetika */
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">EventNest</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-white" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="wrapper">
            <h2><?php echo htmlspecialchars($event['name']); ?></h2>
            <img src="../assets/<?php echo htmlspecialchars($event['image']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($event['name']); ?>">
            <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description']); ?></p>
            <p><strong>Schedule:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($event['lokasi']); ?></p>
            
            <?php if ($registered): ?>
                <p class="alert alert-success">You are already registered for this event.</p>
                <?php else: ?>
                    <a href="../content/register_event.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary mt-3">Register for Event</a>
                    <?php endif; ?>
                    <a href="../dashboard/user_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
