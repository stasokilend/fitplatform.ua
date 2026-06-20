<?php
require_once 'config/database.php';
require_once 'controllers/WorkoutController.php';

$workout = new WorkoutController($userId);
$stats = $workout->getWorkoutStats();

// Пагинация с защитой от отрицательных значений
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;
$status = $_GET['status'] ?? null;

// Получаем тренировки
$workouts = $workout->getUserWorkouts($limit, $offset, $status);
$total = $stats['total'];

// Получаем шаблоны
$templates = $workout->getTemplates(5);
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-calendar-check text-primary"></i> Тренування
        </h1>
        <div>
            <span class="text-muted me-3">
                <i class="bi bi-check-circle text-success"></i> 
                <?php echo $stats['completed']; ?>/<?php echo $stats['total']; ?>
            </span>
            <a href="/dashboard.php?page=workout-create" class="btn btn-primary btn-gradient">
                <i class="bi bi-plus-circle"></i> Нове тренування
            </a>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row g-3 mb-4">
        <div class="col-3 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Всього</div>
            </div>
        </div>
        <div class="col-3 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success"><?php echo $stats['completed']; ?></div>
                <div class="stat-label">Завершено</div>
            </div>
        </div>
        <div class="col-3 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning"><?php echo $stats['in_progress']; ?></div>
                <div class="stat-label">В процесі</div>
            </div>
        </div>
        <div class="col-3 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-info"><?php echo number_format($stats['total_calories'], 0); ?></div>
                <div class="stat-label">Калорій</div>
            </div>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <a href="/dashboard.php?page=workouts" 
           class="btn btn-<?php echo !$status ? 'primary' : 'outline-secondary'; ?> btn-sm filter-btn">
            <i class="bi bi-list"></i> Всі (<?php echo $stats['total']; ?>)
        </a>
        <a href="/dashboard.php?page=workouts&status=completed" 
           class="btn btn-<?php echo $status === 'completed' ? 'success' : 'outline-secondary'; ?> btn-sm filter-btn">
            <i class="bi bi-check-circle"></i> Завершені (<?php echo $stats['completed']; ?>)
        </a>
        <a href="/dashboard.php?page=workouts&status=in_progress" 
           class="btn btn-<?php echo $status === 'in_progress' ? 'warning' : 'outline-secondary'; ?> btn-sm filter-btn">
            <i class="bi bi-clock"></i> В процесі (<?php echo $stats['in_progress']; ?>)
        </a>
        <a href="/dashboard.php?page=workouts&status=planned" 
           class="btn btn-<?php echo $status === 'planned' ? 'info' : 'outline-secondary'; ?> btn-sm filter-btn">
            <i class="bi bi-calendar"></i> Заплановані (<?php echo $stats['planned']; ?>)
        </a>
    </div>

    <!-- Список тренировок -->
    <div class="row g-4" id="workoutsContainer">
        <?php if (count($workouts) > 0): ?>
            <?php foreach ($workouts as $plan): 
                $progress = $plan['total_exercises'] > 0 
                    ? round(($plan['completed_exercises'] / $plan['total_exercises']) * 100) 
                    : 0;
            ?>
                <div class="col-md-6 col-lg-4 workout-card-item">
                    <div class="card workout-card h-100">
                        <div class="card-header-custom">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">
                                    <i class="bi bi-dumbbell me-2"></i>
                                    <?php echo htmlspecialchars($plan['name']); ?>
                                </span>
                                <span class="badge bg-white bg-opacity-25">
                                    <?php echo $plan['status'] === 'completed' ? '✅' : ($plan['status'] === 'in_progress' ? '⏳' : '📋'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 mb-2 flex-wrap">
                                <span class="badge bg-<?php echo $plan['status'] === 'completed' ? 'success' : ($plan['status'] === 'in_progress' ? 'warning' : 'secondary'); ?>">
                                    <?php echo $plan['status'] === 'completed' ? 'Завершено' : ($plan['status'] === 'in_progress' ? 'В процесі' : 'Заплановано'); ?>
                                </span>
                                <?php if ($plan['difficulty']): ?>
                                    <span class="badge bg-<?php echo $plan['difficulty'] === 'advanced' ? 'danger' : ($plan['difficulty'] === 'intermediate' ? 'warning' : 'success'); ?>">
                                        <?php echo ucfirst($plan['difficulty']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mb-2">
                                <span><i class="bi bi-clock"></i> <?php echo $plan['total_duration_min'] ?? 0; ?> хв</span>
                                <span><i class="bi bi-calendar"></i> <?php echo date('d.m.Y', strtotime($plan['created_at'])); ?></span>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small">
                                    <span>Прогрес</span>
                                    <span><?php echo $progress; ?>%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?php echo $progress >= 100 ? 'success' : 'primary'; ?>" 
                                         style="width: <?php echo $progress; ?>%;">
                                    </div>
                                </div>
                            </div>
                            <?php if ($plan['notes_count'] > 0): ?>
                                <small class="text-muted">
                                    <i class="bi bi-pencil"></i> <?php echo $plan['notes_count']; ?> нотаток
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <?php if ($plan['status'] === 'completed'): ?>
                                <a href="/dashboard.php?page=workout-detail&id=<?php echo $plan['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-eye"></i> Деталі
                                </a>
                            <?php elseif ($plan['status'] === 'in_progress'): ?>
                                <a href="/dashboard.php?page=workout-track&id=<?php echo $plan['id']; ?>" 
                                   class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-play"></i> Продовжити
                                </a>
                            <?php else: ?>
                                <a href="/dashboard.php?page=workout-track&id=<?php echo $plan['id']; ?>" 
                                   class="btn btn-sm btn-success w-100">
                                    <i class="bi bi-play-circle"></i> Почати
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-empty display-4 d-block mb-3"></i>
                    <h4 class="fw-semibold">Ще немає тренувань</h4>
                    <p>Створіть своє перше тренування або використайте готовий шаблон</p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="/dashboard.php?page=workout-create" class="btn btn-primary btn-gradient">
                            <i class="bi bi-plus-circle"></i> Створити
                        </a>
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#templatesModal">
                            <i class="bi bi-collection"></i> Шаблони
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Пагинация -->
    <?php if ($total > $limit): 
        $totalPages = ceil($total / $limit);
    ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>">←</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>">→</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Modal шаблонов -->
<div class="modal fade" id="templatesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-collection text-primary"></i> Шаблони тренувань</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (count($templates) > 0): ?>
                    <div class="row g-3">
                        <?php foreach ($templates as $template): ?>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($template['name']); ?></h6>
                                        <p class="card-text small text-muted"><?php echo htmlspecialchars($template['description'] ?? ''); ?></p>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="badge bg-<?php echo $template['difficulty'] === 'advanced' ? 'danger' : ($template['difficulty'] === 'intermediate' ? 'warning' : 'success'); ?>">
                                                <?php echo ucfirst($template['difficulty']); ?>
                                            </span>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-clock"></i> <?php echo $template['duration_min']; ?> хв
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent border-0">
                                        <button class="btn btn-sm btn-primary w-100 use-template-btn" 
                                                data-template="<?php echo $template['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($template['name']); ?>">
                                            <i class="bi bi-plus-circle"></i> Використати
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-collection display-4 d-block mb-2"></i>
                        <p>Немає доступних шаблонів</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Использование шаблона
    document.querySelectorAll('.use-template-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const templateId = this.dataset.template;
            const name = this.dataset.name;
            
            if (!confirm(`Створити тренування "${name}" з шаблону?`)) return;
            
            const formData = new FormData();
            formData.append('action', 'create_from_template');
            formData.append('template_id', templateId);
            formData.append('name', 'Шаблон: ' + name);
            
            fetch('/api/workout.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Тренування створено з шаблону!', 'success');
                    setTimeout(() => {
                        window.location.href = '/dashboard.php?page=workout-track&id=' + data.workout_id;
                    }, 1000);
                } else {
                    showToast(data.error || 'Помилка створення', 'danger');
                }
            })
            .catch(function() {
                showToast('Помилка з\'єднання', 'danger');
            });
        });
    });
});
</script>