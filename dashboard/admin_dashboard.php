<?php
error_reporting(E_ALL); 
ini_set('display_errors', 1); 

session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}


$sql = "SELECT e.id, e.name, e.date, e.max_quantity,e.duration,
                CASE
                    WHEN e.date < CURDATE() THEN 'done'
                    WHEN e.date = CURDATE() THEN 'ongoing'
                    ELSE 'upcoming'
                END AS status,
                COUNT(er.user_id) AS registrants_count
        FROM events e
        LEFT JOIN event_registrations er ON e.id = er.event_id
        LEFT JOIN users u ON er.user_id = u.id AND u.role = 'user'
        GROUP BY e.id";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        <h1>event</h1>
        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Registrants</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?> 
                        <?php while($row = $result->fetch_assoc()): ?> 
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td><?php echo htmlspecialchars($row['registrants_count']) . '/' . htmlspecialchars($row['max_quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['duration']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td>
                                    <a href="../content/view_registrants.php?event_id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">View Registrants</a>
                                    <a href="../content/export_registrants.php?event_id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Export</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No events available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleProfileMenu() {
            var profileMenu = document.getElementById("profileDropdown");
            if (profileMenu.style.display === "none" || profileMenu.style.display === "") {
                profileMenu.style.display = "block";
            } else {
                profileMenu.style.display = "none";
            }
        }

        window.onclick = function(event) {
            if (!event.target.closest('.profile-container')) {
                var profileMenu = document.getElementById("profileDropdown");
                profileMenu.style.display = "none";
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
