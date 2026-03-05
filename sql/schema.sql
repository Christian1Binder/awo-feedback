-- awo-feedback/sql/schema.sql

CREATE TABLE IF NOT EXISTS `submissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `form_type` ENUM('parent', 'child') NOT NULL,
  `holiday` ENUM('Ostern', 'Pfingsten', 'Sommer1', 'Sommer2', 'Sommer3', 'Sommer6', 'Herbst') NOT NULL,
  `location` ENUM('OAS', 'LZ', 'RO', 'WHD') NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `consent` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `parent_answers` (
  `submission_id` INT PRIMARY KEY,
  `q1_source` ENUM('Empfehlung von Bekannten/Freunden', 'Internetrecherche', 'Soziale Medien (Facebook, Instagram, etc.)', 'Flyer/Werbung', 'Andere Quelle') NOT NULL,
  `q1_other` VARCHAR(255) DEFAULT NULL,
  `q2_prior` ENUM('Ja, schon mehrmals', 'Ja, einmalig', 'Nein, zum ersten Mal dabei') NOT NULL,
  `q3_children_count` ENUM('1 Kind', '2 Kinder', '3 Kinder oder mehr') NOT NULL,
  `q4_overall` ENUM('Sehr zufrieden', 'Zufrieden', 'Neutral', 'Unzufrieden', 'Sehr unzufrieden') NOT NULL,
  `q5_safe` ENUM('Ja, sehr wohl und sicher', 'Meistens wohl und sicher', 'Neutral', 'Nicht immer wohl und sicher', 'Nein, überhaupt nicht wohl und sicher') NOT NULL,
  `q6_website` ENUM('Sehr ansprechend und informativ', 'In Ordnung, aber Verbesserung möglich', 'Schlecht, unübersichtlich oder veraltet', 'Ich habe die Website nicht besucht') NOT NULL,
  `q7_registration` ENUM('Ja, sehr bequem und verständlich', 'Akzeptabel, aber Verbesserung möglich', 'Unbequem und verwirrend', 'Ich habe mein Kind nicht registriert, jemand anderes hat das erledigt') NOT NULL,
  `q8_organization` ENUM('Sehr gut organisiert und kommuniziert', 'Meistens gut organisiert und kommuniziert', 'Durchschnittlich, Verbesserung möglich', 'Schlecht organisiert/kommuniziert') NOT NULL,
  `q9_cozy` ENUM('Sehr gemütlich und ansprechend gestaltet', 'Eher gemütlich und ansprechend gestaltet', 'Neutral oder keine besondere Meinung', 'Nicht besonders gemütlich und ansprechend gestaltet') NOT NULL,
  `q10_accessibility` ENUM('Sehr bequem und gut erreichbar', 'Meistens bequem und gut erreichbar', 'Durchschnittlich, Verbesserung möglich', 'Unbequem und schwer erreichbar') NOT NULL,
  `q11_food` ENUM('Ja, sehr zufrieden', 'Eher zufrieden', 'Neutral oder keine besondere Meinung', 'Nicht besonders zufrieden', 'Überhaupt nicht zufrieden') NOT NULL,
  `q12_program` ENUM('Sehr abwechslungsreich und ansprechend', 'Eher abwechslungsreich und ansprechend', 'Durchschnittlich, Verbesserung möglich', 'Nicht besonders ansprechend oder verbesserungswürdig') NOT NULL,
  `q13_trip` ENUM('Sehr sinnvoll und interessant', 'Eher sinnvoll und interessant', 'Neutral oder keine besondere Meinung', 'Nicht besonders sinnvoll oder uninteressant') NOT NULL,
  `q14_contact` ENUM('Ja, ausreichend Möglichkeiten', 'Eher ja als nein', 'Unentschieden oder keine klare Meinung', 'Eher nein als ja', 'Nein, überhaupt nicht ausreichend') NOT NULL,
  `q15_staff` ENUM('Sehr freundlich und professionell', 'Eher freundlich und professionell', 'Durchschnittlich, Verbesserung möglich', 'Unfreundlich oder unprofessionell') NOT NULL,
  `q16_recommend` ENUM('Ja, auf jeden Fall', 'Eher ja', 'Unentschieden oder keine klare Meinung', 'Eher nein', 'Nein, definitiv nicht') NOT NULL,
  `q17_disliked` TEXT DEFAULT NULL,
  `q18_liked` TEXT DEFAULT NULL,
  `q19_suggestions` TEXT DEFAULT NULL,
  FOREIGN KEY (`submission_id`) REFERENCES `submissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `child_answers` (
  `submission_id` INT PRIMARY KEY,
  `q1_crafts` TINYINT(1) NOT NULL COMMENT '1=gut, 5=schlecht',
  `q2_food` TINYINT(1) NOT NULL COMMENT '1=gut, 5=schlecht',
  `q3_staff` TINYINT(1) NOT NULL COMMENT '1=gut, 5=schlecht',
  `q4_trip` TINYINT(1) NOT NULL COMMENT '1=gut, 5=schlecht',
  `q5_return` TINYINT(1) NOT NULL COMMENT '1=gut, 5=schlecht',
  `q6_disliked` TEXT DEFAULT NULL,
  `q7_liked` TEXT DEFAULT NULL,
  FOREIGN KEY (`submission_id`) REFERENCES `submissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
