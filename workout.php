<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тренування – FitPlatform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.html"><i class="fas fa-arrow-left me-2"></i>Назад до кабінету</a>
            <span class="navbar-text text-white" id="sessionTimer">00:00</span>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <!-- Основна зона тренування -->
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 id="exerciseName">Підготовка...</h4>
                        <p class="mb-0">Вправа <span id="currentExerciseIndex">0</span> з <span id="totalExercises">0</span></p>
                    </div>
                    <div class="card-body text-center">
                        <!-- Відео або опис -->
                        <div id="exerciseVideo" class="mb-3">
                            <img src="https://via.placeholder.com/400x200?text=Вправа" class="img-fluid rounded" alt="Exercise">
                        </div>
                        <p id="exerciseDescription" class="text-muted">Опис вправи</p>

                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <small>Підходи</small>
                                    <h4 id="exerciseSets">0</h4>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <small>Повторення</small>
                                    <h4 id="exerciseReps">0</h4>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <small>Відпочинок</small>
                                    <h4 id="exerciseRest">0с</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Таймер виконання -->
                        <div class="mb-3">
                            <div class="progress" style="height: 10px;">
                                <div id="exerciseProgress" class="progress-bar bg-success" style="width: 100%;"></div>
                            </div>
                            <span id="exerciseTimer" class="badge bg-dark mt-1">0с</span>
                        </div>

                        <!-- Моніторинг пульсу -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-heartbeat text-danger"></i></span>
                                    <input type="number" class="form-control" id="heartRateInput" placeholder="Пульс (BPM)" min="30" max="220">
                                    <button class="btn btn-outline-danger" id="sendHrBtn">Відправити</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="hrZoneIndicator" class="p-2 rounded text-center text-white" style="background-color: #6c757d;">
                                    <strong>Зона:</strong> <span id="hrZoneText">Очікування</span>
                                </div>
                                <div id="hrRecommendation" class="small text-muted mt-1">Рекомендація: розпочніть виконання</div>
                            </div>
                        </div>

                        <!-- Кнопки контролю -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button class="btn btn-success btn-lg" id="startExerciseBtn"><i class="fas fa-play me-2"></i>Почати підхід</button>
                            <button class="btn btn-warning btn-lg" id="restBtn" disabled><i class="fas fa-pause me-2"></i>Відпочинок</button>
                            <button class="btn btn-danger btn-lg" id="finishWorkoutBtn"><i class="fas fa-stop me-2"></i>Завершити</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/config.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/workout.js"></script>
</body>
</html>