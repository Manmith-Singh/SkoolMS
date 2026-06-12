-- =============================================================================
--  SchoolMS — Master DB Sample Data
-- =============================================================================
--  Optional seed data for the master database.
--  Run AFTER `php artisan migrate` against `schoolms_master`.
--
--  Default credentials (change in production!):
--      superadmin@school.test / password
-- =============================================================================

USE `schoolms_master`;

-- Super-admin
-- Password = "password" (bcrypt)
INSERT INTO `users` (`tenant_id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(NULL, 'Super Admin', 'superadmin@school.test',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
 'super_admin', NOW(), NOW());

-- -----------------------------------------------------------------------------
--  Note: tenants are created via `php artisan tenant:create` or the
--  self-registration form on the master domain.  The provisioning command
--  also creates the matching MySQL database, so the master `tenants` table
--  will be populated automatically the first time a school signs up.
-- =============================================================================
