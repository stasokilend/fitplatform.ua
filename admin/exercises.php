<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вправи – Адмін-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Навігація -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-heartbeat me-2"></i>FitPlatform</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Дашборд</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users"></i> Користувачі</a></li>
                    <li class="nav-item"><a class="nav-link active" href="exercises.php"><i class="fas fa-dumbbell"></i> Вправи</a></li>
                    <li class="nav-item"><a class="nav-link" href="stats.php"><i class="fas fa-chart-pie"></i> Статистика</a></li>
                    <li class="nav-item"><a class="nav-link" href="../dashboard.php"><i class="fas fa-user"></i> Кабінет</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Вихід</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-dumbbell text-primary me-2"></i>Управління вправами</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addExerciseModal">
                <i class="fas fa-plus me-2"></i>Додати вправу
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="exercisesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Назва</th>
                                <th>Тривалість</th>
                                <th>MET</th>
                                <th>Складність</th>
                                <th>Обладнання</th>
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody id="exercisesBody">
                            <tr><td colspan="7" class="text-center">Завантаження...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальне вікно додавання -->
    <div class="modal fade" id="addExerciseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Додати вправу</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addExerciseForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Назва *</label>
                                <input type="text" class="form-control" id="addExName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Тривалість (хв) *</label>
                                <input type="number" step="0.1" class="form-control" id="addExDuration" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">MET *</label>
                                <input type="number" step="0.1" class="form-control" id="addExMet" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Складність *</label>
                                <select class="form-select" id="addExDifficulty">
                                    <option value="1">Початківець</option>
                                    <option value="2">Середній</option>
                                    <option value="3">Просунутий</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Опис</label>
                            <textarea class="form-control" id="addExDescription" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Обладнання</label>
                                <input type="text" class="form-control" id="addExEquipment">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">URL відео</label>
                                <input type="url" class="form-control" id="addExVideo">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Групи м'язів (JSON)</label>
                            <input type="text" class="form-control" id="addExMuscles" placeholder='{"legs":0.8,"core":0.3}'>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Протипоказання (JSON)</label>
                            <input type="text" class="form-control" id="addExContraindications" placeholder='["hernia","knee_injury"]'>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="button" class="btn btn-success" id="saveExerciseBtn">Додати</button>
                </div>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/config.js"></script>
    <script src="../js/auth.js"></script>
    <script src="js/exercises.js"></script>
</body>
</html>