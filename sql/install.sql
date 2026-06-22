-- Создаем базу
CREATE DATABASE IF NOT EXISTS fitness_platform;
USE fitness_platform;

-- Пользователи
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'trainer', 'admin') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Профили пользователей
CREATE TABLE user_profiles (
    user_id INT PRIMARY KEY,
    age INT,
    weight DECIMAL(5,2),
    height INT,
    gender ENUM('male', 'female', 'other'),
    fitness_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    goal_type ENUM('weight_loss', 'muscle_gain', 'endurance', 'health') DEFAULT 'health',
    target_weight DECIMAL(5,2),
    medical_notes TEXT,
    profile_completed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Медицинские ограничения (справочник)
CREATE TABLE medical_restrictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(50) DEFAULT 'bi-heart-pulse'
);

-- Связь пользователя с ограничениями
CREATE TABLE user_restrictions (
    user_id INT,
    restriction_id INT,
    PRIMARY KEY (user_id, restriction_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restriction_id) REFERENCES medical_restrictions(id)
);

-- Упражнения
CREATE TABLE exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    muscle_group VARCHAR(50),
    equipment VARCHAR(100),
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    duration_min DECIMAL(4,1),
    calories_per_min DECIMAL(5,2),
    video_url VARCHAR(255),
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE
);

-- Тренировочные планы
CREATE TABLE workout_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    trainer_id INT NULL,
    name VARCHAR(100) DEFAULT 'Мое тренування',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    scheduled_date DATE,
    total_duration_min DECIMAL(5,1),
    status ENUM('planned', 'in_progress', 'completed', 'cancelled') DEFAULT 'planned',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Связь плана с упражнениями
CREATE TABLE plan_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT,
    exercise_id INT,
    sets INT DEFAULT 3,
    reps INT,
    weight_kg DECIMAL(5,2),
    order_num INT DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (plan_id) REFERENCES workout_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id)
);

-- Логи тренировок (активность)
CREATE TABLE workout_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    plan_id INT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    heart_rate_avg INT,
    calories_burned DECIMAL(8,2),
    duration_min DECIMAL(5,1),
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES workout_plans(id) ON DELETE SET NULL
);

-- Логи пульса
CREATE TABLE heart_rate_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hr_rest INT,
    hr_current INT,
    hr_max INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Связь тренера и клиентов
CREATE TABLE trainer_clients (
    trainer_id INT,
    client_id INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
    PRIMARY KEY (trainer_id, client_id),
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Вставляем начальные данные
INSERT INTO medical_restrictions (name) VALUES 
('Травма коліна'),
('Травма спини'),
('Гіпертонія'),
('Проблеми з серцем'),
('Діабет'),
('Астма');

INSERT INTO exercises (name, muscle_group, difficulty, duration_min, calories_per_min) VALUES
('Присідання', 'Ноги', 'beginner', 3, 5.0),
('Віджимання', 'Груди', 'beginner', 2, 4.5),
('Планка', 'Кор', 'intermediate', 1, 6.0),
('Випади', 'Ноги', 'intermediate', 3, 5.5),
('Берпі', 'Все тіло', 'advanced', 1, 8.0),
('Скручування', 'Прес', 'beginner', 2, 3.5);

-- Проверка структуры таблицы
DESCRIBE user_profiles;

-- Если поле отсутствует - добавляем
ALTER TABLE user_profiles ADD COLUMN profile_completed TINYINT(1) DEFAULT 0 AFTER medical_notes;

-- Обновление существующих записей (если профиль заполнен)
UPDATE user_profiles SET profile_completed = 1 WHERE age IS NOT NULL AND weight IS NOT NULL;

-- Администратор не создается с паролем по умолчанию. Создайте учетную запись вручную и используйте password_hash().

-- Таблица достижений
CREATE TABLE achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'bi-trophy',
    category ENUM('workout', 'streak', 'strength', 'health', 'social', 'special') DEFAULT 'workout',
    requirement_type ENUM('workouts', 'streak', 'calories', 'exercises', 'weight', 'special') NOT NULL,
    requirement_value INT NOT NULL,
    points INT DEFAULT 10,
    badge_color VARCHAR(20) DEFAULT 'gold',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица достижений пользователя
CREATE TABLE user_achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progress INT DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_achievement (user_id, achievement_id)
);

-- Таблица уровней пользователя
CREATE TABLE user_levels (
    user_id INT PRIMARY KEY,
    level INT DEFAULT 1,
    experience INT DEFAULT 0,
    next_level_experience INT DEFAULT 100,
    total_experience INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица статистики пользователя для геймификации
CREATE TABLE user_gamification_stats (
    user_id INT PRIMARY KEY,
    total_workouts INT DEFAULT 0,
    total_calories INT DEFAULT 0,
    current_streak INT DEFAULT 0,
    max_streak INT DEFAULT 0,
    total_exercises_completed INT DEFAULT 0,
    last_workout_date DATE NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Вставляем начальные достижения
INSERT INTO achievements (code, name, description, icon, category, requirement_type, requirement_value, points, badge_color) VALUES
-- Достижения по тренировкам
('first_workout', 'Перше тренування', 'Виконайте перше тренування', 'bi-star', 'workout', 'workouts', 1, 5, 'bronze'),
('workout_5', '5 тренувань', 'Виконайте 5 тренувань', 'bi-emoji-smile', 'workout', 'workouts', 5, 10, 'bronze'),
('workout_25', '25 тренувань', 'Виконайте 25 тренувань', 'bi-fire', 'workout', 'workouts', 25, 20, 'silver'),
('workout_100', '100 тренувань', 'Виконайте 100 тренувань', 'bi-trophy', 'workout', 'workouts', 100, 50, 'gold'),
('workout_365', '365 тренувань', 'Виконайте 365 тренувань (рік)', 'bi-gem', 'workout', 'workouts', 365, 100, 'platinum'),

-- Достижения по сериям
('streak_3', '3 дні поспіль', 'Тренуйтеся 3 дні поспіль', 'bi-calendar-check', 'streak', 'streak', 3, 10, 'bronze'),
('streak_7', '7 днів поспіль', 'Тренуйтеся 7 днів поспіль', 'bi-calendar2-week', 'streak', 'streak', 7, 20, 'silver'),
('streak_30', '30 днів поспіль', 'Тренуйтеся 30 днів поспіль', 'bi-calendar2-month', 'streak', 'streak', 30, 50, 'gold'),
('streak_100', '100 днів поспіль', 'Тренуйтеся 100 днів поспіль', 'bi-calendar2-range', 'streak', 'streak', 100, 100, 'platinum'),

-- Достижения по калориям
('calories_1000', '1000 калорій', 'Спаліть 1000 калорій загалом', 'bi-fire', 'strength', 'calories', 1000, 10, 'bronze'),
('calories_10000', '10000 калорій', 'Спаліть 10000 калорій загалом', 'bi-fire', 'strength', 'calories', 10000, 25, 'silver'),
('calories_100000', '100000 калорій', 'Спаліть 100000 калорій загалом', 'bi-fire', 'strength', 'calories', 100000, 75, 'gold'),

-- Достижения по упражнениям
('exercises_50', '50 вправ', 'Виконайте 50 вправ', 'bi-dumbbell', 'strength', 'exercises', 50, 10, 'bronze'),
('exercises_500', '500 вправ', 'Виконайте 500 вправ', 'bi-dumbbell', 'strength', 'exercises', 500, 30, 'silver'),
('exercises_5000', '5000 вправ', 'Виконайте 5000 вправ', 'bi-dumbbell', 'strength', 'exercises', 5000, 80, 'gold'),

-- Специальные достижения
('early_bird', 'Рання пташка', 'Виконайте тренування до 7:00', 'bi-sunrise', 'special', 'special', 1, 15, 'bronze'),
('night_owl', 'Нічна сова', 'Виконайте тренування після 22:00', 'bi-moon', 'special', 'special', 1, 15, 'bronze'),
('perfect_workout', 'Ідеальне тренування', 'Виконайте всі вправи тренування', 'bi-check-circle', 'special', 'special', 1, 20, 'gold'),
('consistency', 'Послідовність', 'Повністю виконайте 10 тренувань', 'bi-award', 'special', 'special', 10, 30, 'silver');

-- Таблица для хранения токенов Google Fit
CREATE TABLE IF NOT EXISTS user_google_fit_tokens (
    user_id INT PRIMARY KEY,
    access_token TEXT NOT NULL,
    refresh_token TEXT NOT NULL,
    token_expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица для синхронизированных данных активности
CREATE TABLE IF NOT EXISTS user_activity_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_date DATE NOT NULL,
    steps INT DEFAULT 0,
    calories INT DEFAULT 0,
    distance FLOAT DEFAULT 0,
    active_minutes INT DEFAULT 0,
    source ENUM('manual', 'google_fit', 'apple_health') DEFAULT 'manual',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_date (user_id, activity_date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица логов синхронизации
CREATE TABLE IF NOT EXISTS google_fit_sync_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sync_start DATETIME NOT NULL,
    sync_end DATETIME NOT NULL,
    data_summary TEXT,
    sync_status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Добавляем поля для источника данных в существующие таблицы
ALTER TABLE heart_rate_logs ADD COLUMN source VARCHAR(50) DEFAULT 'manual';
ALTER TABLE heart_rate_logs ADD COLUMN hr_avg INT DEFAULT NULL;

-- Добавляем поля для тренера в пользователей
ALTER TABLE users ADD COLUMN specialty VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN experience_years INT DEFAULT 0;
ALTER TABLE users ADD COLUMN bio TEXT NULL;
ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN is_verified BOOLEAN DEFAULT FALSE;

-- Таблица клиентов тренера (уже есть, добавляем поля)
ALTER TABLE trainer_clients ADD COLUMN notes TEXT NULL;
ALTER TABLE trainer_clients ADD COLUMN goals TEXT NULL;
ALTER TABLE trainer_clients ADD COLUMN health_conditions TEXT NULL;
ALTER TABLE trainer_clients ADD COLUMN last_visit DATETIME NULL;

-- Таблица программ тренировок
CREATE TABLE trainer_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    duration_weeks INT DEFAULT 4,
    sessions_per_week INT DEFAULT 3,
    is_public BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица упражнений в программе
CREATE TABLE program_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    exercise_id INT NOT NULL,
    day INT DEFAULT 1,
    sets INT DEFAULT 3,
    reps INT DEFAULT 10,
    rest_seconds INT DEFAULT 60,
    notes TEXT,
    order_num INT DEFAULT 0,
    FOREIGN KEY (program_id) REFERENCES trainer_programs(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id)
);

-- Таблица назначенных программ клиентам
CREATE TABLE client_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    program_id INT NOT NULL,
    trainer_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'paused', 'completed', 'cancelled') DEFAULT 'active',
    progress INT DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES trainer_programs(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица сообщений тренер-клиент
CREATE TABLE trainer_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица расписания тренера
CREATE TABLE trainer_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    client_id INT NULL,
    day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
    start_time TIME,
    end_time TIME,
    is_recurring BOOLEAN DEFAULT TRUE,
    date DATE NULL,
    status ENUM('available', 'booked', 'completed', 'cancelled') DEFAULT 'available',
    notes TEXT,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Тестовые учетные записи тренеров удалены. Создавайте тренеров через приложение или безопасный админ-процесс.

-- Таблица уведомлений
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'info',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    icon VARCHAR(50) DEFAULT 'bi-bell',
    link VARCHAR(255) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
);

-- Системные уведомления (шаблоны)
CREATE TABLE IF NOT EXISTS notification_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    icon VARCHAR(50) DEFAULT 'bi-bell',
    type VARCHAR(50) DEFAULT 'info',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Вставляем шаблоны уведомлений
INSERT INTO notification_templates (code, title, message, icon, type) VALUES
('workout_completed', '🎉 Тренування завершено!', 'Ви успішно завершили тренування "{workout_name}". Ви спалили {calories} калорій!', 'bi-check-circle', 'success'),
('workout_created', '📋 Нове тренування', 'Ви створили нове тренування "{workout_name}"', 'bi-calendar-plus', 'info'),
('achievement_unlocked', '🏆 Нове досягнення!', 'Ви отримали досягнення "{achievement_name}"!', 'bi-trophy', 'success'),
('streak_milestone', '🔥 Серія!', 'Ви тренуєтесь {streak} днів поспіль! Так тримати!', 'bi-fire', 'warning'),
('level_up', '⬆️ Новий рівень!', 'Вітаємо! Ви досягли рівня {level}!', 'bi-arrow-up-circle', 'success'),
('new_client', '👤 Новий клієнт', 'Користувач "{client_name}" став вашим клієнтом', 'bi-person-plus', 'info'),
('message_received', '💬 Нове повідомлення', 'Ви отримали повідомлення від {sender_name}', 'bi-chat', 'info'),
('program_assigned', '📋 Нова програма', 'Вам призначено програму "{program_name}"', 'bi-file-text', 'info'),
('weight_milestone', '⚖️ Прогрес у вазі', 'Ви досягли цільової ваги!', 'bi-weight-scale', 'success'),
('reminder', '⏰ Нагадування', 'Не забудьте про тренування сьогодні!', 'bi-clock', 'warning');

-- Таблица настроек уведомлений тренера
CREATE TABLE IF NOT EXISTS trainer_notification_settings (
    trainer_id INT PRIMARY KEY,
    email_notifications BOOLEAN DEFAULT TRUE,
    workout_reminders BOOLEAN DEFAULT TRUE,
    achievement_notifications BOOLEAN DEFAULT TRUE,
    message_notifications BOOLEAN DEFAULT TRUE,
    client_activity BOOLEAN DEFAULT TRUE,
    new_clients BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Добавляем новые поля в workout_plans
ALTER TABLE workout_plans 
ADD COLUMN difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'intermediate',
ADD COLUMN focus VARCHAR(50) DEFAULT 'general',
ADD COLUMN calories_burned INT DEFAULT 0,
ADD COLUMN rating INT DEFAULT 0;

-- Таблица для шаблонов тренировок
CREATE TABLE workout_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'intermediate',
    focus VARCHAR(50) DEFAULT 'general',
    duration_min INT DEFAULT 30,
    is_public BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Таблица упражнений в шаблоне
CREATE TABLE template_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    exercise_id INT NOT NULL,
    sets INT DEFAULT 3,
    reps INT DEFAULT 10,
    rest_seconds INT DEFAULT 60,
    order_num INT DEFAULT 0,
    FOREIGN KEY (template_id) REFERENCES workout_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id)
);

-- Таблица для заметок к тренировке
CREATE TABLE workout_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workout_id INT NOT NULL,
    user_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workout_id) REFERENCES workout_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Добавляем начальные шаблоны
INSERT INTO workout_templates (name, description, difficulty, focus, duration_min, is_public) VALUES
('Силова тренировка для начинающих', 'Базовые упражнения для развития силы', 'beginner', 'strength', 30, 1),
('Кардио-тренировка', 'Интенсивная кардио-тренировка для сжигания жира', 'intermediate', 'cardio', 25, 1),
('Функциональный тренинг', 'Развитие выносливости и координации', 'intermediate', 'functional', 35, 1),
('Тренировка для пресса', 'Комплекс упражнений на мышцы кора', 'beginner', 'core', 20, 1),
('Продвинутая силовая', 'Интенсивная силовая тренировка', 'advanced', 'strength', 45, 1);

-- Таблица чатов
CREATE TABLE IF NOT EXISTS chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_chat (user1_id, user2_id),
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица сообщений
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица чатов
CREATE TABLE IF NOT EXISTS chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_chat (user1_id, user2_id),
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица сообщений
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);