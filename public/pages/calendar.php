<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();

if ($_SESSION['user']['role'] !== 'student') {
    header('Location: dashboard.php');
    exit();
}

$user_id = $_SESSION['user']['id'];

// Get current month and year from URL or use current date
$current_date = date('Y-m-d');
$current_month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$current_year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Validate month and year
if ($current_month < 1) $current_month = 12;
if ($current_month > 12) $current_month = 1;

// Get first day of the month and total days
$first_day_of_month = mktime(0, 0, 0, $current_month, 1, $current_year);
$num_days = date('t', $first_day_of_month);
$start_day_of_week = date('w', $first_day_of_month);
$month_name = date('F', $first_day_of_month);

// Fetch all activities with due dates for the student's classes
$stmt = $conn->prepare('
    SELECT a.id, a.name, a.due_date, a.class_id, c.subject, c.section
    FROM activities a
    JOIN classes c ON a.class_id = c.classes_id
    JOIN enrollments e ON c.classes_id = e.class_id
    WHERE e.user_id = ? AND a.due_date IS NOT NULL
    ORDER BY a.due_date ASC
');

if (!$stmt) {
    die('Database error: ' . $conn->error);
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$all_activities = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

// Organize activities by date
$activities_by_date = [];
foreach ($all_activities as $activity) {
    $date = $activity['due_date'];
    if (!isset($activities_by_date[$date])) {
        $activities_by_date[$date] = [];
    }
    $activities_by_date[$date][] = $activity;
}

// Calculate previous and next month
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year = $current_year - 1;
}

$next_month = $current_month + 1;
$next_year = $current_year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year = $current_year + 1;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Activity Calendar</title>
    <link rel="stylesheet" href="../assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../assets/CSS/calendar.css">
</head>
<body>
    <div class="header">
        <h2>Activity Deadline Calendar</h2>
        <a href="dashboard.php" class="logout">Back to Dashboard</a>
    </div>

    <div class="calendar-container">
        <div class="calendar-wrapper">
            <div class="calendar-header">
                <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="nav-btn prev-btn">← Previous</a>
                <h3><?php echo $month_name . ' ' . $current_year; ?></h3>
                <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="nav-btn next-btn">Next →</a>
            </div>

            <div class="calendar">
                <div class="weekdays">
                    <div class="weekday">Sun</div>
                    <div class="weekday">Mon</div>
                    <div class="weekday">Tue</div>
                    <div class="weekday">Wed</div>
                    <div class="weekday">Thu</div>
                    <div class="weekday">Fri</div>
                    <div class="weekday">Sat</div>
                </div>

                <div class="dates">
                    <?php
                    // Empty cells before the first day of the month
                    for ($i = 0; $i < $start_day_of_week; $i++) {
                        echo '<div class="date empty"></div>';
                    }

                    // Days of the month
                    for ($day = 1; $day <= $num_days; $day++) {
                        $date_str = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
                        $is_today = ($date_str === $current_date) ? 'today' : '';
                        $has_activities = isset($activities_by_date[$date_str]) ? 'has-activities' : '';
                        
                        echo '<div class="date ' . $is_today . ' ' . $has_activities . '">';
                        echo '<div class="date-number">' . $day . '</div>';
                        
                        if (isset($activities_by_date[$date_str])) {
                            echo '<div class="activity-indicators">';
                            foreach ($activities_by_date[$date_str] as $activity) {
                                echo '<div class="activity-dot" title="' . htmlspecialchars($activity['name']) . ' (' . htmlspecialchars($activity['subject']) . ')' . '"></div>';
                            }
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    }

                    // Empty cells after the last day of the month
                    $remaining_cells = (7 - (($start_day_of_week + $num_days) % 7)) % 7;
                    for ($i = 0; $i < $remaining_cells; $i++) {
                        echo '<div class="date empty"></div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="activities-sidebar">
            <h3>Upcoming Deadlines</h3>
            <?php if (count($all_activities) > 0): ?>
                <div class="activities-list">
                    <?php 
                    $displayed_count = 0;
                    foreach ($all_activities as $activity):
                        if ($activity['due_date'] >= $current_date && $displayed_count < 10):
                            $due_date = new DateTime($activity['due_date']);
                            $days_until = (int)$due_date->diff(new DateTime($current_date))->format('%d');
                            $overdue = $activity['due_date'] < $current_date;
                            $overdue_class = $overdue ? 'overdue' : '';
                    ?>
                        <div class="activity-item <?php echo $overdue_class; ?>">
                            <div class="activity-title"><?php echo htmlspecialchars($activity['name']); ?></div>
                            <div class="activity-class"><?php echo htmlspecialchars($activity['subject'] . ' - Section ' . $activity['section']); ?></div>
                            <div class="activity-due-date">
                                Due: <?php echo date('M d, Y', strtotime($activity['due_date'])); ?>
                                <?php if (!$overdue): ?>
                                    <span class="days-until"><?php echo $days_until > 0 ? '(' . $days_until . ' days)' : '(Due Today!)'; ?></span>
                                <?php else: ?>
                                    <span class="days-until overdue-text">(Overdue)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php 
                        $displayed_count++;
                        endif;
                    endforeach; 
                    ?>
                </div>

                <div class="all-activities-link">
                    <h4>All Activities with Deadlines</h4>
                    <div class="activities-full-list">
                        <?php foreach ($all_activities as $activity): ?>
                            <div class="activity-full-item">
                                <span class="activity-full-name"><?php echo htmlspecialchars($activity['name']); ?></span>
                                <span class="activity-full-class"><?php echo htmlspecialchars($activity['subject']); ?></span>
                                <span class="activity-full-date"><?php echo date('M d, Y', strtotime($activity['due_date'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-activities">
                    <p>No activities with deadlines yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
