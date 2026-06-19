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
