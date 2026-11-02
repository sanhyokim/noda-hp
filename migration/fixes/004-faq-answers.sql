-- 「AI に教える答え」を、ホームページの「よくある質問」のページにも載せるかどうか（Boss 2026-10-23）
ALTER TABLE chat_answers ADD COLUMN on_faq TINYINT(1) NOT NULL DEFAULT 0 AFTER answer
