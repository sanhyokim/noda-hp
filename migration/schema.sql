-- テーブルの定義（指示書 3-1）。何度流しても壊れないよう IF NOT EXISTS で作る。
-- 段階5の /admin/setup.php もこのファイルを読んで使う。

CREATE TABLE IF NOT EXISTS cases (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(191) NOT NULL,
  title VARCHAR(255) NOT NULL,
  body_html MEDIUMTEXT NOT NULL,
  location VARCHAR(100) NOT NULL DEFAULT '',
  published_at DATE NOT NULL,
  status ENUM('draft','published','deleted') NOT NULL DEFAULT 'draft',
  seo_title VARCHAR(255) NOT NULL DEFAULT '',
  seo_description VARCHAR(500) NOT NULL DEFAULT '',
  thumbnail_image_id INT UNSIGNED NULL,
  needs_review TINYINT(1) NOT NULL DEFAULT 0,
  wp_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cases_slug (slug),
  UNIQUE KEY uq_cases_wp (wp_id),
  KEY idx_cases_status_date (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS case_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  case_id INT UNSIGNED NOT NULL,
  path VARCHAR(255) NOT NULL,
  path_thumb VARCHAR(255) NOT NULL,
  width INT UNSIGNED NOT NULL,
  height INT UNSIGNED NOT NULL,
  alt VARCHAR(255) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0,
  source_url VARCHAR(500) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY idx_case_images_case (case_id, sort_order),
  CONSTRAINT fk_case_images_case FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  axis ENUM('biz','work') NOT NULL,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_axis_slug (axis, slug),
  KEY idx_categories_axis_order (axis, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS case_categories (
  case_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (case_id, category_id),
  KEY idx_case_categories_cat (category_id),
  CONSTRAINT fk_cc_case FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
  CONSTRAINT fk_cc_cat FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(191) NOT NULL,
  title VARCHAR(255) NOT NULL,
  body_html MEDIUMTEXT NOT NULL,
  published_at DATE NOT NULL,
  status ENUM('draft','published','deleted') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_news_slug (slug),
  KEY idx_news_status_date (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS slug_redirects (
  old_slug VARCHAR(191) NOT NULL,
  new_slug VARCHAR(191) NOT NULL,
  PRIMARY KEY (old_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS admin_users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  login_id VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  last_login_at DATETIME NULL,
  failed_count INT NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_login (login_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  `key` VARCHAR(100) NOT NULL,
  value MEDIUMTEXT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  company VARCHAR(200) NOT NULL DEFAULT '',
  prefecture VARCHAR(10) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(255) NOT NULL,
  contact_method VARCHAR(10) NOT NULL DEFAULT '',
  message TEXT NOT NULL,
  attachment_path TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY idx_contact_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI チャットの記録（会話ごとに1行。管理ページで読める）
CREATE TABLE IF NOT EXISTS chat_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  conv_key CHAR(32) NOT NULL,
  started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  turns INT NOT NULL DEFAULT 0,
  messages MEDIUMTEXT NOT NULL,
  page VARCHAR(255) NOT NULL DEFAULT '',
  handed_off TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_chat_conv (conv_key),
  KEY idx_chat_updated (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
