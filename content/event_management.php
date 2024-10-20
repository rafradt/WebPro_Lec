<?php
    session_start();
    require '../config.php';
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    if ($_SESSION['role'] != 'admin') {
        header("Location: index.php");
        exit;
    }

    $limit = 10; // Jumlah event per halaman
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    $search = isset($_GET['search']) ? $_GET['search'] : '';

    $sql = "SELECT * FROM events WHERE name LIKE ? LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $searchParam = '%' . $search . '%';
    $stmt->bind_param("sii", $searchParam, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $countSql = "SELECT COUNT(*) as total FROM events WHERE name LIKE ?";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("s", $searchParam);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalEvents = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalEvents / $limit);

    if(isset($_POST['add_event'])){
        $name = $_POST['name'];
        $date = $_POST['date'];
        $description = $_POST['description'];
        $duration = $_POST['duration'];
        $location =$_POST['location'];
        $status = isset($_POST['status']) ? $_POST['status'] : 'open'; // Default ke "open" jika tidak terisi
        $max_quantity = $_POST['max_quantity'];
        $created_at = date('Y-m-d H:i:s');
        
        
        $target_dir = "../uploads/";
        $image_file = $target_dir . basename($_FILES["image"]["name"]);
    
        if (!empty($_FILES["image"]["tmp_name"]) && move_uploaded_file($_FILES["image"]["tmp_name"], $image_file)) {
            $sql = "INSERT INTO events (name, date, description, created_at, status, max_quantity, image, duration, lokasi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssisss", $name, $date, $description, $created_at, $status, $max_quantity, $image_file, $duration, $location);
            $stmt->execute();
            header("Location: event_management.php");
        } else {
            echo "Gagal mengunggah gambar. Pastikan Anda memilih file yang benar.";
        }
    }

    if (isset($_POST['edit_event'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $date = $_POST['date'];
        $description = $_POST['description'];
        $duration = $_POST['duration'];
        $location =$_POST['location'];
        $status = $_POST['status'];
        $max_quantity = $_POST['max_quantity'];
        
        
        $sql = "UPDATE events SET name=?, date=?, description=?, duration=?, status=?, max_quantity=?, lokasi=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $name, $date, $description, $duration, $status, $max_quantity, $location, $id);
        $stmt->execute();
        header("Location: event_management.php");
        exit;
    }

    if (isset($_POST['delete_event'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM events WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: event_management.php");
        exit;
    }
    
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
        <h2>Event Management</h2>
        <button class="btn btn-primary" id="openModalButton">New</button>
        <!-- tambah event -->
        <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="AddEventModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addEventModalLabel">Tambah Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="event_management.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <input type="text" name="name" placeholder="Nama Event" class="form-contol" required>
                            </div>
                            <div class="mb-3">
                                <input type="date" name="date" id="" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <textarea name="description" placeholder="Deskription" class="form-control" required></textarea>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="duration" placeholder="duration" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="location" placeholder="Location" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <input type="number" name="max_quantity" placeholder="Quantity" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <input type="file" name="image" class="form-control" required>
                            </div>
                            <button type="submit" name="add_event" class="btn btn-success">add event</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Event -->
        <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="event_management.php" method="post">
                            <input type="hidden" name="id" id="editEventId">
                            <div class="mb-3">
                                <input type="text" name="name" id="editName" placeholder="Nama Event" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <input type="date" name="date" id="editDate" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <textarea name="description" id="editDescription" placeholder="Deskripsi" class="form-control" required></textarea>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="duration" id="editDuration" placeholder="Durasi" class="form-control">
                            </div>
                            <div class="mb-3">
                            <input type="text" name="location" id="editLocation" placeholder="location" class="form-control">
                            </div>
                            <div class="mb-3">
                                <select name="status" id="editStatus" class="form-control" required>
                                    <option value="open">Open</option>
                                    <option value="closed">Closed</option>
                                    <option value="canceled">Canceled</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <input type="number" name="max_quantity" id="editMaxQuantity" placeholder="Jumlah Maksimum" class="form-control" required>
                            </div>
                            <button type="submit" name="edit_event" class="btn btn-primary">Update Event</button>
                            <button type="submit" name="delete_event" class="btn btn-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
            $sql = "SELECT * FROM events";
            $result = $conn->query($sql);
            
            if (!$result) {
                die("Query gagal: " . $conn->error);
            }
        ?>
        <h2>Daftar Event</h2>
        <form action="event_management.php" method="get" class="mb-3">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Event" class="form-control" style="width: 300px; display: inline-block;">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Event</th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Duration</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <th>Quantity</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-id="<?= $row['id']; ?>">
                        <td class="event-name"><?= htmlspecialchars($row['name']); ?></td>
                        <td class="event-date"><?= htmlspecialchars($row['date']); ?></td>
                        <td class="event-description"><?= htmlspecialchars($row['description']); ?></td>
                        <td class="event-duration"><?= htmlspecialchars($row['duration']); ?></td>
                        <td class="event-location"><?= htmlspecialchars($row['lokasi']); ?></td> <!-- Remove or update this line -->
                        <td class="event-status"><?= htmlspecialchars($row['status']); ?></td>
                        <td class="event-max-quantity"><?= htmlspecialchars($row['max_quantity']); ?></td>
                        <td>
                            <button type="button" class="btn btn-warning" onclick="editEvent(<?= $row['id']; ?>)">Edit</button>
                            <form action="event_management.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table> 
        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="event_management.php?page=<?= $i; ?>&search=<?= urlencode($search); ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
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

        document.getElementById("openModalButton").addEventListener("click", function() {
            var myModal = new bootstrap.Modal(document.getElementById("addEventModal"));
            myModal.show();
        });

        function editEvent(id) {
        var row = document.querySelector(`[data-id="${id}"]`);
        var name = row.querySelector('.event-name').innerText;
        var date = row.querySelector('.event-date').innerText;
        var description = row.querySelector('.event-description').innerText;
        var duration = row.querySelector('.event-duration').innerText;
        var location = row.querySelector('.event-location').innerText
        var status = row.querySelector('.event-status').innerText;
        var maxQuantity = row.querySelector('.event-max-quantity').innerText;

        document.getElementById('editEventId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editDate').value = date;
        document.getElementById('editDescription').value = description;
        document.getElementById('editDuration').value = duration;
        document.getElementById('editLocation').value = location;
        document.getElementById('editStatus').value = status.toLowerCase();
        document.getElementById('editMaxQuantity').value = maxQuantity;

        var editModal = new bootstrap.Modal(document.getElementById('editEventModal'));
            editModal.show();
        }

        function confirmDelete(id) {
            if (confirm("Apakah Anda yakin ingin menghapus event ini?")) {
                var form = document.createElement("form");
                form.method = "POST";
                form.action = "event_management.php";

                var inputId = document.createElement("input");
                inputId.type = "hidden";
                inputId.name = "id";
                inputId.value = id;

                var inputDelete = document.createElement("input");
                inputDelete.type = "hidden";
                inputDelete.name = "delete_event";
                inputDelete.value = "1";

                form.appendChild(inputId);
                form.appendChild(inputDelete);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>