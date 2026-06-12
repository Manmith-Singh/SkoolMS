-- =============================================================================
--  SchoolMS — Sample Data
-- =============================================================================
--  Use this for local development / demos.  Apply it AFTER running
--  `php artisan migrate` against a fresh tenant database.
-- =============================================================================

USE `schoolms_<your_subdomain>`;
-- ↑ replace with your actual tenant DB name (e.g. schoolms_greenfield)

-- -----------------------------------------------------------------------------
--  Classes
-- -----------------------------------------------------------------------------
INSERT INTO `classes` (`id`, `name`, `section`, `capacity`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Grade 1', 'A', 40, 'First grade — section A', NOW(), NOW()),
(2, 'Grade 2', 'A', 40, 'Second grade — section A', NOW(), NOW()),
(3, 'Grade 3', 'A', 40, 'Third grade — section A', NOW(), NOW()),
(4, 'Grade 4', 'A', 40, 'Fourth grade — section A', NOW(), NOW()),
(5, 'Grade 5', 'A', 40, 'Fifth grade — section A', NOW(), NOW());

-- -----------------------------------------------------------------------------
--  Subjects
-- -----------------------------------------------------------------------------
INSERT INTO `subjects` (`class_id`, `name`, `code`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Mathematics',     'MTH-1', 'Basic numeracy',    NOW(), NOW()),
(1, 'English',         'ENG-1', 'Reading & writing', NOW(), NOW()),
(1, 'Science',         'SCI-1', 'Intro to science',  NOW(), NOW()),
(2, 'Mathematics',     'MTH-2', NULL,                NOW(), NOW()),
(2, 'English',         'ENG-2', NULL,                NOW(), NOW()),
(3, 'Mathematics',     'MTH-3', NULL,                NOW(), NOW()),
(3, 'Social Studies',  'SST-3', NULL,                NOW(), NOW()),
(4, 'Mathematics',     'MTH-4', NULL,                NOW(), NOW()),
(4, 'Science',         'SCI-4', NULL,                NOW(), NOW()),
(5, 'Mathematics',     'MTH-5', NULL,                NOW(), NOW()),
(5, 'English',         'ENG-5', NULL,                NOW(), NOW()),
(5, 'Computer Science','CS-5',  'Intro to computing', NOW(), NOW());

-- -----------------------------------------------------------------------------
--  Students  (12 sample students across 3 classes)
-- -----------------------------------------------------------------------------
INSERT INTO `students` (`admission_no`, `first_name`, `last_name`, `roll_no`, `dob`, `gender`, `email`, `phone`, `address`, `guardian_name`, `guardian_phone`, `admission_date`, `class_id`, `created_at`, `updated_at`) VALUES
('ADM-2024-0001', 'Aarav',  'Sharma',  '01', '2015-04-12', 'male',   'aarav@example.com',  '9876543210', '12 MG Road',  'Rakesh Sharma',  '9876543201', '2024-04-01', 1, NOW(), NOW()),
('ADM-2024-0002', 'Diya',   'Patel',   '02', '2015-08-22', 'female', 'diya@example.com',   '9876543211', '45 Park Ave',  'Suresh Patel',   '9876543202', '2024-04-01', 1, NOW(), NOW()),
('ADM-2024-0003', 'Vihaan', 'Kumar',   '03', '2015-01-30', 'male',   'vihaan@example.com', '9876543212', '78 Lake Rd',   'Anil Kumar',     '9876543203', '2024-04-01', 1, NOW(), NOW()),
('ADM-2024-0004', 'Anaya',  'Singh',   '04', '2015-06-15', 'female', 'anaya@example.com',  '9876543213', '90 Hill St',   'Pradeep Singh',  '9876543204', '2024-04-01', 1, NOW(), NOW()),
('ADM-2024-0005', 'Reyansh','Gupta',   '05', '2015-09-09', 'male',   'reyansh@example.com','9876543214', '23 River Ln',  'Manoj Gupta',    '9876543205', '2024-04-01', 2, NOW(), NOW()),
('ADM-2024-0006', 'Ishaani','Mehta',   '06', '2014-11-20', 'female', 'ishaani@example.com','9876543215', '11 Beach Rd',  'Rajiv Mehta',    '9876543206', '2024-04-01', 2, NOW(), NOW()),
('ADM-2024-0007', 'Arjun',  'Reddy',   '07', '2014-07-18', 'male',   'arjun@example.com',  '9876543216', '34 Forest Ave','Krishna Reddy',  '9876543207', '2024-04-01', 2, NOW(), NOW()),
('ADM-2024-0008', 'Saanvi', 'Joshi',   '08', '2014-03-05', 'female', 'saanvi@example.com', '9876543217', '67 Garden St', 'Vikas Joshi',    '9876543208', '2024-04-01', 3, NOW(), NOW()),
('ADM-2024-0009', 'Atharv', 'Verma',   '09', '2013-12-11', 'male',   'atharv@example.com', '9876543218', '89 Mountain Rd','Sunil Verma',  '9876543209', '2024-04-01', 3, NOW(), NOW()),
('ADM-2024-0010', 'Myra',   'Iyer',    '10', '2013-05-25', 'female', 'myra@example.com',   '9876543219', '15 Valley Rd', 'Ramesh Iyer',    '9876543210', '2024-04-01', 3, NOW(), NOW()),
('ADM-2024-0011', 'Kiaan',  'Khan',    '11', '2012-10-14', 'male',   'kiaan@example.com',  '9876543220', '50 Desert Ave','Imran Khan',     '9876543211', '2024-04-01', 4, NOW(), NOW()),
('ADM-2024-0012', 'Aadhya', 'Nair',    '12', '2012-02-28', 'female', 'aadhya@example.com', '9876543221', '22 Ocean Blvd','Ravi Nair',      '9876543212', '2024-04-01', 4, NOW(), NOW());

-- -----------------------------------------------------------------------------
--  Teachers
-- -----------------------------------------------------------------------------
INSERT INTO `teachers` (`employee_id`, `first_name`, `last_name`, `email`, `phone`, `qualification`, `hire_date`, `gender`, `salary`, `subject_id`, `created_at`, `updated_at`) VALUES
('T-2024-0001', 'Sunita',  'Deshmukh', 'sunita.deshmukh@school.test',  '9988776601', 'M.Sc, B.Ed',     '2018-06-15', 'female', 45000.00, 1,  NOW(), NOW()),
('T-2024-0002', 'Rajesh',  'Pillai',   'rajesh.pillai@school.test',    '9988776602', 'M.A, B.Ed',      '2019-07-20', 'male',   42000.00, 2,  NOW(), NOW()),
('T-2024-0003', 'Anita',   'Bose',     'anita.bose@school.test',       '9988776603', 'M.Sc, M.Ed',     '2020-08-10', 'female', 48000.00, 3,  NOW(), NOW()),
('T-2024-0004', 'Mohan',   'Chandra',  'mohan.chandra@school.test',    '9988776604', 'M.Tech, B.Ed',   '2017-04-05', 'male',   55000.00, 12, NOW(), NOW()),
('T-2024-0005', 'Kavitha', 'Raman',    'kavitha.raman@school.test',    '9988776605', 'M.A, B.Ed',      '2021-01-12', 'female', 43000.00, 7,  NOW(), NOW());

-- -----------------------------------------------------------------------------
--  Fee categories  (TenantSeeder would normally do this — included for reference)
-- -----------------------------------------------------------------------------
INSERT INTO `fee_categories` (`name`, `description`, `default_amount`, `frequency`, `is_active`, `created_at`, `updated_at`) VALUES
('Tuition Fee',     'Regular tuition',           1500.00, 'monthly',     1, NOW(), NOW()),
('Transport Fee',   'School bus transport',       500.00, 'monthly',     1, NOW(), NOW()),
('Library Fee',     'Library & books',            200.00, 'annually',    1, NOW(), NOW()),
('Examination Fee', 'Mid-term and final exams',   300.00, 'quarterly',   1, NOW(), NOW()),
('Lab Fee',         'Science / computer lab',     250.00, 'half_yearly', 1, NOW(), NOW()),
('Sports Fee',      'Sports & extracurriculars',  150.00, 'annually',    1, NOW(), NOW()),
('Admission Fee',   'One-time admission charge', 2000.00, 'one_time',    1, NOW(), NOW());

-- -----------------------------------------------------------------------------
--  Exams
-- -----------------------------------------------------------------------------
INSERT INTO `exams` (`name`, `class_id`, `subject_id`, `date`, `max_marks`, `pass_marks`, `description`, `created_at`, `updated_at`) VALUES
('Mid-term Math',      1, 1, '2024-09-15', 100.00, 33.00, 'First semester math exam', NOW(), NOW()),
('Mid-term English',   1, 2, '2024-09-18', 100.00, 33.00, 'First semester English',  NOW(), NOW()),
('Mid-term Science',   1, 3, '2024-09-20', 100.00, 33.00, 'First semester science',  NOW(), NOW()),
('Mid-term Math',      2, 4, '2024-09-15', 100.00, 33.00, NULL, NOW(), NOW()),
('Final Math',         1, 1, '2025-03-15', 100.00, 33.00, 'Final math exam',         NOW(), NOW());

-- -----------------------------------------------------------------------------
--  Results  (a few sample marks)
-- -----------------------------------------------------------------------------
INSERT INTO `results` (`exam_id`, `student_id`, `marks_obtained`, `grade`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 85.00, 'A',  'Excellent work', NOW(), NOW()),
(1, 2, 78.00, 'B+', 'Very good',      NOW(), NOW()),
(1, 3, 92.00, 'A+', 'Outstanding',    NOW(), NOW()),
(1, 4, 65.00, 'B',  'Good',           NOW(), NOW()),
(2, 1, 72.00, 'B+', NULL,             NOW(), NOW()),
(2, 2, 88.00, 'A',  NULL,             NOW(), NOW()),
(2, 3, 55.00, 'C',  'Needs practice', NOW(), NOW()),
(2, 4, 91.00, 'A+', NULL,             NOW(), NOW()),
(3, 1, 70.00, 'B',  NULL,             NOW(), NOW()),
(3, 2, 82.00, 'A',  NULL,             NOW(), NOW()),
(3, 3, 76.00, 'B+', NULL,             NOW(), NOW()),
(3, 4, 68.00, 'B',  NULL,             NOW(), NOW());

-- -----------------------------------------------------------------------------
--  Fees  (assign monthly tuition + transport to all students)
-- -----------------------------------------------------------------------------
INSERT INTO `fees` (`student_id`, `category_id`, `amount`, `paid_amount`, `due_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1500.00, 1500.00, '2024-10-05', 'paid',    NULL, NOW(), NOW()),
(1, 2,  500.00,  500.00, '2024-10-05', 'paid',    NULL, NOW(), NOW()),
(2, 1, 1500.00,  500.00, '2024-10-05', 'partial', NULL, NOW(), NOW()),
(2, 2,  500.00,    0.00, '2024-10-05', 'pending', NULL, NOW(), NOW()),
(3, 1, 1500.00,    0.00, '2024-10-05', 'pending', NULL, NOW(), NOW()),
(3, 2,  500.00,    0.00, '2024-10-05', 'pending', NULL, NOW(), NOW()),
(4, 1, 1500.00, 1500.00, '2024-10-05', 'paid',    NULL, NOW(), NOW()),
(4, 3,  200.00,  200.00, '2024-04-15', 'paid',    'Annual library', NOW(), NOW()),
(5, 1, 1500.00,    0.00, '2024-10-05', 'overdue', NULL, NOW(), NOW()),
(5, 2,  500.00,    0.00, '2024-10-05', 'overdue', NULL, NOW(), NOW()),
(6, 1, 1500.00, 1500.00, '2024-10-05', 'paid',    NULL, NOW(), NOW()),
(7, 1, 1500.00,  750.00, '2024-10-05', 'partial', NULL, NOW(), NOW()),
(8, 1, 1500.00, 1500.00, '2024-10-05', 'paid',    NULL, NOW(), NOW()),
(9, 1, 1500.00,    0.00, '2024-10-05', 'pending', NULL, NOW(), NOW()),
(10, 1, 1500.00, 1500.00, '2024-10-05', 'paid',   NULL, NOW(), NOW()),
(11, 1, 1500.00,    0.00, '2024-10-05', 'pending', NULL, NOW(), NOW()),
(12, 1, 1500.00, 1500.00, '2024-10-05', 'paid',   NULL, NOW(), NOW());

-- -----------------------------------------------------------------------------
--  Fee payments
-- -----------------------------------------------------------------------------
INSERT INTO `fee_payments` (`fee_id`, `student_id`, `amount_paid`, `payment_date`, `mode`, `transaction_ref`, `receipt_no`, `notes`, `received_by`, `created_at`, `updated_at`) VALUES
(1,  1,  1500.00, '2024-10-02', 'cash',          NULL,            'RCP-20241002-0001', NULL, 'Sunita Deshmukh',  NOW(), NOW()),
(2,  1,   500.00, '2024-10-02', 'cash',          NULL,            'RCP-20241002-0002', NULL, 'Sunita Deshmukh',  NOW(), NOW()),
(3,  2,   500.00, '2024-10-04', 'cheque',        'CHQ-998877',    'RCP-20241004-0001', NULL, 'Sunita Deshmukh',  NOW(), NOW()),
(7,  4,  1500.00, '2024-10-03', 'bank_transfer', 'TXN-554433',    'RCP-20241003-0001', NULL, 'Sunita Deshmukh',  NOW(), NOW()),
(8,  4,   200.00, '2024-04-10', 'cash',          NULL,            'RCP-20240410-0001', 'Annual', 'Sunita Deshmukh',  NOW(), NOW()),
(11, 6,  1500.00, '2024-10-01', 'online',        'UPI-99881234',  'RCP-20241001-0001', NULL, 'Sunita Deshmukh',  NOW(), NOW()),
(13, 7,   750.00, '2024-10-05', 'cash',          NULL,            'RCP-20241005-0001', NULL, 'Sunita Deshmukh',  NOW(), NOW()),
(14, 8,  1500.00, '2024-10-04', 'cash',          NULL,            'RCP-20241004-0002', NULL, 'Sunita Deshmukh',  NOW(), NOW()),
(16, 10, 1500.00, '2024-10-02', 'card',          'CRD-778899',    'RCP-20241002-0003', NULL, 'Sunita Deshmukh',  NOW(), NOW()),
(18, 12, 1500.00, '2024-10-03', 'cash',          NULL,            'RCP-20241003-0002', NULL, 'Sunita Deshmukh',  NOW(), NOW());

-- -----------------------------------------------------------------------------
--  Attendance  (last 5 days for all students in class 1)
-- -----------------------------------------------------------------------------
INSERT INTO `attendance` (`student_id`, `class_id`, `date`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, '2024-10-01', 'present', NULL, NOW(), NOW()),
(2, 1, '2024-10-01', 'present', NULL, NOW(), NOW()),
(3, 1, '2024-10-01', 'absent',  'Sick', NOW(), NOW()),
(4, 1, '2024-10-01', 'present', NULL, NOW(), NOW()),
(1, 1, '2024-10-02', 'present', NULL, NOW(), NOW()),
(2, 1, '2024-10-02', 'late',    'Bus delay', NOW(), NOW()),
(3, 1, '2024-10-02', 'absent',  'Sick', NOW(), NOW()),
(4, 1, '2024-10-02', 'present', NULL, NOW(), NOW()),
(1, 1, '2024-10-03', 'present', NULL, NOW(), NOW()),
(2, 1, '2024-10-03', 'present', NULL, NOW(), NOW()),
(3, 1, '2024-10-03', 'present', NULL, NOW(), NOW()),
(4, 1, '2024-10-03', 'half_day','Left early', NOW(), NOW());

-- =============================================================================
--  End of sample_data.sql
-- =============================================================================
