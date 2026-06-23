<?php
require_once 'controllers/TrainerController.php';

$trainer = new TrainerController($userId);
$clients = $trainer->getClients('active');

// Получаем пользователей, которых тренер может добавить
$availableUsers = $trainer->getAvailableClients();
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-people text-primary"></i> Клієнти
        </h1>
        <button class="btn btn-primary btn-gradient" data-bs-toggle="modal" data-bs-target="#addClientModal">
            <i class="bi bi-person-plus"></i> Додати клієнта
        </button>
    </div>

    <!-- Фильтры -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <button class="btn btn-outline-primary btn-sm active filter-btn" data-status="active">
            <i class="bi bi-person-check"></i> Активні (<?php echo count($clients); ?>)
        </button>
        <button class="btn btn-outline-secondary btn-sm filter-btn" data-status="pending">
            <i class="bi bi-clock"></i> Запити
        </button>
        <button class="btn btn-outline-secondary btn-sm filter-btn" data-status="inactive">
            <i class="bi bi-person-x"></i> Неактивні
        </button>
    </div>

    <div class="row g-4" id="clientsContainer">
        <?php if (count($clients) > 0): ?>
            <?php foreach ($clients as $client): ?>
                <?php $clientStatus = $client['status'] ?? 'active'; ?>
                <div class="col-md-6 col-lg-4 client-card" data-status="<?php echo htmlspecialchars($clientStatus); ?>">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-placeholder bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px; font-size: 1.5rem; flex-shrink: 0;">
                                    <?php echo strtoupper(substr($client['full_name'], 0, 1)); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($client['full_name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($client['email']); ?></small>
                                </div>
                                <?php if ($client['unread_messages'] > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?php echo $client['unread_messages']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="bi bi-calendar"></i> З: <?php echo date('d.m.Y', strtotime($client['assigned_at'])); ?></span>
                                <span>
                                    <span class="badge bg-<?php echo $client['fitness_level'] === 'advanced' ? 'danger' : ($client['fitness_level'] === 'intermediate' ? 'warning' : 'success'); ?>">
                                        <?php echo getFitnessLevelLabel($client['fitness_level']); ?>
                                    </span>
                                </span>
                            </div>
                            
                            <?php if ($client['notes']): ?>
                                <p class="small text-muted mt-2 mb-0"><?php echo htmlspecialchars(substr($client['notes'], 0, 60)) . (strlen($client['notes']) > 60 ? '...' : ''); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="/dashboard.php?page=client-detail&id=<?php echo $client['id']; ?>" 
                               class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-eye"></i> Деталі
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people display-4 d-block mb-2"></i>
                    <h5>Ще немає клієнтів</h5>
                    <p>Додайте першого клієнта, щоб почати роботу</p>
                    <button class="btn btn-primary btn-gradient" data-bs-toggle="modal" data-bs-target="#addClientModal">
                        <i class="bi bi-person-plus"></i> Додати клієнта
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal добавления клиента -->
<div class="modal fade" id="addClientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Додати клієнта</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addClientForm">
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i> Додати можна лише зареєстрованого активного користувача, який ще не є вашим клієнтом. Після додавання чат створиться автоматично.
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="clientSearchInput">Пошук клієнта</label>
                        <input type="search" id="clientSearchInput" class="form-control" placeholder="Ім’я або email" autocomplete="off">
                        <div class="form-text">Почніть вводити ім’я або email, щоб швидко знайти користувача.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="clientSelect">Виберіть клієнта</label>
                        <select name="client_id" id="clientSelect" class="form-select" required <?php echo count($availableUsers) === 0 ? 'disabled' : ''; ?>>
                            <option value="">-- Виберіть --</option>
                            <?php foreach ($availableUsers as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['full_name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" id="clientSelectHelp">
                            <?php echo count($availableUsers) === 0 ? 'Немає доступних користувачів для додавання.' : 'Доступно користувачів: ' . count($availableUsers); ?>
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Нотатки</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Додаткова інформація про клієнта"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="submit" class="btn btn-primary" id="addClientBtn" <?php echo count($availableUsers) === 0 ? 'disabled' : ''; ?>>Додати</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Фильтрация клиентов
    document.querySelectorAll('.filter-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(function(b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            
            const status = this.dataset.status;
            const cards = document.querySelectorAll('.client-card');
            
            cards.forEach(function(card) {
                if (card.dataset.status === status) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    
    const clientSelect = document.getElementById('clientSelect');
    const clientSearchInput = document.getElementById('clientSearchInput');
    const clientSelectHelp = document.getElementById('clientSelectHelp');

    function renderClientOptions(users) {
        clientSelect.innerHTML = '<option value="">-- Виберіть --</option>';
        users.forEach(function(user) {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = user.full_name + ' (' + user.email + ')';
            clientSelect.appendChild(option);
        });
        clientSelect.disabled = users.length === 0;
        document.getElementById('addClientBtn').disabled = users.length === 0;
        clientSelectHelp.textContent = users.length === 0
            ? 'Користувачів за цим пошуком не знайдено.'
            : 'Знайдено користувачів: ' + users.length;
    }

    let clientSearchTimer = null;
    clientSearchInput.addEventListener('input', function() {
        clearTimeout(clientSearchTimer);
        clientSearchTimer = setTimeout(function() {
            const params = new URLSearchParams({
                action: 'available_clients',
                search: clientSearchInput.value.trim()
            });

            fetch('/api/trainer.php?' + params.toString())
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        renderClientOptions(data.data || []);
                    } else {
                        showToast(data.error || 'Помилка пошуку клієнтів', 'danger');
                    }
                })
                .catch(function(error) {
                    showToast('Помилка пошуку клієнтів: ' + error.message, 'danger');
                });
        }, 300);
    });

    // Добавление клиента
    document.getElementById('addClientForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'add_client');
        
        const btn = document.getElementById('addClientBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Додавання...';
        
        fetch('/api/trainer.php', {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.text().then(function(text) {
                let data;
                try {
                    data = JSON.parse(text);
                } catch (error) {
                    throw new Error(text || 'Сервер повернув некоректну відповідь');
                }

                if (!response.ok) {
                    throw new Error(data.error || 'HTTP ' + response.status);
                }

                return data;
            });
        })
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Додати';
            
            if (data.success) {
                showToast('Клієнта додано! 🎉', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.error || 'Помилка додавання', 'danger');
            }
        })
        .catch(function(error) {
            btn.disabled = false;
            btn.innerHTML = 'Додати';
            showToast('Помилка додавання клієнта: ' + error.message, 'danger');
        });
    });
});
</script>