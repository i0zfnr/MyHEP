-- ============================================================
-- e-biasiswa DATABASE
-- MySQL | Laragon
-- ============================================================

CREATE DATABASE IF NOT EXISTS e_biasiswa
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE e_biasiswa;

-- ============================================================
-- 1. STUDENTS
-- Shared table used by both scholarship and discipline sides.
-- Login: matric_no (username) + ic_no (default password),
-- then student can set custom hashed password.
-- ============================================================
CREATE TABLE students (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(150)  NOT NULL,
  matric_no     VARCHAR(20)   NOT NULL UNIQUE,
  ic_no         VARCHAR(20)   NOT NULL UNIQUE,
  password      VARCHAR(255)  NULL,
  email         VARCHAR(150)  NULL UNIQUE,
  program       VARCHAR(100)  NOT NULL,
  semester      VARCHAR(20)   NULL,
  academic_session VARCHAR(30) NULL,
  religion      VARCHAR(50)   NULL,
  parliament    VARCHAR(120)  NULL,
  dun           VARCHAR(120)  NULL,
  race          VARCHAR(80)   NULL,
  date_of_birth DATE          NULL,
  phone         VARCHAR(20)   NULL,
  address       TEXT          NULL,
  guardian_name VARCHAR(150)  NULL,
  guardian_ic_no VARCHAR(20)  NULL,
  guardian_address TEXT       NULL,
  guardian_phone VARCHAR(20)  NULL,
  mother_ic_no  VARCHAR(20)   NULL,
  guardian_occupation VARCHAR(120) NULL,
  family_income DECIMAL(12,2) NULL,
  study_address TEXT          NULL,
  photo         VARCHAR(255)  NULL,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. ADMINS
-- Single table for both scholarship admin and discipline admin.
-- role distinguishes them. Login: ic_no + password
-- ============================================================
CREATE TABLE admins (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(150)  NOT NULL,
  ic_no         VARCHAR(20)   NOT NULL UNIQUE,
  password      VARCHAR(255)  NOT NULL,
  role          ENUM('scholarship_admin','discipline_admin','system_admin') NOT NULL,
  photo         VARCHAR(255)  NULL,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SCHOLARSHIP SIDE
-- ============================================================

-- 3. SCHOLARSHIPS
-- Tracks whether a student receives scholarship / welfare /
-- sponsorship. Both recipients and non-recipients are stored
-- (type = 'none' for non-recipients).
-- ============================================================
CREATE TABLE scholarships (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id    BIGINT UNSIGNED NOT NULL,
  type          ENUM('scholarship','welfare','sponsorship','none') NOT NULL DEFAULT 'none',
  provider_name VARCHAR(150)  NULL,
  amount        DECIMAL(10,2) NULL,
  status        ENUM('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
  proof_file    VARCHAR(255)  NULL,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT chk_scholarships_amount_non_negative
    CHECK (amount IS NULL OR amount >= 0),
  CONSTRAINT fk_scholarships_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. SCHOLARSHIP ANNOUNCEMENTS
-- Created by scholarship admin.
-- link_url + link_label become a button on the student page.
-- ============================================================
CREATE TABLE scholarship_announcements (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id      BIGINT UNSIGNED NOT NULL,
  title         VARCHAR(200)  NOT NULL,
  body          TEXT          NOT NULL,
  type          ENUM('scholarship','welfare','general') NOT NULL DEFAULT 'general',
  link_url      VARCHAR(500)  NULL,
  link_label    VARCHAR(100)  NULL,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_sch_announcements_admin
    FOREIGN KEY (admin_id) REFERENCES admins(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DISCIPLINE SIDE
-- ============================================================

-- 5. OFFENSE TYPES
-- Seeded lookup table for all campus rules / offense categories.
-- requires_note = 1 means admin must type a reason (e.g. "state:")
-- ============================================================
CREATE TABLE offense_types (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rule_reference  VARCHAR(100) NOT NULL,
  description     VARCHAR(255) NOT NULL,
  requires_note   TINYINT(1)   NOT NULL DEFAULT 0,
  created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. OFFENSES
-- One record per summon issued by discipline admin.
-- fine_amount entered manually by admin (e.g. 50.00).
-- status tracks settlement progress.
-- ============================================================
CREATE TABLE offenses (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id    BIGINT UNSIGNED NOT NULL,
  admin_id      BIGINT UNSIGNED NOT NULL,
  offense_date  DATE          NOT NULL,
  offense_time  TIME          NOT NULL,
  place         VARCHAR(150)  NOT NULL,
  fine_amount   DECIMAL(8,2)  NOT NULL DEFAULT 0.00,
  status        ENUM('unpaid','applied','paid') NOT NULL DEFAULT 'unpaid',
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT chk_offenses_fine_amount_non_negative
    CHECK (fine_amount >= 0),
  CONSTRAINT fk_offenses_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_offenses_admin
    FOREIGN KEY (admin_id) REFERENCES admins(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. OFFENSE ITEMS
-- Pivot between offenses and offense_types.
-- One offense can tick multiple rules.
-- note stores free-text reason when requires_note = 1.
-- ============================================================
CREATE TABLE offense_items (
  id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  offense_id       BIGINT UNSIGNED NOT NULL,
  offense_type_id  BIGINT UNSIGNED NOT NULL,
  note             TEXT            NULL,
  created_at       TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uq_offense_items_offense_type (offense_id, offense_type_id),
  CONSTRAINT fk_offense_items_offense
    FOREIGN KEY (offense_id) REFERENCES offenses(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_offense_items_type
    FOREIGN KEY (offense_type_id) REFERENCES offense_types(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. FINE PAYMENT APPLICATIONS
-- Student submits this to request to pay their fine.
-- Admin approves and sets meeting_date.
-- Student sees meeting_date on their discipline dashboard.
-- ============================================================
CREATE TABLE fine_payment_applications (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  offense_id    BIGINT UNSIGNED NOT NULL,
  student_id    BIGINT UNSIGNED NOT NULL,
  student_note  TEXT            NULL,
  status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  meeting_date  DATE            NULL,
  created_at    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_fine_apps_offense
    FOREIGN KEY (offense_id) REFERENCES offenses(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_fine_apps_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_fine_apps_student_status ON fine_payment_applications (student_id, status);
CREATE INDEX idx_fine_apps_offense_status ON fine_payment_applications (offense_id, status);

-- 9. VEHICLE STICKER APPLICATIONS
-- Student applies for vehicle sticker.
-- approved_by references the discipline admin who actioned it.
-- ============================================================
CREATE TABLE vehicle_sticker_applications (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id    BIGINT UNSIGNED NOT NULL,
  vehicle_no    VARCHAR(20)   NOT NULL,
  vehicle_type  VARCHAR(50)   NOT NULL,
  status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  approved_by   BIGINT UNSIGNED NULL,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_sticker_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_sticker_admin
    FOREIGN KEY (approved_by) REFERENCES admins(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_stickers_student_status ON vehicle_sticker_applications (student_id, status);

-- 10. RULE CATEGORIES
-- Normalized lookup for discipline rule categories.
-- ============================================================
CREATE TABLE rule_categories (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL UNIQUE,
  created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. RULES
-- Campus rules displayed to students.
-- Managed (CRUD) by discipline admin.
-- ============================================================
CREATE TABLE rules (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title         VARCHAR(200)  NOT NULL,
  category_id   BIGINT UNSIGNED NOT NULL,
  description   TEXT          NOT NULL,
  updated_by    BIGINT UNSIGNED NULL,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_rules_category
    FOREIGN KEY (category_id) REFERENCES rule_categories(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_rules_admin
    FOREIGN KEY (updated_by) REFERENCES admins(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. DISCIPLINE ANNOUNCEMENTS
-- Posted by discipline admin only.
-- Separate from scholarship announcements.
-- ============================================================
CREATE TABLE discipline_announcements (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id      BIGINT UNSIGNED NOT NULL,
  title         VARCHAR(200)  NOT NULL,
  body          TEXT          NOT NULL,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_disc_announcements_admin
    FOREIGN KEY (admin_id) REFERENCES admins(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. AUDIT LOGS
-- Records critical user actions for traceability and security.
-- ============================================================
CREATE TABLE audit_logs (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_type    VARCHAR(20)   NOT NULL,
  actor_id      BIGINT UNSIGNED NULL,
  action        VARCHAR(120)  NOT NULL,
  target_type   VARCHAR(80)   NULL,
  target_id     BIGINT UNSIGNED NULL,
  description   VARCHAR(255)  NULL,
  ip_address    VARCHAR(45)   NULL,
  user_agent    VARCHAR(255)  NULL,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_audit_action_created_at ON audit_logs (action, created_at);
CREATE INDEX idx_audit_actor ON audit_logs (actor_type, actor_id, created_at);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Seed: Sample students
INSERT INTO students (full_name, matric_no, ic_no, program, phone, address, photo) VALUES
('Irfan', '23DIT00001', '030101011111', 'Diploma Teknologi Maklumat', NULL, NULL, NULL),
('Nuraa', '23DIT00002', '030202011112', 'Diploma Teknologi Maklumat', NULL, NULL, NULL);

-- Seed: Rule categories
INSERT INTO rule_categories (name) VALUES
('Kad Pelajar'),
('Pemakaian'),
('Kebersihan'),
('Bunyi Bising'),
('Perpustakaan'),
('Trafik Kampus'),
('Kemudahan Kampus'),
('Lain-lain');

-- Seed: Offense types (all rules from the system spec)
INSERT INTO offense_types (rule_reference, description, requires_note) VALUES
-- Part IV: Road Traffic Discipline
('Part IV', 'No valid driving license', 0),
('Part IV', 'No valid road tax', 0),
('Part IV', 'No valid vehicle sticker', 0),
-- Rule 26A
('Rule 26A', 'Immediate disciplinary action', 0),
-- Rule 25: Student Card
('Rule 25', 'No student card', 0),
('Rule 25', 'Not wearing student card', 0),
('Rule 25', 'Wearing another student card', 0),
('Rule 25', 'Damaged or modified student card', 0),
('Rule 25', 'Wearing student card in inappropriate place', 1),
-- Rule 6: Dress & Appearance
('Rule 6', 'Inappropriate dressing (tight/worn-out/etc.)', 0),
('Rule 6', 'Improper attire (no collar/slippers/etc.)', 0),
('Rule 6', 'Long/untidy/colored/punk hair', 1),
-- Rule 21: Cleanliness
('Rule 21', 'Littering', 0),
('Rule 21', 'Vandalism', 0),
('Rule 21', 'Not maintaining cleanliness in campus', 0),
-- Rule 22: Noise
('Rule 22', 'Causing disturbance', 0),
-- Rule 3(j): Library
('Rule 3(j)', 'Damaging books or materials', 0),
('Rule 3(j)', 'Not returning borrowed materials', 0),
('Rule 3(j)', 'Late return of books', 1),
-- Rule 23: Living/Sleeping in Campus
('Rule 23', 'Using campus buildings as sleeping place (other than hostel)', 0),
('Rule 23', 'Causing disturbance in campus buildings', 0),
-- Other offenses
('Other', 'Violating road signs or traffic directions', 0),
('Other', 'Parking in prohibited areas', 1);

-- Seed: Sample admins
INSERT INTO admins (full_name, ic_no, password, role) VALUES
('Ustazah Wan Jamilah', '800101015555', '$2y$12$R9h7cIPz0gi.URNNX3kh2OPST9/PgBkqquzi.Ee8Wm6vvKkQ7Tpl6', 'scholarship_admin'),
('Encik Azlan', '750202026666', '$2y$12$R9h7cIPz0gi.URNNX3kh2OPST9/PgBkqquzi.Ee8Wm6vvKkQ7Tpl6', 'discipline_admin'),
('Hafizul', '900305015555', '$2y$12$R9h7cIPz0gi.URNNX3kh2OPST9/PgBkqquzi.Ee8Wm6vvKkQ7Tpl6', 'system_admin');
