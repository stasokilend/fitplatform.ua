-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.4:3306
-- Время создания: Июн 23 2026 г., 19:39
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
-- Структура таблицы `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `icon` varchar(50) DEFAULT 'bi-bell',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int NOT NULL,
  `code` varchar(80) NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `icon` varchar(50) DEFAULT 'bi-bell',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `code`, `type`, `title`, `message`, `icon`, `created_at`) VALUES
(1, 'achievement_unlocked', 'achievement', 'Нове досягнення!', 'Ви отримали досягнення «{achievement_name}» та +{points} балів.', 'bi-trophy', '2026-06-23 13:55:43'),
(2, 'workout_completed', 'success', 'Тренування завершено', 'Ви завершили «{workout_name}» і спалили {calories} ккал.', 'bi-check-circle', '2026-06-23 13:55:43'),
(6, 'trainer_new_program', 'info', 'Нова програма від тренера', '{trainer_name} опублікував(ла) нову програму «{program_name}».', 'bi-file-earmark-plus', '2026-06-23 14:11:38');

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
(49, 10, 5, 3, 6, NULL, 60, NULL, 0, 1, NULL, NULL),
(50, 10, 2, 3, 10, NULL, 60, NULL, 1, 1, NULL, NULL),
(51, 10, 3, 3, 10, NULL, 60, NULL, 2, 1, NULL, NULL),
(52, 10, 4, 3, 10, NULL, 60, NULL, 3, 1, NULL, NULL),
(53, 10, 1, 3, 10, NULL, 60, NULL, 4, 1, NULL, NULL),
(54, 11, 5, 3, 6, NULL, 60, NULL, 0, 1, NULL, NULL),
(55, 11, 2, 3, 10, NULL, 60, NULL, 1, 1, NULL, NULL),
(56, 11, 3, 3, 10, NULL, 60, NULL, 2, 1, NULL, NULL),
(57, 11, 4, 3, 10, NULL, 60, NULL, 3, 1, NULL, NULL),
(58, 11, 6, 3, 10, NULL, 60, NULL, 4, 1, NULL, NULL),
(71, 14, 5, 3, 6, NULL, 60, NULL, 0, 1, NULL, NULL),
(72, 14, 2, 3, 10, NULL, 60, NULL, 1, 1, NULL, NULL),
(73, 14, 3, 3, 10, NULL, 60, NULL, 2, 1, NULL, NULL),
(74, 14, 4, 3, 10, NULL, 60, NULL, 3, 1, NULL, NULL),
(75, 14, 1, 3, 10, NULL, 60, NULL, 4, 1, NULL, NULL),
(76, 14, 6, 3, 10, NULL, 60, NULL, 5, 1, NULL, NULL),
(86, 17, 1, 3, 12, NULL, 60, NULL, 1, 1, NULL, NULL),
(87, 17, 2, 3, 10, NULL, 60, NULL, 2, 1, NULL, NULL),
(88, 17, 3, 3, 1, NULL, 60, NULL, 3, 1, NULL, NULL);

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
(17, 3, 5, 3, 3, 10, 60, '', 2),
(18, 4, 6, 1, 3, 10, 60, NULL, 0),
(19, 5, 2, 1, 3, 10, 60, NULL, 0),
(20, 6, 4, 1, 3, 10, 60, NULL, 0),
(21, 6, 1, 1, 3, 10, 60, NULL, 0);

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

--
-- Дамп данных таблицы `template_exercises`
--

INSERT INTO `template_exercises` (`id`, `template_id`, `exercise_id`, `sets`, `reps`, `rest_seconds`, `duration_min`, `order_num`, `notes`) VALUES
(1, 1, 1, 3, 12, 60, 3.0, 1, 'Контролюйте техніку присідання'),
(2, 1, 2, 3, 10, 60, 2.0, 2, 'Виконуйте з колін за потреби'),
(3, 1, 3, 3, 1, 45, 1.0, 3, 'Утримуйте планку 30-60 секунд'),
(4, 2, 5, 4, 8, 45, 1.0, 1, 'Працюйте у комфортному темпі'),
(5, 2, 4, 3, 12, 45, 3.0, 2, 'Чергуйте ноги'),
(6, 2, 3, 3, 1, 30, 1.0, 3, 'Активне відновлення'),
(7, 3, 1, 3, 15, 45, 3.0, 1, 'Повний діапазон руху'),
(8, 3, 4, 3, 12, 45, 3.0, 2, 'Тримайте корпус рівно'),
(9, 3, 5, 3, 8, 60, 1.0, 3, 'Зменшуйте темп за потреби'),
(10, 4, 3, 4, 1, 45, 1.0, 1, 'Планка 30-60 секунд'),
(11, 4, 6, 4, 15, 45, 2.0, 2, 'Не тягніть шию руками'),
(12, 5, 1, 4, 15, 60, 3.0, 1, 'Додайте вагу за можливості'),
(13, 5, 2, 4, 12, 60, 2.0, 2, 'Контрольована амплітуда'),
(14, 5, 5, 4, 10, 60, 1.0, 3, 'Висока інтенсивність'),
(15, 6, 3, 3, 1, 45, 1.0, 1, 'Спокійне дихання'),
(16, 6, 6, 3, 12, 45, 2.0, 2, 'М\'яка активація кора'),
(17, 7, 5, 5, 10, 30, 1.0, 1, 'Інтервали високої інтенсивності'),
(18, 7, 4, 4, 14, 30, 3.0, 2, 'Швидкий темп'),
(19, 8, 1, 4, 15, 45, 3.0, 1, 'Акцент на ноги'),
(20, 8, 4, 4, 12, 45, 3.0, 2, 'Стабільний темп');

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
(3, 3, 'фывыфв', '<p><iframe style=\"width: 350px; height: 197px;\" title=\"YouTube video player\" src=\"https://www.youtube.com/embed/VFVOJMJMqpE?si=Px8lYCETaF1RaSi-\" width=\"350\" height=\"197\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" allowfullscreen=\"allowfullscreen\"></iframe></p>', 'intermediate', 4, 3, 1, 1, '2026-06-20 15:02:30', '2026-06-20 15:02:30', NULL, NULL, NULL, 0, 'general', 0, 0, 0),
(4, 1, 'asdsadsadsad', '<p><span style=\"background-color: rgb(0, 0, 0);\">asdasdasdasdasdasd</span></p>', 'intermediate', 4, 3, 1, 1, '2026-06-23 14:08:02', '2026-06-23 14:08:02', NULL, NULL, NULL, 0, 'general', 0, 0, 0),
(5, 1, 'asdsadasdsad', '<p>asdsadasdasdasd</p>', 'intermediate', 4, 3, 1, 1, '2026-06-23 14:08:32', '2026-06-23 14:08:32', NULL, NULL, NULL, 0, 'general', 0, 0, 0),
(6, 1, 'trytrytry', '<p>rtytyrytryerty</p>', 'intermediate', 4, 3, 1, 1, '2026-06-23 15:31:21', '2026-06-23 15:31:21', NULL, NULL, NULL, 0, 'general', 0, 0, 0);

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

--
-- Дамп данных таблицы `trainer_schedule`
--

INSERT INTO `trainer_schedule` (`id`, `trainer_id`, `client_id`, `day_of_week`, `start_time`, `end_time`, `is_recurring`, `date`, `status`, `notes`) VALUES
(2, 1, NULL, 'wednesday', '07:00:00', '08:00:00', 1, '2026-06-28', 'booked', ''),
(3, 1, NULL, 'monday', '09:00:00', '10:00:00', 1, '2026-06-23', 'available', '');

-- --------------------------------------------------------

--
-- Структура таблицы `trainer_subscriptions`
--

CREATE TABLE `trainer_subscriptions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `trainer_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `trainer_subscriptions`
--

INSERT INTO `trainer_subscriptions` (`id`, `user_id`, `trainer_id`, `created_at`) VALUES
(26, 8, 1, '2026-06-23 17:18:40');

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
(1, 'stasokilend@gmail.com', '$2y$12$TGh6k0AKLayXdOTQmUTrTe/ekig0mEUrWg8pEzRwpqICN7TK0hti.', 'stanislav', 'trainer', 1, '2026-06-19 19:49:08', '2026-06-23 14:00:58', NULL, 0, NULL, NULL, 0),
(2, 'admin@fitplatform.ua', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Адміністратор', 'admin', 1, '2026-06-19 20:33:16', '2026-06-19 20:33:16', NULL, 0, NULL, NULL, 0),
(3, 'staso@gmail.com', '$2y$12$y8XbJvaAENGFnDwMXfC9cujLhfqgq1404cytTVT9XjEUtAkKVD/Sy', 'stasik', 'trainer', 1, '2026-06-19 20:42:34', '2026-06-20 14:38:02', NULL, 0, NULL, NULL, 0),
(8, 'brutal@rw.com', '$2y$12$u8cnIBIjge5e9HAN/WvNZu.14/aqpK1WEFdQqkhBV9QiTs55SOiHy', 'brutal', 'user', 1, '2026-06-23 16:37:01', '2026-06-23 16:37:01', NULL, 0, NULL, NULL, 0);

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
(8, 0, 0, 0, 0, 0, NULL, '2026-06-23 17:38:16');

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
(7, 2, 40, 130, 140, '2026-06-23 16:34:43'),
(8, 1, 45, 100, 45, '2026-06-23 17:16:57'),
(9, 1, 25, 100, 25, '2026-06-23 14:16:52');

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
(7, 23, 90.00, 170, 'male', 'beginner', 'muscle_gain', NULL, '', 1, '2026-06-23 15:46:42', '{\"health\": 0.0, \"endurance\": 0.0, \"muscle_gain\": 1.0, \"weight_loss\": 0.0}'),
(8, 22, 80.00, 178, 'male', 'intermediate', 'muscle_gain', NULL, '', 1, '2026-06-23 16:37:18', NULL),
(9, 22, 80.00, 178, 'male', 'intermediate', 'health', NULL, '', 1, '2026-06-23 15:27:19', NULL);

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
(10, 7, NULL, 'Моє тренування 20.06.2026', NULL, 10.0, 'intermediate', 'general', 0, 'completed', NULL, '2026-06-20 17:33:34', '2026-06-23 16:34:43', '2026-06-23 18:34:43', 0, NULL),
(11, 9, NULL, 'Моє тренування 23.06.2026', NULL, 10.0, 'intermediate', 'general', 0, 'completed', NULL, '2026-06-23 14:16:40', '2026-06-23 14:16:52', '2026-06-23 16:16:52', 0, NULL),
(14, 7, NULL, 'Моє тренування 23.06.2026', NULL, 14.4, 'intermediate', 'general', 0, 'completed', NULL, '2026-06-23 15:46:02', '2026-06-23 15:56:57', '2026-06-23 17:56:57', 0, NULL),
(17, 8, NULL, 'Шаблон: 🏋️ Силова тренировка для початківців', NULL, 30.0, 'beginner', 'strength', 0, 'completed', NULL, '2026-06-23 17:38:25', '2026-06-23 17:38:29', '2026-06-23 19:38:29', 0, NULL);

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
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `idx_chat_created` (`chat_id`,`created_at`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active_difficulty` (`is_active`,`difficulty`),
  ADD KEY `idx_muscle_group` (`muscle_group`);

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
-- Индексы таблицы `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`,`created_at`);

--
-- Индексы таблицы `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

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
  ADD KEY `idx_template` (`template_id`),
  ADD KEY `idx_template_order` (`template_id`,`order_num`);

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
-- Индексы таблицы `trainer_subscriptions`
--
ALTER TABLE `trainer_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_trainer_subscription` (`user_id`,`trainer_id`),
  ADD KEY `idx_trainer_subscriptions_trainer` (`trainer_id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT для таблицы `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `plan_exercises`
--
ALTER TABLE `plan_exercises`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT для таблицы `program_exercises`
--
ALTER TABLE `program_exercises`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `trainer_programs`
--
ALTER TABLE `trainer_programs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `trainer_schedule`
--
ALTER TABLE `trainer_schedule`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `trainer_subscriptions`
--
ALTER TABLE `trainer_subscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
-- Ограничения внешнего ключа таблицы `trainer_subscriptions`
--
ALTER TABLE `trainer_subscriptions`
  ADD CONSTRAINT `trainer_subscriptions_trainer_fk` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trainer_subscriptions_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
