<?php
require_once 'controllers/TrainerController.php';

$trainer = new TrainerController($userId);
$weekStart = $_GET['week'] ?? date('Y-m-d');
$schedule = $trainer->getSchedule($weekStart);

$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
$dayLabels = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'];
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-calendar text-primary"></i> Розклад
        </h1>
        <div>
            <a href="/dashboard.php?page=schedule&week=<?php echo date('Y-m-d', strtotime($weekStart . ' -7 days')); ?>" 
               class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-chevron-left"></i>
            </a>
            <span class="mx-2"><?php echo date('d.m.Y', strtotime($weekStart)); ?> - <?php echo date('d.m.Y', strtotime($weekStart . ' +6 days')); ?></span>
            <a href="/dashboard.php?page=schedule&week=<?php echo date('Y-m-d', strtotime($weekStart . ' +7 days')); ?>" 
               class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Час</th>
                        <?php foreach ($days as $day): ?>
                            <th><?php echo $dayLabels[array_search($day, $days)]; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($hour = 8; $hour <= 20; $hour++): ?>
                        <tr>
                            <td class="text-center text-muted small"><?php echo str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00'; ?></td>
                            <?php foreach ($days as $day): ?>
                                <td style="height: 40px;">
                                    <?php 
                                    $found = false;
                                    foreach ($schedule as $event) {
                                        if (strtolower($event['day_of_week']) === $day && 
                                            date('H', strtotime($event['start_time'])) == $hour) {
                                            $found = true;
                                            echo '<div class="badge bg-primary w-100">';
                                            echo htmlspecialchars($event['client_name'] ?? 'Заняття');
                                            echo '</div>';
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                        echo '<div class="text-muted small text-center">-</div>';
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>