<?php
$caloriePlan = calculateCaloriePlan($profile ?? []);
$goalLabel = getGoalTypeLabel($profile['goal_type'] ?? 'health');
$fitnessLabel = getFitnessLevelLabel($profile['fitness_level'] ?? 'beginner');
$delta = (int)($caloriePlan['calorie_delta'] ?? 0);
$deltaText = $delta > 0 ? '+' . number_format($delta, 0) : number_format($delta, 0);
?>

<div class="fade-in-up">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">
            <i class="bi bi-fire text-warning"></i> Калорії та харчування
        </h1>
        <a href="/profile-setup.php" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil-square"></i> Оновити профіль
        </a>
    </div>

    <?php if (!$caloriePlan['is_complete']): ?>
        <div class="alert alert-warning border-0 shadow-sm">
            <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Потрібно заповнити профіль</h5>
            <p class="mb-2">Щоб розрахувати норму калорій, додайте вік, вагу, зріст, стать, рівень підготовки та ціль.</p>
            <a href="/profile-setup.php" class="btn btn-warning btn-sm">Заповнити дані</a>
        </div>
    <?php else: ?>
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="stat-label">Базовий обмін</div>
                    <div class="stat-number text-primary"><?php echo number_format($caloriePlan['bmr']); ?></div>
                    <small class="text-muted">ккал/день у спокої</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="stat-label">Підтримка ваги</div>
                    <div class="stat-number text-info"><?php echo number_format($caloriePlan['maintenance_calories']); ?></div>
                    <small class="text-muted">з урахуванням активності</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="stat-label">Ціль на день</div>
                    <div class="stat-number text-warning"><?php echo number_format($caloriePlan['target_calories']); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($deltaText); ?> ккал до підтримки</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="stat-label">Вода</div>
                    <div class="stat-number text-success"><?php echo htmlspecialchars((string)$caloriePlan['water_l']); ?> л</div>
                    <small class="text-muted">орієнтир на день</small>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-pie-chart text-primary"></i> Макронутрієнти</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center g-3">
                            <div class="col-md-4">
                                <div class="p-3 rounded-4 bg-light">
                                    <div class="display-6 text-danger"><?php echo number_format($caloriePlan['protein_g']); ?> г</div>
                                    <div class="fw-semibold">Білки</div>
                                    <small class="text-muted">відновлення та м'язи</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-4 bg-light">
                                    <div class="display-6 text-warning"><?php echo number_format($caloriePlan['fat_g']); ?> г</div>
                                    <div class="fw-semibold">Жири</div>
                                    <small class="text-muted">гормони та енергія</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-4 bg-light">
                                    <div class="display-6 text-success"><?php echo number_format($caloriePlan['carbs_g']); ?> г</div>
                                    <div class="fw-semibold">Вуглеводи</div>
                                    <small class="text-muted">паливо для тренувань</small>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <p class="text-muted mb-0 small">
                            Розрахунок використовує формулу Mifflin-St Jeor, множник активності за рівнем підготовки та корекцію під вашу мету: <?php echo htmlspecialchars($goalLabel); ?>.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0"><i class="bi bi-lightbulb text-warning"></i> Рекомендації</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3"><i class="bi bi-check-circle text-success me-2"></i>Тримайте денну ціль близько <?php echo number_format($caloriePlan['target_calories']); ?> ккал 10–14 днів і відстежуйте вагу.</li>
                            <li class="mb-3"><i class="bi bi-check-circle text-success me-2"></i>Якщо прогрес зупинився, коригуйте раціон на 100–150 ккал, а не різко.</li>
                            <li class="mb-3"><i class="bi bi-check-circle text-success me-2"></i>Рівень активності: <strong><?php echo htmlspecialchars($fitnessLabel); ?></strong>. Оновлюйте його, коли тренування стають регулярнішими.</li>
                            <li><i class="bi bi-info-circle text-primary me-2"></i>Це орієнтир, а не медична порада. За захворювань або вагітності зверніться до фахівця.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
