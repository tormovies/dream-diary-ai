-- SQL скрипт для добавления колонки questions_preview в таблицу articles
-- Выполните этот SQL в вашей базе данных MySQL

ALTER TABLE `articles` 
ADD COLUMN `questions_preview` TEXT NULL 
AFTER `content`;
