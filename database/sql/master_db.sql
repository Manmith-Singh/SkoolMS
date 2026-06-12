-- =============================================================================
--  SchoolMS — Master Database Schema
-- =============================================================================
--  Database:  schoolms_master
--  Purpose:   Stores tenant registry and global user accounts (login).
--  Engine:    InnoDB   Charset: utf8mb4
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `schoolms_master`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `schoolms_master`;

-- -----------------------------------------------------------------------------
--  Table:  tenants
--  One row per registered school.  Each row maps to a dedicated MySQL DB.
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tenants`;
CREATE TABLE `tenants` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(191) NOT NULL,
    `subdomain`       VARCHAR(63)  NOT NULL,
    `db_name`         VARCHAR(100) NOT NULL,
    `db_username`     VARCHAR(100) NULL,
    `db_password`     VARCHAR(255) NULL,
    `status`          VARCHAR(20)  NOT NULL DEFAULT 'active',
    `contact_email`   VARCHAR(191) NULL,
    `contact_phone`   VARCHAR(30)  NULL,
    `address`         VARCHAR(500) NULL,
    `trial_ends_at`   TIMESTAMP    NULL,
    `created_at`      TIMESTAMP    NULL,
    `updated_at`      TIMESTAMP    NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `tenants_subdomain_unique` (`subdomain`),
    UNIQUE KEY `tenants_db_name_unique` (`db_name`),
    KEY `tenants_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  users
--  Holds login accounts for super-admins, school admins, teachers, students.
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`           BIGINT UNSIGNED NULL,
    `name`                VARCHAR(191) NOT NULL,
    `email`               VARCHAR(191) NOT NULL,
    `email_verified_at`   TIMESTAMP    NULL,
    `password`            VARCHAR(255) NOT NULL,
    `role`                ENUM('super_admin','admin','teacher','student') NOT NULL DEFAULT 'admin',
    `remember_token`      VARCHAR(100) NULL,
    `created_at`          TIMESTAMP    NULL,
    `updated_at`          TIMESTAMP    NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_tenant_id_index` (`tenant_id`),
    CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  password_reset_tokens
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
    `email`      VARCHAR(191) NOT NULL,
    `token`      VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP    NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  sessions
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
    `id`            VARCHAR(255) NOT NULL,
    `user_id`       BIGINT UNSIGNED NULL,
    `ip_address`    VARCHAR(45)  NULL,
    `user_agent`    TEXT         NULL,
    `payload`       LONGTEXT     NOT NULL,
    `last_activity` INT          NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  cache  (Laravel database cache driver)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
    `key`        VARCHAR(255) NOT NULL,
    `value`      LONGTEXT     NOT NULL,
    `expiration` INT          NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
    `key`        VARCHAR(255) NOT NULL,
    `owner`      VARCHAR(255) NOT NULL,
    `expiration` INT          NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
--  Table:  jobs / failed_jobs / job_batches  (Laravel queue driver)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue`        VARCHAR(255) NOT NULL,
    `payload`      LONGTEXT     NOT NULL,
    `attempts`     TINYINT UNSIGNED NOT NULL,
    `reserved_at`  INT UNSIGNED NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at`   INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
    `id`             VARCHAR(255) NOT NULL,
    `name`           VARCHAR(255) NOT NULL,
    `total_jobs`     INT          NOT NULL,
    `pending_jobs`   INT          NOT NULL,
    `failed_jobs`    INT          NOT NULL,
    `failed_job_ids` LONGTEXT     NOT NULL,
    `options`        MEDIUMTEXT   NULL,
    `cancelled_at`   INT          NULL,
    `created_at`     INT          NOT NULL,
    `finished_at`    INT          NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`       VARCHAR(255) NOT NULL,
    `connection` TEXT         NOT NULL,
    `queue`      TEXT         NOT NULL,
    `payload`    LONGTEXT     NOT NULL,
    `exception`  LONGTEXT     NOT NULL,
    `failed_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
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
--  End of master_db.sql
-- =============================================================================
