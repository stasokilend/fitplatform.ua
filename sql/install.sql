CREATE DATABASE IF NOT EXISTS fitplatform;
USE fitplatform;

-- Таблиця: users (користувачі)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'trainer', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблиця: user_profiles (профілі користувачів)
CREATE TABLE user_profiles (
    user_id INT PRIMARY KEY,
    age INT NOT NULL CHECK (age BETWEEN 10 AND 100),
    weight DECIMAL(5,2) NOT NULL CHECK (weight > 0),
    height INT NOT NULL CHECK (height BETWEEN 50 AND 300),
    gender ENUM('male', 'female') NOT NULL,
    fitness_level TINYINT NOT NULL CHECK (fitness_level IN (1,2,3)),
    goals JSON NOT NULL, -- {"lose_weight": 0.7, "gain_muscle": 0.2, "endurance": 0.1}
    medical_restrictions JSON, -- ["hernia", "hypertension"]
    bmr DECIMAL(8,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблиця: exercises (база вправ)
CREATE TABLE exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    duration_minutes DECIMAL(4,1) NOT NULL, -- t_i
    met_value DECIMAL(4,2) NOT NULL, -- MET (енерговитрати)
    muscle_groups JSON NOT NULL, -- {"legs": 0.8, "core": 0.3, "arms": 0.2}
    difficulty TINYINT NOT NULL CHECK (difficulty IN (1,2,3)),
    contraindications JSON, -- ["hernia", "hypertension"]
    video_url VARCHAR(255),
    equipment VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблиця: workout_plans (згенеровані плани)
CREATE TABLE workout_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) DEFAULT 'Моє тренування',
    total_duration INT NOT NULL, -- загальна тривалість у хвилинах
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблиця: plan_exercises (вправи в плані)
CREATE TABLE plan_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    exercise_id INT NOT NULL,
    sets INT NOT NULL DEFAULT 3,
    reps INT NOT NULL DEFAULT 12,
    rest_seconds INT NOT NULL DEFAULT 60,
    order_index INT NOT NULL,
    FOREIGN KEY (plan_id) REFERENCES workout_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
);

-- Таблиця: training_sessions (виконані тренування)
CREATE TABLE training_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP,
    duration_minutes INT,
    avg_hr INT,
    max_hr INT,
    calories_burned DECIMAL(8,2),
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES workout_plans(id) ON DELETE SET NULL
);

-- Таблиця: heart_rate_logs (логи пульсу)
CREATE TABLE heart_rate_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    timestamp DATETIME NOT NULL,
    bpm INT NOT NULL CHECK (bpm BETWEEN 30 AND 220),
    FOREIGN KEY (session_id) REFERENCES training_sessions(id) ON DELETE CASCADE
);

-- Додаємо поле role, якщо його немає
ALTER TABLE users ADD COLUMN role ENUM('user', 'trainer', 'admin') DEFAULT 'user';

-- ПОЧАТКОВІ ДАНІ (базові вправи)
INSERT INTO exercises (name, description, duration_minutes, met_value, muscle_groups, difficulty, contraindications, video_url, equipment) VALUES
('Присідання', 'Класичні присідання з вагою власного тіла', 0.5, 5.0, '{"legs": 0.9, "core": 0.4}', 1, '["knee_injury"]', 'https://www.youtube.com/embed/example1', NULL),
('Віджимання', 'Віджимання від підлоги', 0.5, 4.5, '{"chest": 0.8, "arms": 0.6, "core": 0.3}', 2, '["shoulder_injury", "wrist_injury"]', 'https://www.youtube.com/embed/example2', NULL),
('Планка', 'Статична вправа на прес', 1.0, 3.0, '{"core": 0.9, "shoulders": 0.3}', 1, '["hernia"]', NULL, NULL),
('Біг на місці', 'Інтенсивний біг на місці', 1.0, 8.0, '{"legs": 0.7, "cardiovascular": 0.9}', 1, '["knee_injury", "hypertension"]', NULL, NULL),
('Випади', 'Випади вперед з вагою тіла', 0.5, 5.5, '{"legs": 0.8, "glutes": 0.7}', 2, '["knee_injury"]', NULL, NULL),
('Підтягування', 'Підтягування на турніку', 0.5, 6.0, '{"back": 0.9, "arms": 0.7}', 3, '["shoulder_injury"]', NULL, 'турнік'),
('Скручування', 'Скручування на прес лежачи', 0.5, 3.5, '{"core": 0.9}', 1, '["hernia"]', NULL, NULL),
('Бурпі', 'Інтенсивна вправа на все тіло', 0.5, 9.0, '{"legs": 0.6, "chest": 0.5, "cardiovascular": 0.9}', 3, '["hypertension", "knee_injury"]', NULL, NULL),
('Присідання з гантелями', 'Присідання з гантелями в руках', 0.5, 6.5, '{"legs": 0.9, "arms": 0.4}', 2, '["knee_injury"]', NULL, 'гантелі'),
('Жим гантелей лежачи', 'Жим гантелей на горизонтальній лаві', 0.5, 5.0, '{"chest": 0.9, "arms": 0.6}', 2, '["shoulder_injury"]', NULL, 'гантелі,лава');