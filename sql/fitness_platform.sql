-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.4:3306
-- Время создания: Июн 22 2026 г., 11:43
-- Версия сервера: 8.4.8
-- Версия PHP: 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `fitness_platform`
--

-- --------------------------------------------------------

--
-- Структура таблицы `achievements`
--

CREATE TABLE `achievements` (
  `id` int NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `icon` varchar(50) DEFAULT 'bi-trophy',
  `category` enum('workout','streak','strength','health','social','special') DEFAULT 'workout',
  `requirement_type` enum('workouts','streak','calories','exercises','weight','special') NOT NULL,
  `requirement_value` int NOT NULL,
  `points` int DEFAULT '10',
  `badge_color` varchar(20) DEFAULT 'gold',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `image_url` varchar(255) DEFAULT NULL,
  `rarity` enum('common','uncommon','rare','epic','legendary') DEFAULT 'common',
  `category_icon` varchar(50) DEFAULT 'bi-trophy',
  `order_num` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `achievements`
--

INSERT INTO `achievements` (`id`, `code`, `name`, `description`, `icon`, `category`, `requirement_type`, `requirement_value`, `points`, `badge_color`, `is_active`, `created_at`, `image_url`, `rarity`, `category_icon`, `order_num`) VALUES
(1, 'first_workout', 'Перше тренування', 'Виконайте перше тренування', 'bi-star', 'workout', 'workouts', 1, 5, 'bronze', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(2, 'workout_5', '5 тренувань', 'Виконайте 5 тренувань', 'bi-emoji-smile', 'workout', 'workouts', 5, 10, 'bronze', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(3, 'workout_25', '25 тренувань', 'Виконайте 25 тренувань', 'bi-fire', 'workout', 'workouts', 25, 20, 'silver', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(4, 'workout_100', '100 тренувань', 'Виконайте 100 тренувань', 'bi-trophy', 'workout', 'workouts', 100, 50, 'gold', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(5, 'workout_365', '365 тренувань', 'Виконайте 365 тренувань (рік)', 'bi-gem', 'workout', 'workouts', 365, 100, 'platinum', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(6, 'streak_3', '3 дні поспіль', 'Тренуйтеся 3 дні поспіль', 'bi-calendar-check', 'streak', 'streak', 3, 10, 'bronze', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(7, 'streak_7', '7 днів поспіль', 'Тренуйтеся 7 днів поспіль', 'bi-calendar2-week', 'streak', 'streak', 7, 20, 'silver', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(8, 'streak_30', '30 днів поспіль', 'Тренуйтеся 30 днів поспіль', 'bi-calendar2-month', 'streak', 'streak', 30, 50, 'gold', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(9, 'streak_100', '100 днів поспіль', 'Тренуйтеся 100 днів поспіль', 'bi-calendar2-range', 'streak', 'streak', 100, 100, 'platinum', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(10, 'calories_1000', '1000 калорій', 'Спаліть 1000 калорій загалом', 'bi-fire', 'strength', 'calories', 1000, 10, 'bronze', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(11, 'calories_10000', '10000 калорій', 'Спаліть 10000 калорій загалом', 'bi-fire', 'strength', 'calories', 10000, 25, 'silver', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(12, 'calories_100000', '100000 калорій', 'Спаліть 100000 калорій загалом', 'bi-fire', 'strength', 'calories', 100000, 75, 'gold', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(13, 'exercises_50', '50 вправ', 'Виконайте 50 вправ', 'bi-dumbbell', 'strength', 'exercises', 50, 10, 'bronze', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(14, 'exercises_500', '500 вправ', 'Виконайте 500 вправ', 'bi-dumbbell', 'strength', 'exercises', 500, 30, 'silver', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(15, 'exercises_5000', '5000 вправ', 'Виконайте 5000 вправ', 'bi-dumbbell', 'strength', 'exercises', 5000, 80, 'gold', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(16, 'early_bird', 'Рання пташка', 'Виконайте тренування до 7:00', 'bi-sunrise', 'special', 'special', 1, 15, 'bronze', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(17, 'night_owl', 'Нічна сова', 'Виконайте тренування після 22:00', 'bi-moon', 'special', 'special', 1, 15, 'bronze', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(18, 'perfect_workout', 'Ідеальне тренування', 'Виконайте всі вправи тренування', 'bi-check-circle', 'special', 'special', 1, 20, 'gold', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0),
(19, 'consistency', 'Послідовність', 'Повністю виконайте 10 тренувань', 'bi-award', 'special', 'special', 10, 30, 'silver', 1, '2026-06-19 21:57:38', NULL, 'common', 'bi-trophy', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `chats`
--

CREATE TABLE `chats` (
  `id` int NOT NULL,
  `user1_id` int NOT NULL,
  `user2_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `chats`
--

INSERT INTO `chats` (`id`, `user1_id`, `user2_id`, `created_at`, `updated_at`) VALUES
(1, 7, 3, '2026-06-20 13:47:00', '2026-06-20 17:19:29');

-- --------------------------------------------------------

--
-- Структура таблицы `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int NOT NULL,
  `chat_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `chat_id`, `sender_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 7, 'hhh', 1, '2026-06-20 13:50:20'),
(2, 1, 3, 'asdasdasd', 1, '2026-06-20 13:50:50'),
(3, 1, 3, 'https://fitplatform.ua/dashboard.php?page=program-detail&id=3', 1, '2026-06-20 17:19:29');

-- --------------------------------------------------------

--
-- Структура таблицы `client_programs`
--

CREATE TABLE `client_programs` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `program_id` int NOT NULL,
  `trainer_id` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','paused','completed','cancelled') DEFAULT 'active',
  `progress` int DEFAULT '0',
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `exercises`
--

CREATE TABLE `exercises` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `muscle_group` varchar(50) DEFAULT NULL,
  `equipment` varchar(100) DEFAULT NULL,
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `duration_min` decimal(4,1) DEFAULT NULL,
  `calories_per_min` decimal(5,2) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `exercises`
--

INSERT INTO `exercises` (`id`, `name`, `description`, `muscle_group`, `equipment`, `difficulty`, `duration_min`, `calories_per_min`, `video_url`, `image_url`, `is_active`) VALUES
(1, 'Присідання', NULL, 'Ноги', NULL, 'beginner', 3.0, 5.00, NULL, NULL, 1),
(2, 'Віджимання', NULL, 'Груди', NULL, 'beginner', 2.0, 4.50, NULL, NULL, 1),
(3, 'Планка', NULL, 'Кор', NULL, 'intermediate', 1.0, 6.00, NULL, NULL, 1),
(4, 'Випади', NULL, 'Ноги', NULL, 'intermediate', 3.0, 5.50, NULL, NULL, 1),
(5, 'Берпі', NULL, 'Все тіло', NULL, 'advanced', 1.0, 8.00, NULL, NULL, 1),
(6, 'Скручування', NULL, 'Прес', NULL, 'beginner', 2.0, 3.50, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `google_fit_sync_log`
--

CREATE TABLE `google_fit_sync_log` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `sync_start` datetime NOT NULL,
  `sync_end` datetime NOT NULL,
  `data_summary` text,
  `sync_status` enum('pending','completed','failed') DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `heart_rate_logs`
--

CREATE TABLE `heart_rate_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `recorded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `hr_rest` int DEFAULT NULL,
  `hr_current` int DEFAULT NULL,
  `hr_max` int DEFAULT NULL,
  `source` varchar(50) DEFAULT 'manual',
  `hr_avg` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `heart_rate_logs`
--

INSERT INTO `heart_rate_logs` (`id`, `user_id`, `recorded_at`, `hr_rest`, `hr_current`, `hr_max`, `source`, `hr_avg`) VALUES
(1, 3, '2026-06-19 21:49:23', 70, 40, 193, 'manual', NULL),
(2, 3, '2026-06-19 21:52:23', 70, 50, 193, 'manual', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `medical_restrictions`
--

CREATE TABLE `medical_restrictions` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'bi-heart-pulse'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `medical_restrictions`
--

INSERT INTO `medical_restrictions` (`id`, `name`, `icon`) VALUES
(1, 'Травма коліна', 'bi-heart-pulse'),
(2, 'Травма спини', 'bi-heart-pulse'),
(3, 'Гіпертонія', 'bi-heart-pulse'),
(4, 'Проблеми з серцем', 'bi-heart-pulse'),
(5, 'Діабет', 'bi-heart-pulse'),
(6, 'Астма', 'bi-heart-pulse');

-- --------------------------------------------------------

--
-- Структура таблицы `plan_exercises`
--

CREATE TABLE `plan_exercises` (
  `id` int NOT NULL,
  `plan_id` int NOT NULL,
  `exercise_id` int NOT NULL,
  `sets` int DEFAULT '3',
  `reps` int DEFAULT '10',
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `rest_seconds` int DEFAULT '60',
  `duration_min` decimal(4,1) DEFAULT NULL,
  `order_num` int DEFAULT '0',
  `is_completed` tinyint(1) DEFAULT '0',
  `completed_at` datetime DEFAULT NULL,
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `plan_exercises`
--

INSERT INTO `plan_exercises` (`id`, `plan_id`, `exercise_id`, `sets`, `reps`, `weight_kg`, `rest_seconds`, `duration_min`, `order_num`, `is_completed`, `completed_at`, `notes`) VALUES
(1, 1, 3, 3, 10, NULL, 60, NULL, 0, 1, NULL, NULL),
(2, 1, 4, 3, 10, NULL, 60, NULL, 1, 1, NULL, NULL),
(3, 1, 1, 3, 10, NULL, 60, NULL, 2, 1, NULL, NULL),
(4, 1, 2, 3, 10, NULL, 60, NULL, 3, 1, NULL, NULL),
(5, 1, 6, 3, 10, NULL, 60, NULL, 4, 1, NULL, NULL),
(6, 2, 2, 3, 10, NULL, 60, NULL, 0, 0, NULL, NULL),
(7, 2, 4, 3, 10, NULL, 60, NULL, 1, 1, NULL, NULL),
(8, 2, 1, 3, 10, NULL, 60, NULL, 2, 1, NULL, NULL),
(9, 2, 3, 3, 10, NULL, 60, NULL, 3, 1, NULL, NULL),
(10, 2, 6, 3, 10, NULL, 60, NULL, 4, 1, NULL, NULL),
(11, 3, 2, 3, 10, NULL, 60, NULL, 0, 0, NULL, NULL),
(12, 3, 1, 3, 10, NULL, 60, NULL, 1, 0, NULL, NULL),
(13, 3, 6, 3, 10, NULL, 60, NULL, 2, 0, NULL, NULL),
(14, 3, 4, 3, 10, NULL, 60, NULL, 3, 0, NULL, NULL),
(15, 4, 5, 3, 6, NULL, 60, NULL, 0, 0, NULL, NULL),
(16, 4, 2, 3, 10, NULL, 60, NULL, 1, 0, NULL, NULL),
(17, 4, 3, 3, 10, NULL, 60, NULL, 2, 0, NULL, NULL),
(18, 4, 4, 3, 10, NULL, 60, NULL, 3, 0, NULL, NULL),
(19, 4, 1, 3, 10, NULL, 60, NULL, 4, 0, NULL, NULL),
(20, 4, 6, 3, 10, NULL, 60, NULL, 5, 0, NULL, NULL),
(21, 5, 5, 3, 6, NULL, 60, NULL, 0, 0, NULL, NULL),
(22, 5, 2, 3, 10, NULL, 60, NULL, 1, 0, NULL, NULL),
(23, 5, 3, 3, 10, NULL, 60, NULL, 2, 0, NULL, NULL),
(24, 5, 4, 3, 10, NULL, 60, NULL, 3, 0, NULL, NULL),
(25, 5, 1, 3, 10, NULL, 60, NULL, 4, 0, NULL, NULL),
(26, 5, 6, 3, 10, NULL, 60, NULL, 5, 0, NULL, NULL),
(27, 6, 5, 3, 6, NULL, 60, NULL, 0, 0, NULL, NULL),
(28, 6, 2, 3, 10, NULL, 60, NULL, 1, 0, NULL, NULL),
(29, 6, 3, 3, 10, NULL, 60, NULL, 2, 0, NULL, NULL),
(30, 6, 4, 3, 10, NULL, 60, NULL, 3, 0, NULL, NULL),
(31, 6, 1, 3, 10, NULL, 60, NULL, 4, 0, NULL, NULL),
(32, 6, 6, 3, 10, NULL, 60, NULL, 5, 0, NULL, NULL),
(33, 7, 5, 3, 6, NULL, 60, NULL, 0, 0, NULL, NULL),
(34, 7, 2, 3, 10, NULL, 60, NULL, 1, 0, NULL, NULL),
(35, 7, 3, 3, 10, NULL, 60, NULL, 2, 0, NULL, NULL),
(36, 7, 4, 3, 10, NULL, 60, NULL, 3, 0, NULL, NULL),
(37, 7, 1, 3, 10, NULL, 60, NULL, 4, 0, NULL, NULL),
(38, 8, 5, 3, 6, NULL, 60, NULL, 0, 0, NULL, NULL),
(39, 8, 2, 3, 10, NULL, 60, NULL, 1, 0, NULL, NULL),
(40, 8, 3, 3, 10, NULL, 60, NULL, 2, 0, NULL, NULL),
(41, 8, 4, 3, 10, NULL, 60, NULL, 3, 0, NULL, NULL),
(42, 8, 1, 3, 10, NULL, 60, NULL, 4, 0, NULL, NULL),
(43, 9, 5, 3, 6, NULL, 60, NULL, 0, 0, NULL, NULL),
(44, 9, 2, 3, 10, NULL, 60, NULL, 1, 0, NULL, NULL),
(45, 9, 3, 3, 10, NULL, 60, NULL, 2, 0, NULL, NULL),
(46, 9, 4, 3, 10, NULL, 60, NULL, 3, 0, NULL, NULL),
(47, 9, 1, 3, 10, NULL, 60, NULL, 4, 0, NULL, NULL),
(48, 9, 6, 3, 10, NULL, 60, NULL, 5, 0, NULL, NULL),
(49, 10, 5, 3, 6, NULL, 60, NULL, 0, 0, NULL, NULL),
(50, 10, 2, 3, 10, NULL, 60, NULL, 1, 0, NULL, NULL),
(51, 10, 3, 3, 10, NULL, 60, NULL, 2, 0, NULL, NULL),
(52, 10, 4, 3, 10, NULL, 60, NULL, 3, 0, NULL, NULL),
(53, 10, 1, 3, 10, NULL, 60, NULL, 4, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `program_exercises`
--

CREATE TABLE `program_exercises` (
  `id` int NOT NULL,
  `program_id` int NOT NULL,
  `exercise_id` int NOT NULL,
  `day` int DEFAULT '1',
  `sets` int DEFAULT '3',
  `reps` int DEFAULT '10',
  `rest_seconds` int DEFAULT '60',
  `notes` text,
  `order_num` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `program_exercises`
--

INSERT INTO `program_exercises` (`id`, `program_id`, `exercise_id`, `day`, `sets`, `reps`, `rest_seconds`, `notes`, `order_num`) VALUES
(1, 2, 4, 1, 3, 10, 60, NULL, 0),
(15, 3, 4, 1, 3, 10, 60, '', 0),
(16, 3, 6, 1, 3, 10, 60, '', 1),
(17, 3, 5, 3, 3, 10, 60, '', 2);

-- --------------------------------------------------------

--
-- Структура таблицы `program_likes`
--

CREATE TABLE `program_likes` (
  `id` int NOT NULL,
  `program_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `program_media`
--

CREATE TABLE `program_media` (
  `id` int NOT NULL,
  `program_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` enum('image','video','document') DEFAULT 'image',
  `file_size` int DEFAULT '0',
  `order_num` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `template_exercises`
--

CREATE TABLE `template_exercises` (
  `id` int NOT NULL,
  `template_id` int NOT NULL,
  `exercise_id` int NOT NULL,
  `sets` int DEFAULT '3',
  `reps` int DEFAULT '10',
  `rest_seconds` int DEFAULT '60',
  `duration_min` decimal(4,1) DEFAULT NULL,
  `order_num` int DEFAULT '0',
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `trainer_clients`
--

CREATE TABLE `trainer_clients` (
  `trainer_id` int NOT NULL,
  `client_id` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','active','inactive') DEFAULT 'pending',
  `notes` text,
  `goals` text,
  `health_conditions` text,
  `last_visit` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `trainer_notification_settings`
--

CREATE TABLE `trainer_notification_settings` (
  `trainer_id` int NOT NULL,
  `email_notifications` tinyint(1) DEFAULT '1',
  `workout_reminders` tinyint(1) DEFAULT '1',
  `achievement_notifications` tinyint(1) DEFAULT '1',
  `message_notifications` tinyint(1) DEFAULT '1',
  `client_activity` tinyint(1) DEFAULT '1',
  `new_clients` tinyint(1) DEFAULT '1',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `trainer_programs`
--

CREATE TABLE `trainer_programs` (
  `id` int NOT NULL,
  `trainer_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `duration_weeks` int DEFAULT '4',
  `sessions_per_week` int DEFAULT '3',
  `is_public` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `content` longtext,
  `cover_image` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `duration_minutes` int DEFAULT '0',
  `category` varchar(50) DEFAULT 'general',
  `views` int DEFAULT '0',
  `likes` int DEFAULT '0',
  `is_featured` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `trainer_programs`
--

INSERT INTO `trainer_programs` (`id`, `trainer_id`, `name`, `description`, `difficulty`, `duration_weeks`, `sessions_per_week`, `is_public`, `is_active`, `created_at`, `updated_at`, `content`, `cover_image`, `video_url`, `duration_minutes`, `category`, `views`, `likes`, `is_featured`) VALUES
(2, 3, 'фывыфв', 'фывфывфывфыв', 'intermediate', 4, 3, 1, 1, '2026-06-20 14:45:44', '2026-06-20 14:45:44', NULL, NULL, NULL, 0, 'general', 0, 0, 0),
(3, 3, 'фывыфв', '<p><iframe style=\"width: 350px; height: 197px;\" title=\"YouTube video player\" src=\"https://www.youtube.com/embed/VFVOJMJMqpE?si=Px8lYCETaF1RaSi-\" width=\"350\" height=\"197\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" allowfullscreen=\"allowfullscreen\"></iframe></p>', 'intermediate', 4, 3, 1, 1, '2026-06-20 15:02:30', '2026-06-20 15:02:30', NULL, NULL, NULL, 0, 'general', 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `trainer_schedule`
--

CREATE TABLE `trainer_schedule` (
  `id` int NOT NULL,
  `trainer_id` int NOT NULL,
  `client_id` int DEFAULT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT '1',
  `date` date DEFAULT NULL,
  `status` enum('available','booked','completed','cancelled') DEFAULT 'available',
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('user','trainer','admin') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `specialty` varchar(255) DEFAULT NULL,
  `experience_years` int DEFAULT '0',
  `bio` text,
  `avatar_url` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `full_name`, `role`, `is_active`, `created_at`, `updated_at`, `specialty`, `experience_years`, `bio`, `avatar_url`, `is_verified`) VALUES
(1, 'stasokilend@gmail.com', '$2y$12$TGh6k0AKLayXdOTQmUTrTe/ekig0mEUrWg8pEzRwpqICN7TK0hti.', 'stanislav', 'admin', 1, '2026-06-19 19:49:08', '2026-06-19 20:30:04', NULL, 0, NULL, NULL, 0),
(2, 'admin@fitplatform.ua', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Адміністратор', 'admin', 1, '2026-06-19 20:33:16', '2026-06-19 20:33:16', NULL, 0, NULL, NULL, 0),
(3, 'staso@gmail.com', '$2y$12$y8XbJvaAENGFnDwMXfC9cujLhfqgq1404cytTVT9XjEUtAkKVD/Sy', 'stasik', 'trainer', 1, '2026-06-19 20:42:34', '2026-06-20 14:38:02', NULL, 0, NULL, NULL, 0),
(5, 'trainer@fitplatform.ua', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Олексій Тренер', 'trainer', 1, '2026-06-20 07:18:17', '2026-06-20 07:18:17', 'Силові тренування, Функціональний фітнес', 5, NULL, NULL, 1),
(7, 'brutal@rw.com', '$2y$12$kwDSr8ySMoYG0TUix01RBe6bEeCmlddHlQVOgl1WdG4LnB.UxFGOu', 'brutal', 'user', 1, '2026-06-20 10:57:48', '2026-06-20 13:46:25', NULL, 0, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `user_achievements`
--

CREATE TABLE `user_achievements` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `achievement_id` int NOT NULL,
  `unlocked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `progress` int DEFAULT '0',
  `is_completed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user_activity_data`
--

CREATE TABLE `user_activity_data` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `activity_date` date NOT NULL,
  `steps` int DEFAULT '0',
  `calories` int DEFAULT '0',
  `distance` float DEFAULT '0',
  `active_minutes` int DEFAULT '0',
  `source` enum('manual','google_fit','apple_health') DEFAULT 'manual',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user_gamification_stats`
--

CREATE TABLE `user_gamification_stats` (
  `user_id` int NOT NULL,
  `total_workouts` int DEFAULT '0',
  `total_calories` int DEFAULT '0',
  `current_streak` int DEFAULT '0',
  `max_streak` int DEFAULT '0',
  `total_exercises_completed` int DEFAULT '0',
  `last_workout_date` date DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `user_gamification_stats`
--

INSERT INTO `user_gamification_stats` (`user_id`, `total_workouts`, `total_calories`, `current_streak`, `max_streak`, `total_exercises_completed`, `last_workout_date`, `updated_at`) VALUES
(1, 0, 0, 0, 0, 0, NULL, '2026-06-19 22:42:25'),
(3, 0, 0, 0, 0, 0, NULL, '2026-06-19 21:57:40'),
(7, 1, 0, 1, 1, 5, '2026-06-20', '2026-06-20 11:39:14');

-- --------------------------------------------------------

--
-- Структура таблицы `user_google_fit_tokens`
--

CREATE TABLE `user_google_fit_tokens` (
  `user_id` int NOT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text NOT NULL,
  `token_expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user_levels`
--

CREATE TABLE `user_levels` (
  `user_id` int NOT NULL,
  `level` int DEFAULT '1',
  `experience` int DEFAULT '0',
  `next_level_experience` int DEFAULT '100',
  `total_experience` int DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `user_levels`
--

INSERT INTO `user_levels` (`user_id`, `level`, `experience`, `next_level_experience`, `total_experience`, `updated_at`) VALUES
(1, 1, 0, 100, 0, '2026-06-19 22:42:25'),
(3, 1, 0, 100, 0, '2026-06-19 21:57:40'),
(7, 1, 25, 100, 25, '2026-06-20 11:39:14');

-- --------------------------------------------------------

--
-- Структура таблицы `user_profiles`
--

CREATE TABLE `user_profiles` (
  `user_id` int NOT NULL,
  `age` int DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` int DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `fitness_level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `goal_type` enum('weight_loss','muscle_gain','endurance','health') DEFAULT 'health',
  `target_weight` decimal(5,2) DEFAULT NULL,
  `medical_notes` text,
  `profile_completed` tinyint(1) DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `goal_weights` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `user_profiles`
--

INSERT INTO `user_profiles` (`user_id`, `age`, `weight`, `height`, `gender`, `fitness_level`, `goal_type`, `target_weight`, `medical_notes`, `profile_completed`, `updated_at`, `goal_weights`) VALUES
(1, 22, 80.40, 179, 'male', 'beginner', 'health', NULL, '', 1, '2026-06-20 17:22:19', '{\"health\": 1.0, \"endurance\": 0.0, \"muscle_gain\": 0.0, \"weight_loss\": 0.0}'),
(3, 22, 80.00, 169, 'male', 'intermediate', 'health', NULL, '', 1, '2026-06-20 17:22:19', '{\"health\": 1.0, \"endurance\": 0.0, \"muscle_gain\": 0.0, \"weight_loss\": 0.0}'),
(7, 22, 90.00, 170, 'male', 'beginner', 'muscle_gain', NULL, '', 1, '2026-06-20 17:22:19', '{\"health\": 0.0, \"endurance\": 0.0, \"muscle_gain\": 1.0, \"weight_loss\": 0.0}');

-- --------------------------------------------------------

--
-- Структура таблицы `user_restrictions`
--

CREATE TABLE `user_restrictions` (
  `user_id` int NOT NULL,
  `restriction_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `workout_logs`
--

CREATE TABLE `workout_logs` (
  `id` int NOT NULL,
  `workout_id` int NOT NULL,
  `user_id` int NOT NULL,
  `started_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` datetime DEFAULT NULL,
  `duration_min` decimal(5,1) DEFAULT NULL,
  `calories_burned` int DEFAULT '0',
  `heart_rate_avg` int DEFAULT NULL,
  `heart_rate_max` int DEFAULT NULL,
  `feeling_rating` int DEFAULT NULL,
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `workout_notes`
--

CREATE TABLE `workout_notes` (
  `id` int NOT NULL,
  `workout_id` int NOT NULL,
  `user_id` int NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `workout_notes`
--

INSERT INTO `workout_notes` (`id`, `workout_id`, `user_id`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 7, 'готово', '2026-06-20 11:39:11', '2026-06-20 11:39:11');

-- --------------------------------------------------------

--
-- Структура таблицы `workout_plans`
--

CREATE TABLE `workout_plans` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `trainer_id` int DEFAULT NULL,
  `name` varchar(100) NOT NULL DEFAULT 'Моє тренування',
  `description` text,
  `total_duration_min` decimal(5,1) DEFAULT '30.0',
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT 'intermediate',
  `focus` varchar(50) DEFAULT 'general',
  `calories_burned` int DEFAULT '0',
  `status` enum('planned','in_progress','completed','cancelled') DEFAULT 'planned',
  `scheduled_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  `rating` int DEFAULT '0',
  `review` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `workout_plans`
--

INSERT INTO `workout_plans` (`id`, `user_id`, `trainer_id`, `name`, `description`, `total_duration_min`, `difficulty`, `focus`, `calories_burned`, `status`, `scheduled_date`, `created_at`, `updated_at`, `completed_at`, `rating`, `review`) VALUES
(1, 7, NULL, 'Моє тренування 20.06.2026', NULL, 11.0, 'intermediate', 'general', 0, 'completed', NULL, '2026-06-20 11:38:47', '2026-06-20 11:39:14', '2026-06-20 13:39:14', 0, NULL),
(2, 7, NULL, 'Моє тренування 20.06.2026', NULL, 11.0, 'intermediate', 'general', 0, 'planned', NULL, '2026-06-20 11:52:40', '2026-06-20 11:52:40', NULL, 0, NULL),
(3, 7, NULL, 'Моє тренування 20.06.2026', NULL, 10.0, 'intermediate', 'general', 0, 'planned', NULL, '2026-06-20 14:19:46', '2026-06-20 14:19:46', NULL, 0, NULL),
(4, 7, NULL, 'Моє тренування 20.06.2026', NULL, 14.4, 'intermediate', 'general', 0, 'planned', NULL, '2026-06-20 17:33:18', '2026-06-20 17:33:18', NULL, 0, NULL),
(5, 7, NULL, 'Моє тренування 20.06.2026', NULL, 14.4, 'intermediate', 'general', 0, 'planned', NULL, '2026-06-20 17:33:24', '2026-06-20 17:33:24', NULL, 0, NULL),
(6, 7, NULL, 'Моє тренування 20.06.2026', NULL, 14.4, 'intermediate', 'general', 0, 'planned', NULL, '2026-06-20 17:33:27', '2026-06-20 17:33:27', NULL, 0, NULL),
(7, 7, NULL, 'Моє тренування 20.06.2026', NULL, 10.0, 'intermediate', 'general', 0, 'planned', NULL, '2026-06-20 17:33:29', '2026-06-20 17:33:29', NULL, 0, NULL),
(8, 7, NULL, 'Моє тренування 20.06.2026', NULL, 10.0, 'intermediate', 'general', 0, 'planned', NULL, '2026-06-20 17:33:30', '2026-06-20 17:33:30', NULL, 0, NULL),
(9, 7, NULL, 'Моє тренування 20.06.2026', NULL, 14.4, 'intermediate', 'general', 0, 'planned', NULL, '2026-06-20 17:33:32', '2026-06-20 17:33:32', NULL, 0, NULL),
(10, 7, NULL, 'Моє тренування 20.06.2026', NULL, 10.0, 'intermediate', 'general', 0, 'planned', NULL, '2026-06-20 17:33:34', '2026-06-20 17:33:34', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `workout_templates`
--

CREATE TABLE `workout_templates` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `icon` varchar(50) DEFAULT 'bi-dumbbell',
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT 'intermediate',
  `focus` varchar(50) DEFAULT 'general',
  `duration_min` int DEFAULT '30',
  `calories_estimate` int DEFAULT '200',
  `is_public` tinyint(1) DEFAULT '1',
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `use_count` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `workout_templates`
--

INSERT INTO `workout_templates` (`id`, `name`, `description`, `icon`, `difficulty`, `focus`, `duration_min`, `calories_estimate`, `is_public`, `is_active`, `created_by`, `created_at`, `updated_at`, `use_count`) VALUES
(1, '🏋️ Силова тренировка для початківців', 'Базові вправи для розвитку сили та м\'язового тонусу. Підходить для новачків.', 'bi-dumbbell', 'beginner', 'strength', 30, 250, 1, 1, NULL, '2026-06-20 11:36:42', '2026-06-20 11:36:42', 0),
(2, '🔥 Кардіо-тренування', 'Інтенсивне кардіо для спалювання жиру та покращення витривалості.', 'bi-heart-pulse', 'intermediate', 'cardio', 25, 350, 1, 1, NULL, '2026-06-20 11:36:42', '2026-06-20 11:36:42', 0),
(3, '💪 Функціональний тренінг', 'Комплекс вправ для розвитку координації, балансу та загальної витривалості.', 'bi-activity', 'intermediate', 'functional', 35, 300, 1, 1, NULL, '2026-06-20 11:36:42', '2026-06-20 11:36:42', 0),
(4, '🎯 Тренування для пресу', 'Ефективний комплекс вправ на всі групи м\'язів кора.', 'bi-arrows-angle-expand', 'beginner', 'core', 20, 150, 1, 1, NULL, '2026-06-20 11:36:42', '2026-06-20 11:36:42', 0),
(5, '💯 Продвинута силова', 'Інтенсивна силова тренировка для досвідчених атлетів.', 'bi-trophy', 'advanced', 'strength', 45, 400, 1, 1, NULL, '2026-06-20 11:36:42', '2026-06-20 11:36:42', 0),
(6, '🧘 Йога та розтяжка', 'Розслаблююча практика для гнучкості та відновлення.', 'bi-sun', 'beginner', 'flexibility', 30, 120, 1, 1, NULL, '2026-06-20 11:36:42', '2026-06-20 11:36:42', 0),
(7, '⚡ HIIT-тренування', 'Високоінтенсивне інтервальне тренування для максимального спалювання жиру.', 'bi-lightning', 'advanced', 'cardio', 20, 350, 1, 1, NULL, '2026-06-20 11:36:42', '2026-06-20 11:36:42', 0),
(8, '🚴 Велотренування', 'Інтенсивна велосипедна тренировка для ніг та серцево-судинної системи.', 'bi-bicycle', 'intermediate', 'cardio', 40, 400, 1, 1, NULL, '2026-06-20 11:36:42', '2026-06-20 11:36:42', 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Индексы таблицы `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_chat` (`user1_id`,`user2_id`),
  ADD KEY `user2_id` (`user2_id`);

--
-- Индексы таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Индексы таблицы `client_programs`
--
ALTER TABLE `client_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `trainer_id` (`trainer_id`);

--
-- Индексы таблицы `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `google_fit_sync_log`
--
ALTER TABLE `google_fit_sync_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `heart_rate_logs`
--
ALTER TABLE `heart_rate_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `medical_restrictions`
--
ALTER TABLE `medical_restrictions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `plan_exercises`
--
ALTER TABLE `plan_exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exercise_id` (`exercise_id`),
  ADD KEY `idx_plan` (`plan_id`),
  ADD KEY `idx_completed` (`is_completed`);

--
-- Индексы таблицы `program_exercises`
--
ALTER TABLE `program_exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `exercise_id` (`exercise_id`);

--
-- Индексы таблицы `program_likes`
--
ALTER TABLE `program_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`program_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `program_media`
--
ALTER TABLE `program_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_id` (`program_id`);

--
-- Индексы таблицы `template_exercises`
--
ALTER TABLE `template_exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exercise_id` (`exercise_id`),
  ADD KEY `idx_template` (`template_id`);

--
-- Индексы таблицы `trainer_clients`
--
ALTER TABLE `trainer_clients`
  ADD PRIMARY KEY (`trainer_id`,`client_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Индексы таблицы `trainer_notification_settings`
--
ALTER TABLE `trainer_notification_settings`
  ADD PRIMARY KEY (`trainer_id`);

--
-- Индексы таблицы `trainer_programs`
--
ALTER TABLE `trainer_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`);

--
-- Индексы таблицы `trainer_schedule`
--
ALTER TABLE `trainer_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_achievement` (`user_id`,`achievement_id`),
  ADD KEY `achievement_id` (`achievement_id`);

--
-- Индексы таблицы `user_activity_data`
--
ALTER TABLE `user_activity_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_date` (`user_id`,`activity_date`);

--
-- Индексы таблицы `user_gamification_stats`
--
ALTER TABLE `user_gamification_stats`
  ADD PRIMARY KEY (`user_id`);

--
-- Индексы таблицы `user_google_fit_tokens`
--
ALTER TABLE `user_google_fit_tokens`
  ADD PRIMARY KEY (`user_id`);

--
-- Индексы таблицы `user_levels`
--
ALTER TABLE `user_levels`
  ADD PRIMARY KEY (`user_id`);

--
-- Индексы таблицы `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Индексы таблицы `user_restrictions`
--
ALTER TABLE `user_restrictions`
  ADD PRIMARY KEY (`user_id`,`restriction_id`),
  ADD KEY `restriction_id` (`restriction_id`);

--
-- Индексы таблицы `workout_logs`
--
ALTER TABLE `workout_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_workout` (`workout_id`);

--
-- Индексы таблицы `workout_notes`
--
ALTER TABLE `workout_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_workout` (`workout_id`);

--
-- Индексы таблицы `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Индексы таблицы `workout_templates`
--
ALTER TABLE `workout_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_public` (`is_public`),
  ADD KEY `idx_active` (`is_active`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT для таблицы `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `client_programs`
--
ALTER TABLE `client_programs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `exercises`
--
ALTER TABLE `exercises`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `google_fit_sync_log`
--
ALTER TABLE `google_fit_sync_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `heart_rate_logs`
--
ALTER TABLE `heart_rate_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `medical_restrictions`
--
ALTER TABLE `medical_restrictions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `plan_exercises`
--
ALTER TABLE `plan_exercises`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT для таблицы `program_exercises`
--
ALTER TABLE `program_exercises`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT для таблицы `program_likes`
--
ALTER TABLE `program_likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `program_media`
--
ALTER TABLE `program_media`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `template_exercises`
--
ALTER TABLE `template_exercises`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `trainer_programs`
--
ALTER TABLE `trainer_programs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `trainer_schedule`
--
ALTER TABLE `trainer_schedule`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `user_achievements`
--
ALTER TABLE `user_achievements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user_activity_data`
--
ALTER TABLE `user_activity_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `workout_logs`
--
ALTER TABLE `workout_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `workout_notes`
--
ALTER TABLE `workout_notes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `workout_plans`
--
ALTER TABLE `workout_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `workout_templates`
--
ALTER TABLE `workout_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `client_programs`
--
ALTER TABLE `client_programs`
  ADD CONSTRAINT `client_programs_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_programs_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `trainer_programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_programs_ibfk_3` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `google_fit_sync_log`
--
ALTER TABLE `google_fit_sync_log`
  ADD CONSTRAINT `google_fit_sync_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `heart_rate_logs`
--
ALTER TABLE `heart_rate_logs`
  ADD CONSTRAINT `heart_rate_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `plan_exercises`
--
ALTER TABLE `plan_exercises`
  ADD CONSTRAINT `plan_exercises_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `workout_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `plan_exercises_ibfk_2` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`);

--
-- Ограничения внешнего ключа таблицы `program_exercises`
--
ALTER TABLE `program_exercises`
  ADD CONSTRAINT `program_exercises_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `trainer_programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `program_exercises_ibfk_2` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`);

--
-- Ограничения внешнего ключа таблицы `program_likes`
--
ALTER TABLE `program_likes`
  ADD CONSTRAINT `program_likes_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `trainer_programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `program_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `program_media`
--
ALTER TABLE `program_media`
  ADD CONSTRAINT `program_media_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `trainer_programs` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `template_exercises`
--
ALTER TABLE `template_exercises`
  ADD CONSTRAINT `template_exercises_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `workout_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `template_exercises_ibfk_2` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`);

--
-- Ограничения внешнего ключа таблицы `trainer_clients`
--
ALTER TABLE `trainer_clients`
  ADD CONSTRAINT `trainer_clients_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trainer_clients_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `trainer_notification_settings`
--
ALTER TABLE `trainer_notification_settings`
  ADD CONSTRAINT `trainer_notification_settings_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `trainer_programs`
--
ALTER TABLE `trainer_programs`
  ADD CONSTRAINT `trainer_programs_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `trainer_schedule`
--
ALTER TABLE `trainer_schedule`
  ADD CONSTRAINT `trainer_schedule_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trainer_schedule_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_activity_data`
--
ALTER TABLE `user_activity_data`
  ADD CONSTRAINT `user_activity_data_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_gamification_stats`
--
ALTER TABLE `user_gamification_stats`
  ADD CONSTRAINT `user_gamification_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_google_fit_tokens`
--
ALTER TABLE `user_google_fit_tokens`
  ADD CONSTRAINT `user_google_fit_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_levels`
--
ALTER TABLE `user_levels`
  ADD CONSTRAINT `user_levels_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_restrictions`
--
ALTER TABLE `user_restrictions`
  ADD CONSTRAINT `user_restrictions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_restrictions_ibfk_2` FOREIGN KEY (`restriction_id`) REFERENCES `medical_restrictions` (`id`);

--
-- Ограничения внешнего ключа таблицы `workout_logs`
--
ALTER TABLE `workout_logs`
  ADD CONSTRAINT `workout_logs_ibfk_1` FOREIGN KEY (`workout_id`) REFERENCES `workout_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workout_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `workout_notes`
--
ALTER TABLE `workout_notes`
  ADD CONSTRAINT `workout_notes_ibfk_1` FOREIGN KEY (`workout_id`) REFERENCES `workout_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workout_notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD CONSTRAINT `workout_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workout_plans_ibfk_2` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `workout_templates`
--
ALTER TABLE `workout_templates`
  ADD CONSTRAINT `workout_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
