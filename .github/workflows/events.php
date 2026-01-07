<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../student_login.php');
    exit;
}

require_once '../includes/db.php';

// Handle event registration
if (isset($_GET['register'])) {
    $event_id = intval($_GET['register']);
    $student_id = $_SESSION['user_id'];

    $check_sql = "SELECT * FROM event_registrations WHERE event_id='$event_id' AND student_id='$student_id'";
    $check_res = $conn->query($check_sql);

    if ($check_res->num_rows === 0) {
        $insert_sql = "INSERT INTO event_registrations (event_id, student_id) VALUES ('$event_id', '$student_id')";
        if ($conn->query($insert_sql)) {
            $success_msg = "You have successfully registered for the event!";
        } else {
            $error_msg = "Error registering for the event.";
        }
    } else {
        $error_msg = "You are already registered for this event.";
    }
}

// Fetch upcoming events
$today = date('Y-m-d');
$events_sql = "SELECT * FROM events WHERE event_date >= '$today' ORDER BY event_date ASC";
$events_result = $conn->query($events_sql);

$events = [];
while ($row = $events_result->fetch_assoc()) {
    $events[] = $row;
}

include '../includes/header.php';
?>

<main class="content-section">
    <div class="events-section">
        <h1 class="section-title">📅 Upcoming Events</h1>
        <p class="section-subtitle">Check out events organized by your institute and register to participate.</p>

        <?php if (!empty($success_msg)): ?>
            <p class="form-success"><?= $success_msg; ?></p>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <p class="form-error"><?= $error_msg; ?></p>
        <?php endif; ?>

        <div class="events-grid">
            <?php if (count($events) > 0): ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <?php if (!empty($event['banner_image'])): ?>
                            <img src="../uploads/events/<?= htmlspecialchars($event['banner_image']); ?>" alt="<?= htmlspecialchars($event['title']); ?>" class="event-banner">
                        <?php endif; ?>
                        <h3 class="event-title"><?= htmlspecialchars($event['title']); ?></h3>
                        <p class="event-date"><?= date('d M Y', strtotime($event['event_date'])); ?> at <?= date('H:i', strtotime($event['event_time'])); ?></p>
                        <p class="event-venue"><?= htmlspecialchars($event['venue']); ?></p>
                        <p class="event-description"><?= nl2br(htmlspecialchars($event['description'])); ?></p>
                        <a href="?register=<?= $event['event_id']; ?>" class="btn btn-primary">Register</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">No upcoming events at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
