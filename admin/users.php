<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/includes/admin-functions.php';

requireAdminLogin();

$users = getAllUsers(100);
$message = '';

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);
    
    if ($action === 'toggle_active') {
        $user = getUserById($userId);
        if ($user) {
            $newStatus = $user['is_active'] ? 0 : 1;
            if (updateUser($userId, ['is_active' => $newStatus])) {
                $message = ['type' => 'success', 'text' => 'Статус користувача змінено'];
            }
        }
    }
    
    if ($action === 'make_trainer') {
        if (updateUser($userId, ['role' => 'trainer'])) {
            $message = ['type' => 'success', 'text' => 'Користувача призначено тренером'];
        }
    }
    
    if ($action === 'make_user') {
        if (updateUser($userId, ['role' => 'user'])) {
            $message = ['type' => 'success', 'text' => 'Користувача призначено звичайним'];
        }
    }
    
    if ($action === 'delete') {
        if (deleteUser($userId)) {
            $message = ['type' => 'success', 'text' => 'Користувача видалено'];
        }
    }
}

$pageTitle = 'Користувачі';
ob_start();
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2"><i class="bi bi-people"></i> Користувачі</h1>
        <div>
            <span class="text-muted">Всього: <?php echo count($users); ?></span>
        </div>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $message['text']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Ім'я</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Рівень</th>
                    <th>Мета</th>
                    <th>Статус</th>
                    <th>Дата реєстрації</th>
                    <th>Дії</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'trainer' ? 'warning' : 'secondary'); ?>">
                                <?php echo $user['role'] === 'admin' ? 'Адмін' : ($user['role'] === 'trainer' ? 'Тренер' : 'Користувач'); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $levels = [
                                'beginner' => 'Початківець',
                                'intermediate' => 'Середній',
                                'advanced' => 'Просунутий'
                            ];
                            echo $levels[$user['fitness_level']] ?? '-';
                            ?>
                        </td>
                        <td>
                            <?php 
                            $goals = [
                                'weight_loss' => 'Зниження ваги',
                                'muscle_gain' => 'Набір маси',
                                'endurance' => 'Витривалість',
                                'health' => 'Здоров\'я'
                            ];
                            echo $goals[$user['goal_type']] ?? '-';
                            ?>
                        </td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge bg-success">Активний</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Заблокований</span>
                            <?php endif; ?>
                            <?php if ($user['profile_completed']): ?>
                                <span class="badge bg-info">Профіль заповнено</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Профіль не заповнено</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="toggle_active">
                                    <button type="submit" class="btn btn-<?php echo $user['is_active'] ? 'warning' : 'success'; ?>" 
                                            title="<?php echo $user['is_active'] ? 'Заблокувати' : 'Розблокувати'; ?>">
                                        <i class="bi bi-<?php echo $user['is_active'] ? 'lock' : 'unlock'; ?>"></i>
                                    </button>
                                </form>
                                
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <?php if ($user['role'] === 'user'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="make_trainer">
                                            <button type="submit" class="btn btn-primary" title="Зробити тренером">
                                                <i class="bi bi-person-badge"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="make_user">
                                            <button type="submit" class="btn btn-secondary" title="Зробити користувачем">
                                                <i class="bi bi-person"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Ви впевнені?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger" title="Видалити">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once 'layout/admin-layout.php';
?>