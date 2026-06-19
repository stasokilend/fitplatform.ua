<?php
require_once 'config/database.php';
require_once 'controllers/HealthController.php';

$userId = $_SESSION['user_id'];
$health = new HealthController($userId);
$stats = $health->getHealthStats();
$zones = $stats['target_zones'];
$maxHr = $stats['max_hr'];
$restHr = $stats['resting_hr'];

// Получаем историю для графика
$history = $health->getHeartRateHistory(30);
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-heart-pulse text-danger"></i> Моніторинг здоров'я
        </h1>
        <div>
            <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
                <i class="bi bi-arrow-repeat"></i> Оновити
            </button>
        </div>
    </div>

    <!-- Быстрый ввод пульса -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-activity text-primary"></i> Виміряти пульс
                    </h5>
                </div>
                <div class="card-body">
                    <form id="heartRateForm" onsubmit="return false;">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Пульс спокою</label>
                                <input type="number" id="hrRest" class="form-control" 
                                       value="<?php echo $restHr; ?>" min="40" max="120">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Поточний пульс</label>
                                <input type="number" id="hrCurrent" class="form-control" 
                                       placeholder="Введіть пульс" min="40" max="220" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-gradient w-100" id="saveHeartRateBtn">
                                    <i class="bi bi-save"></i> Зберегти
                                </button>
                            </div>
                        </div>
                        <div class="mt-3" id="zoneDisplay" style="display: none;">
                            <div class="alert" id="zoneAlert">
                                <div class="d-flex align-items-center">
                                    <i class="bi display-6 me-3" id="zoneIcon"></i>
                                    <div>
                                        <h6 class="mb-0" id="zoneName">-</h6>
                                        <small id="zoneDescription">-</small>
                                    </div>
                                    <div class="ms-auto text-end">
                                        <span class="badge fs-6" id="zoneBadge">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Текущие показатели -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up text-primary"></i> Поточні показники
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="display-4 text-primary"><?php echo $maxHr; ?></div>
                            <small class="text-muted">Макс. ЧСС</small>
                        </div>
                        <div class="col-4">
                            <div class="display-4 text-success"><?php echo $restHr; ?></div>
                            <small class="text-muted">Пульс спокою</small>
                        </div>
                        <div class="col-4">
                            <div class="display-4 text-warning"><?php echo $stats['total_records']; ?></div>
                            <small class="text-muted">Всього вимірів</small>
                        </div>
                    </div>
                    <?php if ($stats['last_heart_rate']): ?>
                        <div class="mt-2 text-center text-muted small">
                            Останній вимір: <?php echo date('d.m.Y H:i', strtotime($stats['last_heart_rate']['recorded_at'])); ?>
                            <br>
                            Пульс: <?php echo $stats['last_heart_rate']['hr_current']; ?> уд/хв
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Целевые зоны пульса (компактные) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0">
                <i class="bi bi-target text-primary"></i> Цільові зони пульса
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <?php foreach ($zones as $key => $zone): ?>
                    <div class="col-6 col-md">
                        <div class="card h-100 text-center border-0 shadow-sm" 
                             style="border-top: 3px solid <?php echo $zone['color']; ?>;">
                            <div class="card-body py-2">
                                <i class="bi <?php echo $zone['icon']; ?>" style="font-size: 1.2rem; color: <?php echo $zone['color']; ?>;"></i>
                                <h6 class="mt-1 mb-0 small"><?php echo $zone['name']; ?></h6>
                                <small class="text-muted">
                                    <?php echo $zone['min_hr']; ?>-<?php echo $zone['max_hr']; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- График пульса (УМЕНЬШЕННЫЙ) -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-graph-up-arrow text-primary"></i> Динаміка пульса
            </h6>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary active" data-days="7">7д</button>
                <button class="btn btn-outline-secondary" data-days="30">30д</button>
                <button class="btn btn-outline-secondary" data-days="90">90д</button>
            </div>
        </div>
        <div class="card-body py-2">
            <canvas id="heartRateChart" style="height: 150px !important; max-height: 150px; width: 100%;"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ---- СОХРАНЕНИЕ ПУЛЬСА ----
    const form = document.getElementById('heartRateForm');
    const saveBtn = document.getElementById('saveHeartRateBtn');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const hrRest = parseInt(document.getElementById('hrRest').value) || 70;
        const hrCurrent = parseInt(document.getElementById('hrCurrent').value);
        
        if (!hrCurrent || hrCurrent < 30 || hrCurrent > 250) {
            showToast('Введіть коректне значення пульсу (30-250)', 'danger');
            return;
        }
        
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Збереження...';
        
        const formData = new FormData();
        formData.append('action', 'save');
        formData.append('hr_rest', hrRest);
        formData.append('hr_current', hrCurrent);
        
        fetch('/api/heart-rate.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-save"></i> Зберегти';
            
            if (data.success) {
                showToast('Показники збережено!', 'success');
                checkZone(hrCurrent);
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.error || 'Помилка збереження', 'danger');
            }
        })
        .catch(function(error) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-save"></i> Зберегти';
            showToast('Помилка з\'єднання', 'danger');
        });
    });
    
    // ---- ОПРЕДЕЛЕНИЕ ЗОНЫ ----
    function checkZone(hrCurrent) {
        const formData = new FormData();
        formData.append('action', 'zone');
        formData.append('hr_current', hrCurrent);
        
        fetch('/api/heart-rate.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const zone = data.data;
                const display = document.getElementById('zoneDisplay');
                const alert = document.getElementById('zoneAlert');
                const icon = document.getElementById('zoneIcon');
                const name = document.getElementById('zoneName');
                const desc = document.getElementById('zoneDescription');
                const badge = document.getElementById('zoneBadge');
                
                display.style.display = 'block';
                
                if (zone.zone === 'below') {
                    alert.className = 'alert alert-info';
                    icon.className = 'bi bi-arrow-down display-6 me-3';
                } else if (zone.zone === 'above') {
                    alert.className = 'alert alert-danger';
                    icon.className = 'bi bi-exclamation-triangle display-6 me-3';
                } else {
                    alert.className = 'alert alert-' + (zone.zone === 'warmup' ? 'info' : 
                                                         zone.zone === 'fat_burn' ? 'warning' : 
                                                         zone.zone === 'aerobic' ? 'success' : 
                                                         zone.zone === 'anaerobic' ? 'danger' : 'secondary');
                    icon.className = 'bi ' + zone.icon + ' display-6 me-3';
                }
                
                name.textContent = zone.name;
                desc.textContent = zone.zone !== 'below' && zone.zone !== 'above' 
                    ? 'Прогрес: ' + zone.percent + '% в зоні' 
                    : '';
                badge.textContent = hrCurrent + ' уд/хв';
                badge.className = 'badge fs-6 bg-' + (zone.zone === 'above' ? 'danger' : 'secondary');
            }
        })
        .catch(function(error) {
            console.error('Ошибка:', error);
        });
    }
    
    // ---- ГРАФИК ПУЛЬСА (УМЕНЬШЕННЫЙ) ----
    <?php if (count($history) > 0): ?>
        var ctx = document.getElementById('heartRateChart').getContext('2d');
        var historyData = <?php echo json_encode(array_reverse($history)); ?>;
        
        // Ограничиваем количество точек для читаемости
        var maxPoints = 20;
        var step = Math.max(1, Math.floor(historyData.length / maxPoints));
        var filteredData = historyData.filter(function(_, index) {
            return index % step === 0 || index === historyData.length - 1;
        });
        
        var labels = filteredData.map(function(item) {
            return item.recorded_at.substring(5, 10);
        });
        var restData = filteredData.map(function(item) {
            return item.hr_rest || null;
        });
        var currentData = filteredData.map(function(item) {
            return item.hr_current || null;
        });
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Пульс спокою',
                        data: restData,
                        borderColor: '#6C63FF',
                        backgroundColor: 'rgba(108, 99, 255, 0.05)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                        borderWidth: 1.5
                    },
                    {
                        label: 'Поточний пульс',
                        data: currentData,
                        borderColor: '#FF6584',
                        backgroundColor: 'rgba(255, 101, 132, 0.05)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 10,
                            padding: 8,
                            font: {
                                size: 10
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: {
                                size: 9
                            },
                            maxTicksLimit: 10
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: false,
                        min: Math.max(30, Math.min.apply(null, currentData) - 10),
                        max: Math.max.apply(null, currentData) + 10,
                        ticks: {
                            font: {
                                size: 9
                            },
                            stepSize: 20
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    <?php else: ?>
        document.getElementById('heartRateChart').parentElement.innerHTML = 
            '<div class="text-center py-3 text-muted">' +
            '<i class="bi bi-inbox display-6 d-block mb-1"></i>' +
            '<small>Ще немає даних про пульс</small>' +
            '</div>';
    <?php endif; ?>
    
    // ---- ПЕРЕКЛЮЧЕНИЕ ПЕРИОДА ----
    document.querySelectorAll('[data-days]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-days]').forEach(function(b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            showToast('Завантаження даних за ' + this.dataset.days + ' днів...', 'info');
        });
    });
});
</script>

<style>
/* Стили для компактного графика */
#heartRateChart {
    max-height: 150px !important;
    height: 150px !important;
}

/* Компактные зоны */
.card-body.py-2 {
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}

/* Уменьшаем отступы */
.card-header .h5,
.card-header .h6 {
    font-size: 0.95rem;
}

.display-4 {
    font-size: 2rem;
}

/* Адаптивность для зон */
@media (max-width: 576px) {
    .col-6.col-md {
        flex: 0 0 50%;
        max-width: 50%;
    }
}
</style>