-- =============================================================================
--  SchoolMS — Tenant Database Template Schema
-- =============================================================================
--  Database:  schoolms_tenant_template  (or any schoolms_<subdomain> DB)
--  Purpose:   Per-school data: students, teachers, classes, fees, etc.
--  Engine:    InnoDB   Charset: utf8mb4
-- =============================================================================
--
--  Provisioning:
--      This schema is created from scratch every time a school registers.
--      The `php artisan tenant:create` command does it for you.
--      For a manual install, run:
--          CREATE DATABASE schoolms_<name> ... ;
--          USE schoolms_<name> ;
--          <contents of this file>
-- =============================================================================

-- -----------------------------------------------------------------------------
--  Table:  classes
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL,
    `section`     VARCHAR(20)  NULL,
    `capacity`    INT UNSIGNED NOT NULL DEFAULT 40,
    `description` TEXT         NULL,
    `created_at`  TIMESTAMP    NULL,
    `updated_at`  TIMESTAMP    NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `classes_name_section_unique` (`name`, `section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  subjects
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL,
    `code`        VARCHAR(20)  NULL,
    `class_id`    BIGINT UNSIGNED NOT NULL,
    `description` TEXT         NULL,
    `created_at`  TIMESTAMP    NULL,
    `updated_at`  TIMESTAMP    NULL,
    PRIMARY KEY (`id`),
    KEY `subjects_class_id_index` (`class_id`),
    CONSTRAINT `subjects_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  students
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admission_no`    VARCHAR(50)  NOT NULL,
    `first_name`      VARCHAR(100) NOT NULL,
    `last_name`       VARCHAR(100) NOT NULL,
    `roll_no`         VARCHAR(20)  NULL,
    `dob`             DATE         NULL,
    `gender`          ENUM('male','female','other') NULL,
    `email`           VARCHAR(191) NULL,
    `phone`           VARCHAR(30)  NULL,
    `address`         TEXT         NULL,
    `guardian_name`   VARCHAR(191) NULL,
    `guardian_phone`  VARCHAR(30)  NULL,
    `admission_date`  DATE         NULL,
    `class_id`        BIGINT UNSIGNED NULL,
    `photo_path`      VARCHAR(255) NULL,
    `user_id`         BIGINT UNSIGNED NULL COMMENT 'Optional link to master.users.id',
    `created_at`      TIMESTAMP    NULL,
    `updated_at`      TIMESTAMP    NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `students_admission_no_unique` (`admission_no`),
    KEY `students_class_id_index` (`class_id`),
    KEY `students_user_id_index` (`user_id`),
    CONSTRAINT `students_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  teachers
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `teachers`;
CREATE TABLE `teachers` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id`   VARCHAR(50)  NOT NULL,
    `first_name`    VARCHAR(100) NOT NULL,
    `last_name`     VARCHAR(100) NOT NULL,
    `email`         VARCHAR(191) NOT NULL,
    `phone`         VARCHAR(30)  NULL,
    `qualification` VARCHAR(191) NULL,
    `hire_date`     DATE         NULL,
    `gender`        ENUM('male','female','other') NULL,
    `address`       TEXT         NULL,
    `salary`        DECIMAL(10,2) NULL,
    `subject_id`    BIGINT UNSIGNED NULL,
    `user_id`       BIGINT UNSIGNED NULL COMMENT 'Optional link to master.users.id',
    `created_at`    TIMESTAMP    NULL,
    `updated_at`    TIMESTAMP    NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `teachers_employee_id_unique` (`employee_id`),
    UNIQUE KEY `teachers_email_unique` (`email`),
    KEY `teachers_subject_id_index` (`subject_id`),
    KEY `teachers_user_id_index` (`user_id`),
    CONSTRAINT `teachers_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  exams
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `exams`;
CREATE TABLE `exams` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(150) NOT NULL,
    `class_id`    BIGINT UNSIGNED NOT NULL,
    `subject_id`  BIGINT UNSIGNED NOT NULL,
    `date`        DATE         NOT NULL,
    `max_marks`   DECIMAL(6,2) NOT NULL DEFAULT 100,
    `pass_marks`  DECIMAL(6,2) NOT NULL DEFAULT 33,
    `description` TEXT         NULL,
    `created_at`  TIMESTAMP    NULL,
    `updated_at`  TIMESTAMP    NULL,
    PRIMARY KEY (`id`),
    KEY `exams_class_id_index` (`class_id`),
    KEY `exams_subject_id_index` (`subject_id`),
    CONSTRAINT `exams_class_id_foreign`   FOREIGN KEY (`class_id`)   REFERENCES `classes`(`id`)  ON DELETE CASCADE,
    CONSTRAINT `exams_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  results
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `results`;
CREATE TABLE `results` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `exam_id`         BIGINT UNSIGNED NOT NULL,
    `student_id`      BIGINT UNSIGNED NOT NULL,
    `marks_obtained`  DECIMAL(6,2) NOT NULL DEFAULT 0,
    `grade`           VARCHAR(5)  NULL,
    `remarks`         TEXT        NULL,
    `created_at`      TIMESTAMP   NULL,
    `updated_at`      TIMESTAMP   NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `results_exam_id_student_id_unique` (`exam_id`, `student_id`),
    KEY `results_student_id_index` (`student_id`),
    CONSTRAINT `results_exam_id_foreign`    FOREIGN KEY (`exam_id`)    REFERENCES `exams`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `results_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  attendance
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id` BIGINT UNSIGNED NOT NULL,
    `class_id`   BIGINT UNSIGNED NOT NULL,
    `date`       DATE NOT NULL,
    `status`     ENUM('present','absent','late','half_day') NOT NULL DEFAULT 'present',
    `remarks`    TEXT         NULL,
    `marked_by`  BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP    NULL,
    `updated_at` TIMESTAMP    NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `attendance_student_id_date_unique` (`student_id`, `date`),
    KEY `attendance_class_id_date_index` (`class_id`, `date`),
    CONSTRAINT `attendance_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    CONSTRAINT `attendance_class_id_foreign`   FOREIGN KEY (`class_id`)   REFERENCES `classes`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  fee_categories
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `fee_categories`;
CREATE TABLE `fee_categories` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`           VARCHAR(100) NOT NULL,
    `description`    TEXT         NULL,
    `default_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `frequency`      ENUM('one_time','monthly','quarterly','half_yearly','annually') NOT NULL DEFAULT 'monthly',
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP    NULL,
    `updated_at`     TIMESTAMP    NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  fees
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `fees`;
CREATE TABLE `fees` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id`  BIGINT UNSIGNED NOT NULL,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `amount`      DECIMAL(10,2) NOT NULL,
    `paid_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `due_date`    DATE         NOT NULL,
    `status`      ENUM('pending','partial','paid','overdue','waived') NOT NULL DEFAULT 'pending',
    `notes`       TEXT         NULL,
    `created_at`  TIMESTAMP    NULL,
    `updated_at`  TIMESTAMP    NULL,
    PRIMARY KEY (`id`),
    KEY `fees_student_id_status_index` (`student_id`, `status`),
    KEY `fees_due_date_index` (`due_date`),
    CONSTRAINT `fees_student_id_foreign`  FOREIGN KEY (`student_id`)  REFERENCES `students`(`id`)        ON DELETE CASCADE,
    CONSTRAINT `fees_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `fee_categories`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  fee_payments
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `fee_payments`;
CREATE TABLE `fee_payments` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fee_id`          BIGINT UNSIGNED NOT NULL,
    `student_id`      BIGINT UNSIGNED NOT NULL,
    `amount_paid`     DECIMAL(10,2) NOT NULL,
    `payment_date`    DATE NOT NULL,
    `mode`            ENUM('cash','cheque','bank_transfer','card','online','other') NOT NULL DEFAULT 'cash',
    `transaction_ref` VARCHAR(100) NULL,
    `receipt_no`      VARCHAR(100) NOT NULL,
    `notes`           TEXT         NULL,
    `received_by`     VARCHAR(191) NULL,
    `created_at`      TIMESTAMP    NULL,
    `updated_at`      TIMESTAMP    NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `fee_payments_receipt_no_unique` (`receipt_no`),
    KEY `fee_payments_fee_id_index`     (`fee_id`),
    KEY `fee_payments_student_id_index` (`student_id`),
    CONSTRAINT `fee_payments_fee_id_foreign`     FOREIGN KEY (`fee_id`)     REFERENCES `fees`(`id`)     ON DELETE CASCADE,
    CONSTRAINT `fee_payments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  migrations
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch`     INT          NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
--  End of tenant_template.sql
-- =============================================================================
