<?php
session_start();
error_reporting(E_ALL); 
ini_set('display_errors', 1); 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}


require '../config.php';

$queryAvailable = "SELECT id, name, image, description, date, status
                   FROM events 
                   WHERE id NOT IN (SELECT event_id FROM event_registrations WHERE user_id = ?)
                   AND status NOT IN('closed', 'canceled')";
$stmtAvailable = $conn->prepare($queryAvailable);
$stmtAvailable->bind_param("i", $_SESSION['user_id']);
$stmtAvailable->execute();
$resultAvailable = $stmtAvailable->get_result();
$availableEvents = $resultAvailable->fetch_all(MYSQLI_ASSOC);
$stmtAvailable->close();

$eventsThisMonth = [];
$upcomingEvents = [];
$currentDate = strtotime(date("Y-m-d"));


$currentMonth = date("m");


foreach ($availableEvents as $event) {
    $eventDate = strtotime($event['date']);
    $eventMonth = date('m', $eventDate);
    
    if ($eventMonth == $currentMonth && $event['status'] === 'open') {
        $eventsThisMonth[] = $event;
    } else {
        $upcomingEvents[] = $event;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="../index.css">
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

    <div class="container mt-5">
        <h2>Events This Month</h2>
        <!-- Swiper -->
        <div class="swiper">
            <div class="swiper-wrapper">
                <?php if (count($eventsThisMonth) > 0): ?>
                    <?php foreach ($eventsThisMonth as $event): ?>
                        <div class="swiper-slide">
                            <div class="card">
                                <?php
                                $imagePath = "../assets/" . htmlspecialchars($event['image']);
                                echo "<img src='$imagePath' class='card-img-top' alt='" . htmlspecialchars($event['name']) . "'>";
                                ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                                    <a href="../content/event_detail.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary">Detail</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No events available this month.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <h2>Upcoming Events</h2>
        <div class="row">
            <?php if (count($upcomingEvents) > 0): ?>
                <?php foreach ($upcomingEvents as $event): ?>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <?php
                            $imagePath = "../assets/" . htmlspecialchars($event['image']);
                            echo "<img src='$imagePath' class='card-img-top' alt='" . htmlspecialchars($event['name']) . "'>";
                            ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                                <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($event['date']); ?></small></p>
                                <a href="#" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#eventDetailModal" onclick="loadEventDetail(<?php echo $event['id']; ?>)">Detail</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No upcoming events found.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventDetailModalLabel">Event Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="eventDetailContent">
                    <!-- Konten detail event akan dimuat di sini -->
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>
    <script>
        function loadEventDetail(eventId) {
            fetch('../content/event_detail.php?id=' + eventId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(data => {
                    document.getElementById('eventDetailContent').innerHTML = data;
                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation:', error);
                });
            }    
        document.getElementById('profileCircle').addEventListener('click', function () {
            var dropdown = document.getElementById('profileDropdown');
            dropdown.style.display = dropdown.style.display === 'none' || dropdown.style.display === '' ? 'block' : 'none';
        });

        document.addEventListener('click', function (event) {
            var profileCircle = document.getElementById('profileCircle');
            var dropdown = document.getElementById('profileDropdown');
            if (!profileCircle.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });

        var swiper = new Swiper('.swiper', {
            slidesPerView: 1,
            spaceBetween: 10,
            autoplay: {
                delay: 3000, 
                disableOnInteraction: false, 
            },
            loop: true, 
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
