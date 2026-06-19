<?php
// profile-setup.php - Заповнення профілю

$pageTitle = 'Налаштування профілю – FitPlatform';
$extraScripts = '
<script src="js/profile-setup.js"></script>
';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h3><i class="fas fa-user-edit me-2"></i>Заповніть профіль</h3>
                    <p class="mb-0">Це необхідно для створення персоналізованих тренувань</p>
                </div>
                <div class="card-body p-4">
                    <form id="profileSetupForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="setupAge" class="form-label">Вік (років) *</label>
                                <input type="number" class="form-control" id="setupAge" required min="10" max="100">
                            </div>
                            <div class="col-md-6">
                                <label for="setupWeight" class="form-label">Вага (кг) *</label>
                                <input type="number" step="0.1" class="form-control" id="setupWeight" required min="20" max="300">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="setupHeight" class="form-label">Зріст (см) *</label>
                                <input type="number" class="form-control" id="setupHeight" required min="50" max="300">
                            </div>
                            <div class="col-md-6">
                                <label for="setupGender" class="form-label">Стать *</label>
                                <select class="form-select" id="setupGender">
                                    <option value="male">Чоловік</option>
                                    <option value="female">Жінка</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="setupLevel" class="form-label">Рівень фізичної підготовки *</label>
                            <select class="form-select" id="setupLevel">
                                <option value="1">Початківець</option>
                                <option value="2">Середній</option>
                                <option value="3">Просунутий</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Пріоритети тренувань (сума = 1)</label>
                            <div class="row">
                                <div class="col-4">
                                    <label class="small">Схуднення</label>
                                    <input type="number" step="0.01" class="form-control" id="setupGoalLose" value="0.33" min="0" max="1">
                                </div>
                                <div class="col-4">
                                    <label class="small">Набір м'язів</label>
                                    <input type="number" step="0.01" class="form-control" id="setupGoalMuscle" value="0.33" min="0" max="1">
                                </div>
                                <div class="col-4">
                                    <label class="small">Витривалість</label>
                                    <input type="number" step="0.01" class="form-control" id="setupGoalEndurance" value="0.34" min="0" max="1">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Медичні обмеження (якщо є)</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="hernia" id="setupRestHernia">
                                        <label class="form-check-label" for="setupRestHernia">Грижа</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="hypertension" id="setupRestHypertension">
                                        <label class="form-check-label" for="setupRestHypertension">Гіпертонія</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="knee_injury" id="setupRestKnee">
                                        <label class="form-check-label" for="setupRestKnee">Травма колін</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="shoulder_injury" id="setupRestShoulder">
                                        <label class="form-check-label" for="setupRestShoulder">Травма плечей</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="profileSetupError" class="alert alert-danger d-none"></div>
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-save me-2"></i>Зберегти профіль та продовжити
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>