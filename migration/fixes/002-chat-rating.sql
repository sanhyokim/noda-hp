-- AI 相談の「この相談は役に立ちましたか？」の答え（1：はい、0：いいえ、NULL：答えなし）と、
-- 担当者へ知らせのメールを送ったかの印を、会話の記録に残す（Boss 2026-10-22）
ALTER TABLE chat_logs ADD COLUMN rating TINYINT NULL DEFAULT NULL AFTER handed_off, ADD COLUMN reported TINYINT(1) NOT NULL DEFAULT 0 AFTER rating
