-- Performance indexes for common FitPlatform dashboard/API queries.
ALTER TABLE workout_plans
  ADD INDEX idx_workout_plans_user_status_created (user_id, status, created_at),
  ADD INDEX idx_workout_plans_user_completed (user_id, completed_at);

ALTER TABLE plan_exercises
  ADD INDEX idx_plan_exercises_plan_completed (plan_id, is_completed),
  ADD INDEX idx_plan_exercises_exercise (exercise_id);

ALTER TABLE workout_notes
  ADD INDEX idx_workout_notes_workout_user_created (workout_id, user_id, created_at);

ALTER TABLE chat_messages
  ADD INDEX idx_chat_messages_chat_created (chat_id, created_at),
  ADD INDEX idx_chat_messages_sender_read (sender_id, is_read);

ALTER TABLE chats
  ADD INDEX idx_chats_users_updated (user1_id, user2_id, updated_at);

ALTER TABLE notifications
  ADD INDEX idx_notifications_user_read_created (user_id, is_read, created_at);

ALTER TABLE client_programs
  ADD INDEX idx_client_programs_client_status (client_id, status),
  ADD INDEX idx_client_programs_trainer_status (trainer_id, status);

ALTER TABLE exercises
  ADD INDEX idx_exercises_active_difficulty_group (is_active, difficulty, muscle_group);
