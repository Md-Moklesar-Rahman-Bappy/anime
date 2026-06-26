-- ============================================================
-- Anikoto – Complete Database Schema
-- Native PHP + MySQL (XAMPP: localhost / root / "")
-- ============================================================
-- Drop & recreate
DROP DATABASE IF EXISTS `anikoto`;
CREATE DATABASE `anikoto` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `anikoto`;

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE `users` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(100)    NOT NULL,
  `email`      VARCHAR(255)    NOT NULL,
  `password`   VARCHAR(255)    NOT NULL,
  `avatar`     VARCHAR(500)    DEFAULT NULL,
  `role`       ENUM('user','admin','super_admin') NOT NULL DEFAULT 'user',
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PASSWORD RESETS
-- ============================================================
CREATE TABLE `password_resets` (
  `email`      VARCHAR(255) NOT NULL,
  `token`      VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- GENRES
-- ============================================================
CREATE TABLE `genres` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100)    NOT NULL,
  `slug`       VARCHAR(150)    NOT NULL,
  `created_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `genres_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ANIME
-- ============================================================
CREATE TABLE `anime` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mal_id`         INT             DEFAULT NULL,
  `title`          VARCHAR(500)    NOT NULL,
  `title_japanese` VARCHAR(500)    DEFAULT NULL,
  `slug`           VARCHAR(500)    NOT NULL,
  `description`    TEXT            DEFAULT NULL,
  `type`           VARCHAR(50)     DEFAULT NULL COMMENT 'TV, Movie, OVA, ONA, Special, Music',
  `status`         VARCHAR(50)     DEFAULT NULL COMMENT 'Currently Airing, Finished Airing, Not yet aired',
  `country`        VARCHAR(50)     DEFAULT NULL,
  `season`         VARCHAR(20)     DEFAULT NULL COMMENT 'Spring, Summer, Fall, Winter',
  `year`           YEAR            DEFAULT NULL,
  `rating`         DECIMAL(3,1)    DEFAULT NULL COMMENT 'User rating 1-10',
  `age_rating`     VARCHAR(50)     DEFAULT NULL COMMENT 'G, PG, PG-13, R, R+, Rx',
  `score`          DECIMAL(4,2)    DEFAULT NULL,
  `episodes_count` INT             NOT NULL DEFAULT 0,
  `duration`       INT             DEFAULT NULL COMMENT 'Minutes per episode',
  `source`         VARCHAR(100)    DEFAULT NULL COMMENT 'Manga, Original, Light Novel, etc',
  `studio`         VARCHAR(255)    DEFAULT NULL,
  `producers`      VARCHAR(500)    DEFAULT NULL,
  `licensors`      VARCHAR(500)    DEFAULT NULL,
  `thumbnail`      VARCHAR(500)    DEFAULT NULL,
  `banner`         VARCHAR(500)    DEFAULT NULL,
  `views`          INT             NOT NULL DEFAULT 0,
  `featured`       TINYINT(1)      NOT NULL DEFAULT 0,
  `featured_order` INT             DEFAULT NULL,
  `is_upcoming`    TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`     TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `anime_slug_unique` (`slug`),
  KEY `anime_type_index` (`type`),
  KEY `anime_status_index` (`status`),
  KEY `anime_year_index` (`year`),
  KEY `anime_featured_index` (`featured`),
  FULLTEXT KEY `anime_title_fulltext` (`title`, `title_japanese`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ANIME <-> GENRE pivot
-- ============================================================
CREATE TABLE `anime_genre` (
  `anime_id` BIGINT UNSIGNED NOT NULL,
  `genre_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`anime_id`, `genre_id`),
  KEY `anime_genre_genre_id_foreign` (`genre_id`),
  CONSTRAINT `anime_genre_anime_id_foreign` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON DELETE CASCADE,
  CONSTRAINT `anime_genre_genre_id_foreign` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- EPISODES
-- ============================================================
CREATE TABLE `episodes` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `anime_id`    BIGINT UNSIGNED NOT NULL,
  `number`      INT             NOT NULL,
  `title`       VARCHAR(500)    DEFAULT NULL,
  `description` TEXT            DEFAULT NULL,
  `thumbnail`   VARCHAR(500)    DEFAULT NULL,
  `duration`    INT             DEFAULT NULL COMMENT 'Seconds',
  `air_date`    DATE            DEFAULT NULL,
  `has_sub`     TINYINT(1)      NOT NULL DEFAULT 1,
  `has_dub`     TINYINT(1)      NOT NULL DEFAULT 0,
  `views`       INT             NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `episodes_anime_id_number_index` (`anime_id`, `number`),
  CONSTRAINT `episodes_anime_id_foreign` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- EPISODE SOURCES (sub / dub – multiple servers)
-- ============================================================
CREATE TABLE `episode_sources` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `episode_id`  BIGINT UNSIGNED NOT NULL,
  `language`    ENUM('sub','dub') NOT NULL DEFAULT 'sub',
  `label`       VARCHAR(100)    DEFAULT NULL COMMENT 'e.g. Server #1, HD-1',
  `url`         TEXT            NOT NULL,
  `quality`     VARCHAR(20)     DEFAULT 'HD',
  `embed`       TINYINT(1)     NOT NULL DEFAULT 0 COMMENT '1 = embed, 0 = direct',
  `created_at`  TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `episode_sources_episode_id_foreign` (`episode_id`),
  CONSTRAINT `episode_sources_episode_id_foreign` FOREIGN KEY (`episode_id`) REFERENCES `episodes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- WATCH HISTORY
-- ============================================================
CREATE TABLE `watch_history` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `episode_id` BIGINT UNSIGNED NOT NULL,
  `progress`   INT             NOT NULL DEFAULT 0 COMMENT 'Seconds watched',
  `completed`  TINYINT(1)      NOT NULL DEFAULT 0,
  `watched_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `watch_history_user_episode_unique` (`user_id`, `episode_id`),
  CONSTRAINT `watch_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `watch_history_episode_id_foreign` FOREIGN KEY (`episode_id`) REFERENCES `episodes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FAVORITES / USER LISTS
-- ============================================================
CREATE TABLE `favorites` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `anime_id`   BIGINT UNSIGNED NOT NULL,
  `list_type`  ENUM('watching','completed','on_hold','dropped','plan_to_watch') NOT NULL DEFAULT 'plan_to_watch',
  `created_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favorites_user_anime_unique` (`user_id`, `anime_id`),
  CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_anime_id_foreign` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- COMMENTS
-- ============================================================
CREATE TABLE `comments` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `episode_id` BIGINT UNSIGNED DEFAULT NULL,
  `anime_id`   BIGINT UNSIGNED DEFAULT NULL,
  `body`       TEXT            NOT NULL,
  `created_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `comments_user_id_foreign` (`user_id`),
  KEY `comments_episode_id_foreign` (`episode_id`),
  KEY `comments_anime_id_foreign` (`anime_id`),
  CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_episode_id_foreign` FOREIGN KEY (`episode_id`) REFERENCES `episodes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_anime_id_foreign` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SITE SETTINGS
-- ============================================================
CREATE TABLE `settings` (
  `id`    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key`   VARCHAR(100)    NOT NULL,
  `value` TEXT            DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- GENRE SEED DATA
-- ============================================================
INSERT INTO `genres` (`id`, `name`, `slug`) VALUES
(1,  'Action',        'action'),
(2,  'Adventure',     'adventure'),
(3,  'Comedy',        'comedy'),
(4,  'Drama',         'drama'),
(5,  'Fantasy',       'fantasy'),
(6,  'Sci-Fi',        'sci-fi'),
(7,  'Mystery',       'mystery'),
(8,  'Romance',       'romance'),
(9,  'Slice of Life', 'slice-of-life'),
(10, 'Supernatural',  'supernatural'),
(11, 'Thriller',      'thriller'),
(12, 'Horror',        'horror'),
(13, 'Psychological', 'psychological'),
(14, 'Sports',        'sports'),
(15, 'Martial Arts',  'martial-arts'),
(16, 'School',        'school'),
(17, 'Music',         'music'),
(18, 'Mecha',         'mecha'),
(19, 'Military',      'military'),
(20, 'Historical',    'historical'),
(21, 'Seinen',        'seinen'),
(22, 'Shounen',       'shounen'),
(23, 'Shoujo',        'shoujo'),
(24, 'Josei',         'josei'),
(25, 'Kids',          'kids'),
(26, 'Harem',         'harem'),
(27, 'Isekai',        'isekai'),
(28, 'Parody',        'parody'),
(29, 'Ecchi',         'ecchi'),
(30, 'Demons',        'demons'),
(31, 'Game',          'game'),
(32, 'Space',         'space'),
(33, 'Samurai',       'samurai'),
(34, 'Vampire',       'vampire'),
(35, 'Police',        'police'),
(36, 'Magic',         'magic'),
(37, 'Super Power',   'super-power'),
(38, 'Suspense',      'suspense');

-- ============================================================
-- ANIME SEED DATA (matching existing aniwaves data)
-- ============================================================
INSERT INTO `anime` (`id`, `title`, `title_japanese`, `slug`, `description`, `type`, `status`, `country`, `season`, `year`, `rating`, `age_rating`, `score`, `episodes_count`, `duration`, `source`, `studio`, `thumbnail`, `views`, `featured`, `featured_order`) VALUES
(1, 'One Piece', 'ONE PIECE', 'one-piece', 'Follow Monkey D. Luffy and his pirate crew in their adventurous journey to find the legendary treasure One Piece.', 'TV', 'Currently Airing', 'JP', 'Fall', 1999, 8.7, 'PG-13', 8.73, 1057, 24, 'Manga', 'Toei Animation', 'https://cdn.myanimelist.net/images/anime/1244/138851l.jpg', 5, 1, 1),
(2, 'Jujutsu Kaisen', '呪術廻戦', 'jujutsu-kaisen', 'Idly indulging in baseless paranormal activities with the Occult Club, high schooler Yuuji Itadori spends his days at either the clubroom or the hospital.', 'TV', 'Finished Airing', 'JP', 'Fall', 2020, 8.8, 'R', 8.50, 24, 23, 'Manga', 'MAPPA', 'https://cdn.myanimelist.net/images/anime/1171/109222l.jpg', 1, 0, NULL),
(3, 'Attack on Titan', '進撃の巨人', 'attack-on-titan', 'Centuries ago, mankind was slaughtered to near extinction by monstrous humanoid creatures called Titans.', 'TV', 'Finished Airing', 'JP', 'Spring', 2013, 9.0, 'R', 8.57, 5, 24, 'Manga', 'Wit Studio', 'https://cdn.myanimelist.net/images/anime/10/47347l.jpg', 0, 0, NULL),
(4, 'Demon Slayer: Kimetsu no Yaiba', '鬼滅の刃', 'demon-slayer', 'Ever since the death of his father, the burden of supporting the family has fallen upon Tanjirou Kamado\'s shoulders.', 'TV', 'Finished Airing', 'JP', 'Spring', 2019, 8.9, 'R', 8.40, 5, 23, 'Manga', 'ufotable', 'https://cdn.myanimelist.net/images/anime/1286/99889l.jpg', 0, 0, NULL),
(5, 'Naruto Shippuden', '-ナルト- 疾風伝', 'naruto-shippuden', 'It has been two and a half years since Naruto Uzumaki left Konohagakure for intense training.', 'TV', 'Finished Airing', 'JP', 'Winter', 2007, 8.3, 'PG-13', 8.29, 5, 23, 'Manga', 'Studio Pierrot', 'https://cdn.myanimelist.net/images/anime/1565/111305l.jpg', 0, 0, NULL),
(6, 'Death Note', 'デスノート', 'death-note', 'A high school student discovers a supernatural notebook that allows him to kill anyone by writing their name.', 'TV', 'Finished Airing', 'JP', 'Fall', 2006, 8.9, 'R', 8.62, 5, 23, 'Manga', 'Madhouse', 'https://cdn.myanimelist.net/images/anime/1079/138100l.jpg', 0, 0, NULL),
(7, 'Fullmetal Alchemist: Brotherhood', '鋼の錬金術師 FULLMETAL ALCHEMIST', 'fullmetal-alchemist-brotherhood', 'Two brothers search for a Philosopher\'s Stone after an attempt to revive their mother goes wrong.', 'TV', 'Finished Airing', 'JP', 'Spring', 2009, 9.1, 'R', 9.11, 5, 24, 'Manga', 'Bones', 'https://cdn.myanimelist.net/images/anime/1208/94745l.jpg', 0, 0, NULL),
(8, 'My Hero Academia', '僕のヒーローアカデミア', 'my-hero-academia', 'A boy without superpowers enrolls in a prestigious hero academy.', 'TV', 'Finished Airing', 'JP', 'Spring', 2016, 8.2, 'PG-13', 7.83, 5, 24, 'Manga', 'Bones', 'https://cdn.myanimelist.net/images/anime/10/78745l.jpg', 0, 0, NULL),
(9, 'Spy x Family', 'SPY×FAMILY', 'spy-x-family', 'A spy must build a fake family to complete his mission.', 'TV', 'Finished Airing', 'JP', 'Spring', 2022, 8.6, 'PG-13', 8.42, 5, 24, 'Manga', 'Wit Studio', 'https://cdn.myanimelist.net/images/anime/1441/122795l.jpg', 0, 0, NULL),
(10, 'Chainsaw Man', 'チェンソーマン', 'chainsaw-man', 'A young man becomes a devil hunter after merging with his pet devil Pochita.', 'TV', 'Finished Airing', 'JP', 'Fall', 2022, 8.7, 'R', 8.43, 5, 24, 'Manga', 'MAPPA', 'https://cdn.myanimelist.net/images/anime/1806/126216l.jpg', 0, 0, NULL),
(11, 'Solo Leveling', '俺だけレベルアップな件', 'solo-leveling', 'The weakest hunter of all mankind gains a mysterious system that lets him level up.', 'TV', 'Finished Airing', 'JP', 'Winter', 2024, 8.8, 'R', 8.16, 5, 23, 'Web manga', 'A-1 Pictures', 'https://cdn.myanimelist.net/images/anime/1801/142390l.jpg', 1, 1, 5),
(12, 'One-Punch Man', 'ワンパンマン', 'one-punch-man', 'A hero who can defeat any enemy with a single punch seeks a worthy opponent.', 'TV', 'Finished Airing', 'JP', 'Fall', 2015, 8.6, 'R', 8.47, 12, 24, 'Web manga', 'Madhouse', 'https://cdn.myanimelist.net/images/anime/12/76049l.jpg', 1, 1, 4),
(13, 'Steins;Gate', 'STEINS;GATE', 'steinsgate', 'A self-proclaimed mad scientist accidentally invents a time machine.', 'TV', 'Finished Airing', 'JP', 'Spring', 2011, 9.0, 'PG-13', 9.07, 5, 24, 'Visual novel', 'White Fox', 'https://cdn.myanimelist.net/images/anime/1935/127974l.jpg', 0, 0, NULL),
(14, 'Hunter x Hunter', 'HUNTER×HUNTER', 'hunter-x-hunter', 'A young boy becomes a Hunter to find his missing father.', 'TV', 'Finished Airing', 'JP', 'Fall', 2011, 8.8, 'PG-13', 9.03, 5, 23, 'Manga', 'Madhouse', 'https://cdn.myanimelist.net/images/anime/1337/99013l.jpg', 0, 0, NULL),
(15, 'Your Name.', '君の名は。', 'kimi-no-na-wa-your-name', 'Two strangers find themselves mysteriously swapping bodies.', 'Movie', 'Finished Airing', 'JP', NULL, NULL, 8.8, 'PG-13', 8.82, 1, 46, 'Original', 'CoMix Wave Films', 'https://cdn.myanimelist.net/images/anime/5/87048l.jpg', 1, 1, 3),
(16, 'Vinland Saga', 'ヴィンランド・サガ', 'vinland-saga', 'A young Viking seeks revenge against the man who killed his father.', 'TV', 'Finished Airing', 'JP', 'Summer', 2019, 8.7, 'R', 8.78, 5, 24, 'Manga', 'Wit Studio', 'https://cdn.myanimelist.net/images/anime/1500/103005l.jpg', 0, 0, NULL),
(17, 'Tokyo Revengers', '東京リベンジャーズ', 'tokyo-revengers', 'A man travels back in time to save his girlfriend from a tragic death.', 'TV', 'Finished Airing', 'JP', 'Spring', 2021, 8.0, 'R', 7.82, 5, 23, 'Manga', 'LIDENFILMS', 'https://cdn.myanimelist.net/images/anime/1839/122012l.jpg', 0, 0, NULL),
(18, 'Bleach', 'BLEACH', 'bleach', 'A high schooler becomes a Soul Reaper to protect the living from evil spirits.', 'TV', 'Finished Airing', 'JP', 'Fall', 2004, 8.1, 'PG-13', 7.99, 5, 24, 'Manga', 'Studio Pierrot', 'https://cdn.myanimelist.net/images/anime/1541/147774l.jpg', 0, 0, NULL),
(19, 'Cowboy Bebop', 'カウボーイビバップ', 'cowboy-bebop', 'A ragtag crew of bounty hunters travels through space.', 'TV', 'Finished Airing', 'JP', 'Spring', 1998, 8.8, 'R', 8.75, 5, 24, 'Original', 'Sunrise', 'https://cdn.myanimelist.net/images/anime/4/19644l.jpg', 0, 0, NULL),
(20, 'Dragon Ball Z', 'ドラゴンボールZ', 'dragon-ball-z', 'Goku and his friends defend Earth against powerful villains.', 'TV', 'Finished Airing', 'JP', 'Spring', 1989, 8.2, 'PG-13', 8.21, 253, 24, 'Manga', 'Toei Animation', 'https://cdn.myanimelist.net/images/anime/1277/142022l.jpg', 0, 0, NULL),
(21, 'Frieren: Beyond Journey\'s End', '葬送のフリーレン', 'frieren-beyond-journeys-end', 'An elf mage embarks on a journey to understand human connections after her party disbands.', 'TV', 'Finished Airing', 'JP', 'Fall', 2023, 9.0, 'PG-13', 9.27, 28, 24, 'Manga', 'Madhouse', 'https://cdn.myanimelist.net/images/anime/1015/138006l.jpg', 0, 0, NULL),
(22, 'Monster', 'モンスター', 'monster', 'A brain surgeon pursues a serial killer he once saved.', 'TV', 'Finished Airing', 'JP', 'Spring', 2004, 8.9, 'R+', 8.89, 74, 24, 'Manga', 'Madhouse', 'https://cdn.myanimelist.net/images/anime/10/18793l.jpg', 1, 1, 2),
(23, 'Naruto', 'NARUTO', 'naruto', 'A young ninja works to become the Hokage and earn his village\'s respect.', 'TV', 'Finished Airing', 'JP', 'Fall', 2002, 8.0, 'PG-13', 8.02, 130, 23, 'Manga', 'Studio Pierrot', 'https://cdn.myanimelist.net/images/anime/1141/142503l.jpg', 0, 0, NULL),
(24, 'Solo Leveling Season 2: Arise from the Shadow', '俺だけレベルアップな件 Season 2', 'solo-leveling-season-2-arise-from-the-shadow', 'Sung Jin-Woo continues his journey as the Shadow Monarch.', 'TV', 'Finished Airing', 'JP', 'Winter', 2025, 8.8, 'R', 8.54, 13, 23, 'Web manga', 'A-1 Pictures', 'https://cdn.myanimelist.net/images/anime/1448/147351l.jpg', 0, 0, NULL),
(25, 'Boruto: Naruto Next Generations', 'BORUTO', 'boruto-naruto-next-generations', 'The son of Naruto Uzumaki begins his own ninja journey.', 'TV', 'Finished Airing', 'JP', 'Spring', 2017, 6.0, 'PG-13', 5.98, 169, 23, 'Manga', 'Studio Pierrot', 'https://cdn.myanimelist.net/images/anime/1091/99847l.jpg', 0, 0, NULL);

-- ============================================================
-- ANIME-GENRE RELATIONSHIPS
-- ============================================================
INSERT INTO `anime_genre` (`anime_id`, `genre_id`) VALUES
(1, 1), (1, 2), (1, 5),
(2, 1), (2, 12), (2, 37),
(3, 1), (3, 4), (3, 13), (3, 10),
(4, 1), (4, 15), (4, 30),
(5, 1), (5, 2), (5, 21),
(6, 11), (6, 13), (6, 10),
(7, 1), (7, 2), (7, 4), (7, 5),
(8, 1), (8, 16), (8, 37),
(9, 1), (9, 3), (9, 8),
(10, 1), (10, 5), (10, 12),
(11, 1), (11, 2), (11, 5),
(12, 1), (12, 3), (12, 6),
(13, 4), (13, 6), (13, 11),
(14, 1), (14, 2), (14, 21),
(15, 4), (15, 8), (15, 9),
(16, 1), (16, 2), (16, 4), (16, 20),
(17, 1), (17, 4), (17, 11),
(18, 1), (18, 2), (18, 12),
(19, 1), (19, 6), (19, 9),
(20, 1), (20, 2), (20, 3), (20, 5),
(21, 2), (21, 4), (21, 5), (21, 9),
(22, 4), (22, 7), (22, 11), (22, 13),
(23, 1), (23, 2), (23, 21),
(24, 1), (24, 2), (24, 5),
(25, 1), (25, 2), (25, 21);

-- ============================================================
-- EPISODES (sample)
-- ============================================================
INSERT INTO `episodes` (`id`, `anime_id`, `number`, `title`, `description`, `duration`, `has_sub`, `has_dub`, `views`) VALUES
(1, 1, 1, 'Romance Dawn', 'Episode 1 of One Piece', 24, 1, 0, 10),
(2, 1, 2, 'That Guy, Straw Hat Luffy', 'Episode 2 of One Piece', 24, 1, 0, 8),
(3, 1, 3, 'Morgan vs. Luffy', 'Episode 3 of One Piece', 24, 1, 0, 7),
(4, 2, 1, 'Ryomen Sukuna', 'Episode 1 of Jujutsu Kaisen', 23, 1, 0, 5),
(5, 2, 2, 'For Myself', 'Episode 2 of Jujutsu Kaisen', 23, 1, 0, 3),
(6, 11, 1, 'I Level Up', 'Episode 1 of Solo Leveling', 23, 1, 1, 12),
(7, 11, 2, 'Dungeon', 'Episode 2 of Solo Leveling', 23, 1, 1, 9);

-- ============================================================
-- EPISODE SOURCES (sample)
-- ============================================================
INSERT INTO `episode_sources` (`episode_id`, `language`, `label`, `url`, `quality`, `embed`) VALUES
(1, 'sub', 'Server #1', 'https://example.com/stream/one-piece-ep1.mp4', 'HD', 0),
(1, 'sub', 'Server #2', 'https://example.com/stream/one-piece-ep1-alt.mp4', 'HD', 0),
(6, 'sub', 'Server #1', 'https://example.com/stream/solo-leveling-ep1.mp4', 'Full HD', 0),
(6, 'dub', 'Server #1', 'https://example.com/stream/solo-leveling-ep1-dub.mp4', 'HD', 0),
(7, 'sub', 'Server #1', 'https://example.com/stream/solo-leveling-ep2.mp4', 'Full HD', 0),
(7, 'dub', 'Server #1', 'https://example.com/stream/solo-leveling-ep2-dub.mp4', 'HD', 0);

-- ============================================================
-- ADMIN USER (password: admin123)
-- ============================================================
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'admin', 'admin@anikoto.test', '$2y$10$wpJOpCr15M8LrvX.R.JLi.bGa5eLI4SqJn9kEt0g7yZZIjt5H52K.', 'super_admin');
-- Credentials: admin@anikoto.test / admin123

-- ============================================================
-- DEFAULT SETTINGS
-- ============================================================
INSERT INTO `settings` (`key`, `value`) VALUES
('site_title', 'Anikoto'),
('site_description', 'Watch Anime Online, Free Anime Streaming'),
('site_keywords', 'anime, watch anime, free anime, anime streaming'),
('logo_text', 'Anikoto');
