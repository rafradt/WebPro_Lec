<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);

    $sql = "SELECT u.username, u.email
            FROM event_registrations er
            JOIN users u ON er.user_id = u.id
            WHERE er.event_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrants List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../index.css">
</head>
<body>
<div class="sidebar">
        <div class="profile-container" onclick="toggleProfileMenu()">
            <div class="profile-circle">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            <span class="username-text"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <div class="profile-dropdown" id="profileDropdown">
                <a href="../content/profile.php">Profile</a>
            </div>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="../dashboard/admin_dashboard.php">Dashboard</a>
            </li>
            <hr>
            <li class="nav-item">
                <a href="../content/event_management.php" class="nav-link">Event</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../content/user_management.php">User Management</a>
            </li>
        </ul>
        <div class="logout-link">
            <a class="nav-link" href="../auth/logout.php">Logout</a>
        </div>
    </div>
    <div class="content">
        <h1>List of Registrants</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">No registrants for this event.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
    </div>
</body>
</html>
