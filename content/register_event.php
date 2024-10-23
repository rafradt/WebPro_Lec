<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT e.id, e.name, e.image, e.description,e.date,e.lokasi 
          FROM events e 
          JOIN event_registrations r ON e.id = r.event_id 
          WHERE r.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$registeredEvents = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (isset($_GET['event_id'])) {
    $user_id = $_SESSION['user_id'];
    $event_id = $_GET['event_id'];

    $checkQuery = "SELECT * FROM event_registrations WHERE user_id = ? AND event_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "You are already registered for this event.";
    } else {
        $insertQuery = "INSERT INTO event_registrations (user_id, event_id) VALUES (?, ?)";
        $stmtInsert = $conn->prepare($insertQuery);
        $stmtInsert->bind_param("ii", $user_id, $event_id);
        if ($stmtInsert->execute()) {
            header('Location: ../dashboard/user_dashboard.php?message=Registration successful');
        } else {
            echo "Error: " . $stmtInsert->error;
        }
        $stmtInsert->close();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../index.css">
    <style>
        .card img {
            height: 200px;
            object-fit: cover;
        }
        .no-events {
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard/user_dashboard.php">EventNest</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a href="../content/register_event.php" class="nav-link">Registered Event </a>
                    </li>
                    <li class="nav-item position-relative">
                        <div class="nav-link d-flex align-items-center" id="profileCircle" style="cursor: pointer;">
                            <div class="profile-circle">
                                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                            </div>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end position-absolute" id="profileDropdown" style="display: none; top: 100%; right: 0;">
                            <li>
                                <a class="dropdown-item" href="../content/profile.php">Profile</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="../auth/logout.php">Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="containermt-5">

        <h2 class="mb-4">Registered Events</h2> 
        <div class="row">
            <?php if (count($registeredEvents) > 0): ?>
                <?php foreach ($registeredEvents as $event): ?>
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <?php
                        $imagePath = "../assets/" . htmlspecialchars($event['image']);
                        echo "<img src='$imagePath' class='card-img-top' alt='" . htmlspecialchars($event['name']) . "'>";
                        ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                            <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($event['date']); ?></small></p>
                            <p class="card-text"><small class="text-muted">Location: <?php echo htmlspecialchars($event['lokasi']); ?></small></p>
                            <a href="../content/cancel_registration.php?event_id=<?php echo $event['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this registration?');">Cancel Registration</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-events">You haven't registered for any events yet.</p>
                    <?php endif; ?>
                </div>
    </div>
</body>
</html>

