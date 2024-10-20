<?php
error_reporting(E_ALL); 
ini_set('display_errors', 1); 

session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}


$sql = "SELECT u.id, u.username, u.email, 
               GROUP_CONCAT(e.name SEPARATOR ', ') AS registered_events
        FROM users u
        LEFT JOIN event_registrations er ON u.id = er.user_id
        LEFT JOIN events e ON er.event_id = e.id
        WHERE u.role = 'user'
        GROUP BY u.id";

$result = $conn->query($sql);

if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $delete_sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    header("Location: user_management.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
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
                <a href="profile.php">Profile</a>
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
                <a href="../content/user_management.php" class="nav-link">User Management</a>
            </li>
        </ul>
        <div class="logout-link">
            <a class="nav-link" href="../auth/logout.php">Logout</a>
        </div>
    </div>
    <div class="content">
        <h1>User Management</h1>
        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Registered Events</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['registered_events'] ?: 'No events'); ?></td>
                                <td>
                                    <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this registration?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No users available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
