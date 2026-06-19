<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/includes/admin-functions.php';

requireAdminLogin();

$exercises = getAllExercises();
$message = '';

// Обработка добавления
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'muscle_group' => $_POST['muscle_group'],
            'equipment' => $_POST['equipment'],
            'difficulty' => $_POST['difficulty'],
            'duration_min' => $_POST['duration_min'],
            'calories_per_min' => $_POST['calories_per_min'],
            'video_url' => $_POST['video_url'] ?? null,
            'image_url' => $_POST['image_url'] ?? null,
            'is_active' => 1
        ];
        
        if (addExercise($data)) {
            $message = ['type' => 'success', 'text' => 'Вправу додано!'];
            $exercises = getAllExercises();
        } else {
            $message = ['type' => 'danger', 'text' => 'Помилка додавання вправи'];
        }
    }
    
    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $exercise = getExerciseById($id);
        if ($exercise) {
            $newStatus = $exercise['is_active'] ? 0 : 1;
            updateExercise($id, ['is_active' => $newStatus] + (array)$exercise);
            $exercises = getAllExercises();
        }
    }
    
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        if (deleteExercise($id)) {
            $message = ['type' => 'success', 'text' => 'Вправу видалено'];
            $exercises = getAllExercises();
        }
    }
}

$muscleGroups = ['Груди', 'Спина', 'Ноги', 'Плечі', 'Руки', 'Прес', 'Все тіло'];
$difficulties = ['beginner' => 'Початківець', 'intermediate' => 'Середній', 'advanced' => 'Просунутий'];

$pageTitle = 'Вправи';
ob_start();
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2"><i class="bi bi-dumbbell"></i> Вправи</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExerciseModal">
            <i class="bi bi-plus-circle"></i> Додати вправу
        </button>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $message['text']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row g-4">
        <?php foreach ($exercises as $exercise): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 <?php echo !$exercise['is_active'] ? 'opacity-50' : ''; ?>">
                    <?php if ($exercise['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($exercise['image_url']); ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light text-center py-4">
                            <i class="bi bi-dumbbell display-1 text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($exercise['name']); ?></h5>
                        <p class="card-text small text-muted">
                            <i class="bi bi-tag"></i> <?php echo $exercise['muscle_group']; ?>
                            <br>
                            <i class="bi bi-clock"></i> <?php echo $exercise['duration_min']; ?> хв
                            <br>
                            <i class="bi bi-fire"></i> <?php echo $exercise['calories_per_min']; ?> ккал/хв
                            <br>
                            <span class="badge bg-<?php echo $exercise['difficulty'] === 'beginner' ? 'success' : ($exercise['difficulty'] === 'intermediate' ? 'warning' : 'danger'); ?>">
                                <?php echo $difficulties[$exercise['difficulty']]; ?>
                            </span>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100">
                            <form method="POST" style="display:inline; flex:1;">
                                <input type="hidden" name="id" value="<?php echo $exercise['id']; ?>">
                                <input type="hidden" name="action" value="toggle">
                                <button type="submit" class="btn btn-sm btn-<?php echo $exercise['is_active'] ? 'warning' : 'success'; ?> w-100">
                                    <i class="bi bi-<?php echo $exercise['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    <?php echo $exercise['is_active'] ? 'Сховати' : 'Показати'; ?>
                                </button>
                            </form>
                            <form method="POST" style="display:inline; flex:1;" onsubmit="return confirm('Ви впевнені?');">
                                <input type="hidden" name="id" value="<?php echo $exercise['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-sm btn-danger w-100">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal добавления упражнения -->
<div class="modal fade" id="addExerciseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Додати вправу</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Назва</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Опис</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Група м'язів</label>
                            <select name="muscle_group" class="form-select" required>
                                <?php foreach ($muscleGroups as $group): ?>
                                    <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Складність</label>
                            <select name="difficulty" class="form-select" required>
                                <option value="beginner">Початківець</option>
                                <option value="intermediate">Середній</option>
                                <option value="advanced">Просунутий</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <label class="form-label">Тривалість (хв)</label>
                            <input type="number" name="duration_min" class="form-control" step="0.5" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Ккал/хв</label>
                            <input type="number" name="calories_per_min" class="form-control" step="0.1" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-2">
                        <label class="form-label">Обладнання</label>
                        <input type="text" name="equipment" class="form-control" placeholder="Наприклад: гантелі, турнік...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL відео (YouTube)</label>
                        <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL зображення</label>
                        <input type="url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="submit" class="btn btn-primary">Додати</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once 'layout/admin-layout.php';
?>