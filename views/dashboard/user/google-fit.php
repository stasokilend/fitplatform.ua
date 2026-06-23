<?php
require_once 'config/database.php';
require_once 'controllers/GoogleFitController.php';

$userId = $_SESSION['user_id'];
$googleFit = new GoogleFitController($userId);
$isConnected = $googleFit->isConnected();
$userInfo = $isConnected ? $googleFit->getUserInfo() : null;
$syncStatus = $googleFit->getSyncStatus();

// Получаем синхронизированные данные
$stmt = $pdo->prepare("
    SELECT * FROM user_activity_data 
    WHERE user_id = ? 
    ORDER BY activity_date DESC 
    LIMIT 30
");
$stmt->execute([$userId]);
$activityData = $stmt->fetchAll();
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-google text-primary"></i> Google Fit
        </h1>
        <div>
            <span class="badge bg-<?php echo $isConnected ? 'success' : 'danger'; ?>">
                <?php echo $isConnected ? '✅ Підключено' : '❌ Не підключено'; ?>
            </span>
        </div>
    </div>

    <?php if ($isConnected && $userInfo): ?>
        <!-- Информация о пользователе -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <?php if (isset($userInfo['picture'])): ?>
                        <img src="<?php echo htmlspecialchars($userInfo['picture']); ?>" loading="lazy" decoding="async" 
                             alt="Profile" class="rounded-circle me-3" width="64" height="64">
                    <?php else: ?>
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 64px; height: 64px; font-size: 2rem; color: #fff;">
                            <i class="bi bi-person"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h5 class="mb-0"><?php echo htmlspecialchars($userInfo['name'] ?? 'Користувач'); ?></h5>
                        <small class="text-muted"><?php echo htmlspecialchars($userInfo['email'] ?? ''); ?></small>
                    </div>
                    <div class="ms-auto">
                        <button class="btn btn-outline-danger btn-sm" id="disconnectBtn">
                            <i class="bi bi-link"></i> Відключити
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Синхронизация -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-arrow-repeat text-primary"></i> Синхронізація даних
                </h5>
                <div>
                    <select id="syncDays" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                        <option value="7">7 днів</option>
                        <option value="30" selected>30 днів</option>
                        <option value="90">90 днів</option>
                    </select>
                    <button class="btn btn-primary btn-sm" id="syncBtn">
                        <i class="bi bi-cloud-download"></i> Синхронізувати
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if ($syncStatus): ?>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>
                            <i class="bi bi-clock"></i> Остання синхронізація: 
                            <?php echo date('d.m.Y H:i', strtotime($syncStatus['sync_start'])); ?>
                        </span>
                        <span>
                            <i class="bi bi-calendar"></i> 
                            <?php echo date('d.m.Y', strtotime($syncStatus['sync_start'])); ?> - 
                            <?php echo date('d.m.Y', strtotime($syncStatus['sync_end'])); ?>
                        </span>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-2">
                        <small>Синхронізація ще не виконувалася</small>
                    </div>
                <?php endif; ?>
                <div id="syncStatus" class="mt-2" style="display: none;">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Завантаження...</span>
                        </div>
                        <span>Синхронізація даних...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Данные активности -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up text-primary"></i> Активність
                </h5>
            </div>
            <div class="card-body">
                <?php if (count($activityData) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th>Кроки</th>
                                    <th>Калорії</th>
                                    <th>Активні хв</th>
                                    <th>Джерело</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activityData as $data): ?>
                                    <tr>
                                        <td><?php echo date('d.m.Y', strtotime($data['activity_date'])); ?></td>
                                        <td><?php echo number_format($data['steps']); ?></td>
                                        <td><?php echo number_format($data['calories']); ?></td>
                                        <td><?php echo $data['active_minutes']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $data['source'] === 'google_fit' ? 'primary' : 'secondary'; ?>">
                                                <?php echo $data['source'] === 'google_fit' ? 'Google Fit' : 'Вручну'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                        <p>Немає даних активності</p>
                        <small>Синхронізуйте дані з Google Fit</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Подключение к Google Fit -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-google display-1 text-primary d-block mb-3"></i>
                <h4 class="mb-3">Підключіть Google Fit</h4>
                <p class="text-muted mb-4">
                    Синхронізуйте свої дані про активність, пульс та інші показники<br>
                    з Google Fit для більш точного відстеження прогресу
                </p>
                <button class="btn btn-primary btn-lg" id="connectBtn">
                    <i class="bi bi-google"></i> Підключити Google Fit
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($isConnected): ?>
        // Отключение
        document.getElementById('disconnectBtn').addEventListener('click', function() {
            if (!confirm('Ви впевнені, що хочете відключити Google Fit?')) return;
            
            const formData = new FormData();
            formData.append('action', 'disconnect');
            
            fetch('/api/google-fit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Google Fit відключено', 'info');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Помилка відключення', 'danger');
                }
            });
        });
        
        // Синхронизация
        document.getElementById('syncBtn').addEventListener('click', function() {
            const days = document.getElementById('syncDays').value;
            const statusDiv = document.getElementById('syncStatus');
            
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Синхронізація...';
            statusDiv.style.display = 'block';
            
            const formData = new FormData();
            formData.append('action', 'sync');
            formData.append('days', days);
            
            fetch('/api/google-fit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-cloud-download"></i> Синхронізувати';
                statusDiv.style.display = 'none';
                
                if (data.success) {
                    showToast('Дані успішно синхронізовано!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.error || 'Помилка синхронізації', 'danger');
                }
            })
            .catch(function(error) {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-cloud-download"></i> Синхронізувати';
                statusDiv.style.display = 'none';
                showToast('Помилка з\'єднання', 'danger');
            });
        });
    <?php else: ?>
        // Подключение
        document.getElementById('connectBtn').addEventListener('click', function() {
            const formData = new FormData();
            formData.append('action', 'auth_url');
            
            fetch('/api/google-fit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.url) {
                    window.location.href = data.url;
                } else {
                    showToast('Помилка отримання URL авторизації', 'danger');
                }
            });
        });
    <?php endif; ?>
});
</script>