-- ========================================================
-- PDH Nutrition System - Complete Installation SQL for Server 240
-- Compatible with MySQL users without REFERENCES privilege
-- Database: pdhnutrition
-- Pluakdaeng Hospital Clinical Nutrition Management System
-- ========================================================

CREATE DATABASE IF NOT EXISTS `pdhnutrition` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pdhnutrition`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NULL,
  `role` VARCHAR(30) NOT NULL DEFAULT 'DIETITIAN',
  `status` ENUM('ACTIVE', 'INACTIVE', 'SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Roles Table
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_code` VARCHAR(30) NOT NULL UNIQUE,
  `role_name_th` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. User Roles Mapping
DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `user_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  INDEX `idx_ur_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Patients Cache
DROP TABLE IF EXISTS `patients_cache`;
CREATE TABLE `patients_cache` (
  `hn` VARCHAR(20) PRIMARY KEY,
  `cid` VARCHAR(20) NULL,
  `prefix` VARCHAR(20) NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `fullname` VARCHAR(110) NOT NULL,
  `gender` ENUM('MALE', 'FEMALE', 'OTHER') NOT NULL DEFAULT 'MALE',
  `birthdate` DATE NULL,
  `age` INT NULL,
  `phone` VARCHAR(30) NULL,
  `weight` DECIMAL(5,2) NULL,
  `height` DECIMAL(5,2) NULL,
  `bmi` DECIMAL(4,2) NULL,
  `synced_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_patients_cid` (`cid`),
  INDEX `idx_patients_fullname` (`fullname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Visits Cache
DROP TABLE IF EXISTS `visits_cache`;
CREATE TABLE `visits_cache` (
  `vn` VARCHAR(30) PRIMARY KEY,
  `hn` VARCHAR(20) NOT NULL,
  `visit_date` DATE NOT NULL,
  `visit_time` TIME NOT NULL,
  `clinic` VARCHAR(100) NOT NULL,
  `department` VARCHAR(100) NULL,
  `doctor` VARCHAR(100) NULL,
  `queue_number` VARCHAR(20) NULL,
  `synced_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_visits_hn` (`hn`),
  INDEX `idx_visits_date` (`visit_date`),
  INDEX `idx_visits_clinic` (`clinic`),
  INDEX `idx_visits_doctor` (`doctor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Admissions Cache
DROP TABLE IF EXISTS `admissions_cache`;
CREATE TABLE `admissions_cache` (
  `an` VARCHAR(30) PRIMARY KEY,
  `hn` VARCHAR(20) NOT NULL,
  `admit_date` DATE NOT NULL,
  `ward` VARCHAR(100) NOT NULL,
  `bed` VARCHAR(30) NULL,
  `status` ENUM('ADMITTED', 'DISCHARGED') NOT NULL DEFAULT 'ADMITTED',
  `synced_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_adm_hn` (`hn`),
  INDEX `idx_adm_ward` (`ward`),
  INDEX `idx_adm_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Diagnosis Cache
DROP TABLE IF EXISTS `diagnosis_cache`;
CREATE TABLE `diagnosis_cache` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vn` VARCHAR(30) NULL,
  `an` VARCHAR(30) NULL,
  `hn` VARCHAR(20) NOT NULL,
  `icd10` VARCHAR(20) NOT NULL,
  `diagnosis_name` VARCHAR(255) NOT NULL,
  `category` ENUM('PRIMARY', 'SECONDARY', 'OTHER') DEFAULT 'PRIMARY',
  INDEX `idx_diag_hn` (`hn`),
  INDEX `idx_diag_vn` (`vn`),
  INDEX `idx_diag_an` (`an`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Lab Cache
DROP TABLE IF EXISTS `lab_cache`;
CREATE TABLE `lab_cache` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hn` VARCHAR(20) NOT NULL,
  `vn` VARCHAR(30) NULL,
  `test_name` VARCHAR(50) NOT NULL,
  `result_value` DECIMAL(8,2) NOT NULL,
  `result_unit` VARCHAR(20) NULL,
  `result_date` DATE NOT NULL,
  `result_time` TIME NULL,
  INDEX `idx_lab_hn` (`hn`),
  INDEX `idx_lab_test` (`test_name`),
  INDEX `idx_lab_date` (`result_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Nutrition Registry
DROP TABLE IF EXISTS `nutrition_registry`;
CREATE TABLE `nutrition_registry` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hn` VARCHAR(20) NOT NULL UNIQUE,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `reason` VARCHAR(255) NOT NULL,
  `risk_level` ENUM('LOW', 'MEDIUM', 'HIGH', 'SEVERE') NOT NULL DEFAULT 'MEDIUM',
  `start_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `follow_up_interval_days` INT NOT NULL DEFAULT 14,
  `must_review_every_visit` TINYINT(1) NOT NULL DEFAULT 1,
  `note` TEXT NULL,
  `created_by` INT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_registry_hn` (`hn`),
  INDEX `idx_registry_active` (`active`),
  INDEX `idx_registry_risk` (`risk_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Nutrition Tasks
DROP TABLE IF EXISTS `nutrition_tasks`;
CREATE TABLE `nutrition_tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hn` VARCHAR(20) NOT NULL,
  `vn` VARCHAR(30) NOT NULL,
  `visit_date` DATE NOT NULL,
  `clinic` VARCHAR(100) NOT NULL,
  `doctor` VARCHAR(100) NULL,
  `task_type` VARCHAR(50) NOT NULL DEFAULT 'PRE_DOCTOR_ASSESSMENT',
  `risk_level` ENUM('LOW', 'MEDIUM', 'HIGH', 'SEVERE') NOT NULL DEFAULT 'MEDIUM',
  `status` ENUM('WAITING_NUTRITION', 'IN_ASSESSMENT', 'NUTRITION_COMPLETED', 'READY_FOR_DOCTOR', 'REFERRED', 'MISSED', 'CANCELLED') NOT NULL DEFAULT 'WAITING_NUTRITION',
  `priority` INT NOT NULL DEFAULT 3,
  `assigned_to` INT NULL,
  `locked_by` INT NULL,
  `started_at` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_hn_vn_task` (`hn`, `vn`, `task_type`),
  INDEX `idx_tasks_date` (`visit_date`),
  INDEX `idx_tasks_status` (`status`),
  INDEX `idx_tasks_clinic` (`clinic`),
  INDEX `idx_tasks_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. NAF Rules Engine Table
DROP TABLE IF EXISTS `naf_rules`;
CREATE TABLE `naf_rules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `section` INT NOT NULL,
  `item_code` VARCHAR(50) NOT NULL,
  `label_th` VARCHAR(255) NOT NULL,
  `label_en` VARCHAR(255) NULL,
  `score` INT NOT NULL DEFAULT 0,
  `condition_type` VARCHAR(50) NOT NULL DEFAULT 'OPTION',
  `min_value` DECIMAL(8,2) NULL,
  `max_value` DECIMAL(8,2) NULL,
  `operator` VARCHAR(10) NULL,
  `version` INT NOT NULL DEFAULT 1,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `effective_from` DATE NULL,
  `effective_to` DATE NULL,
  INDEX `idx_rules_sec` (`section`),
  INDEX `idx_rules_ver` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. NAF Assessments Table
DROP TABLE IF EXISTS `naf_assessments`;
CREATE TABLE `naf_assessments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hn` VARCHAR(20) NOT NULL,
  `vn` VARCHAR(30) NULL,
  `an` VARCHAR(30) NULL,
  `assessment_date` DATE NOT NULL,
  `assessment_time` TIME NOT NULL,
  `height_cm` DECIMAL(5,2) NULL,
  `height_source` ENUM('HIS', 'Measured', 'Arm span', 'Previous') DEFAULT 'HIS',
  `arm_span_cm` DECIMAL(5,2) NULL,
  `weight_kg` DECIMAL(5,2) NULL,
  `weight_method` ENUM('Standing', 'Bed scale', 'Unable', 'Amputation') DEFAULT 'Standing',
  `bmi` DECIMAL(4,2) NULL,
  `bmi_score` INT DEFAULT 0,
  `albumin` DECIMAL(4,2) NULL,
  `albumin_score` INT DEFAULT 0,
  `wbc` DECIMAL(8,2) NULL,
  `lymphocyte` DECIMAL(5,2) NULL,
  `tlc` DECIMAL(8,2) NULL,
  `tlc_score` INT DEFAULT 0,
  `total_score` INT NOT NULL DEFAULT 0,
  `naf_grade` ENUM('NAF A', 'NAF B', 'NAF C') NOT NULL DEFAULT 'NAF A',
  `rule_version` INT NOT NULL DEFAULT 1,
  `assessor_id` INT NOT NULL,
  `status` ENUM('DRAFT', 'COMPLETED', 'CANCELLED') NOT NULL DEFAULT 'COMPLETED',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_naf_hn` (`hn`),
  INDEX `idx_naf_vn` (`vn`),
  INDEX `idx_naf_an` (`an`),
  INDEX `idx_naf_date` (`assessment_date`),
  INDEX `idx_naf_grade` (`naf_grade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. NAF Answers Breakdown
DROP TABLE IF EXISTS `naf_answers`;
CREATE TABLE `naf_answers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `assessment_id` INT NOT NULL,
  `section_number` INT NOT NULL,
  `item_code` VARCHAR(50) NOT NULL,
  `score_given` INT NOT NULL DEFAULT 0,
  `user_confirmed_diagnosis` TINYINT(1) DEFAULT 0,
  INDEX `idx_na_ass_id` (`assessment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Anthropometric Records History
DROP TABLE IF EXISTS `anthropometric_records`;
CREATE TABLE `anthropometric_records` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hn` VARCHAR(20) NOT NULL,
  `vn` VARCHAR(30) NULL,
  `record_date` DATE NOT NULL,
  `weight_kg` DECIMAL(5,2) NULL,
  `height_cm` DECIMAL(5,2) NULL,
  `bmi` DECIMAL(4,2) NULL,
  `arm_span_cm` DECIMAL(5,2) NULL,
  `source` VARCHAR(30) DEFAULT 'NAF_ASSESSMENT',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_anthro_hn` (`hn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Diet Orders Table
DROP TABLE IF EXISTS `diet_orders`;
CREATE TABLE `diet_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `assessment_id` INT NULL,
  `hn` VARCHAR(20) NOT NULL,
  `vn` VARCHAR(30) NULL,
  `weight_kg` DECIMAL(5,2) NOT NULL,
  `height_cm` DECIMAL(5,2) NOT NULL,
  `ibw_kg` DECIMAL(5,2) NOT NULL,
  `energy_kcal_per_ibw` VARCHAR(20) NOT NULL,
  `total_energy_kcal` DECIMAL(7,2) NOT NULL,
  `protein_g_per_ibw` VARCHAR(20) NOT NULL,
  `total_protein_g` DECIMAL(6,2) NOT NULL,
  `diet_types_json` TEXT NOT NULL,
  `required_diet_note` TEXT NULL,
  `ordered_by` INT NOT NULL,
  `status` ENUM('ACTIVE', 'DISCONTINUED', 'COMPLETED') DEFAULT 'ACTIVE',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_diet_hn` (`hn`),
  INDEX `idx_diet_vn` (`vn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Oral Supplements
DROP TABLE IF EXISTS `oral_supplements`;
CREATE TABLE `oral_supplements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `diet_order_id` INT NOT NULL,
  `formula_type` VARCHAR(100) NOT NULL,
  `ml_per_meal` INT NOT NULL,
  `meal` VARCHAR(100) NULL,
  `frequency_per_day` INT NOT NULL DEFAULT 3,
  `total_daily_ml` INT NOT NULL,
  `note` VARCHAR(255) NULL,
  INDEX `idx_os_diet` (`diet_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Tube Feedings
DROP TABLE IF EXISTS `tube_feedings`;
CREATE TABLE `tube_feedings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `diet_order_id` INT NOT NULL,
  `formula_type` VARCHAR(100) NOT NULL,
  `ml_per_meal` INT NOT NULL,
  `meal` VARCHAR(100) NULL,
  `frequency_per_day` INT NOT NULL DEFAULT 4,
  `total_daily_ml` INT NOT NULL,
  `note` VARCHAR(255) NULL,
  INDEX `idx_tf_diet` (`diet_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Nutrition Clinical Notes (SOAP)
DROP TABLE IF EXISTS `nutrition_notes`;
CREATE TABLE `nutrition_notes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `assessment_id` INT NULL,
  `hn` VARCHAR(20) NOT NULL,
  `vn` VARCHAR(30) NULL,
  `subjective` TEXT NULL,
  `objective` TEXT NULL,
  `assessment_text` TEXT NULL,
  `plan_text` TEXT NULL,
  `dietitian_id` INT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_notes_hn` (`hn`),
  INDEX `idx_notes_vn` (`vn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Nutrition Follow-ups Tracker
DROP TABLE IF EXISTS `nutrition_followups`;
CREATE TABLE `nutrition_followups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hn` VARCHAR(20) NOT NULL,
  `last_assessment_id` INT NULL,
  `next_follow_up_date` DATE NOT NULL,
  `follow_up_interval_days` INT NOT NULL DEFAULT 14,
  `must_review_every_visit` TINYINT(1) NOT NULL DEFAULT 1,
  `follow_up_reason` VARCHAR(255) NULL,
  `status` ENUM('PENDING', 'COMPLETED', 'OVERDUE', 'CANCELLED') NOT NULL DEFAULT 'PENDING',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_fu_hn` (`hn`),
  INDEX `idx_fu_next_date` (`next_follow_up_date`),
  INDEX `idx_fu_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. System Settings
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `setting_key` VARCHAR(50) PRIMARY KEY,
  `setting_value` TEXT NOT NULL,
  `description` VARCHAR(255) NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Audit Logs
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `username` VARCHAR(50) NULL,
  `role` VARCHAR(30) NULL,
  `action` VARCHAR(50) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `record_id` VARCHAR(50) NULL,
  `hn` VARCHAR(20) NULL,
  `vn` VARCHAR(30) NULL,
  `old_value` LONGTEXT NULL,
  `new_value` LONGTEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_audit_action` (`action`),
  INDEX `idx_audit_module` (`module`),
  INDEX `idx_audit_user` (`user_id`),
  INDEX `idx_audit_hn` (`hn`),
  INDEX `idx_audit_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- SEED DATA INSERTION
-- ========================================================

-- Roles
INSERT INTO `roles` (`role_code`, `role_name_th`, `description`) VALUES
('SUPER_ADMIN', 'ผู้ดูแลระบบสูงสุด', 'เข้าถึงได้ทุกส่วนของระบบ'),
('ADMIN', 'ผู้ดูแลระบบ', 'จัดการผู้ใช้งานและตั้งค่าระบบ'),
('DIETITIAN', 'นักโภชนาการ', 'ประเมิน NAF, Diet Order, Clinical Note, Registry'),
('DOCTOR', 'แพทย์', 'ดูผลการประเมิน Diet Order และอนุมัติ'),
('NURSE', 'พยาบาล', 'ดูผลการประเมิน คัดกรองเบื้องต้น'),
('PHARMACIST', 'เภสัชกร', 'ตรวจสอบโภชนบำบัดร่วมกับยา'),
('VIEWER', 'ผู้ดูข้อมูล', 'สิทธิ์อ่านอย่างเดียว')
ON DUPLICATE KEY UPDATE `role_name_th` = VALUES(`role_name_th`);

-- Default Users (password: password123)
INSERT INTO `users` (`id`, `username`, `password_hash`, `fullname`, `email`, `role`, `status`) VALUES
(1, 'admin', '$2y$10$QNLsf3Dv92ZidgozkY7FIeuzEqdYKN8t1q/5H/Arv0WegqQh40mVu', 'ผู้ดูแลระบบ ปลวกแดง', 'admin@pluakdaeng.go.th', 'ADMIN', 'ACTIVE'),
(2, 'dietitian1', '$2y$10$QNLsf3Dv92ZidgozkY7FIeuzEqdYKN8t1q/5H/Arv0WegqQh40mVu', 'นักโภชนาการ สมศรี มีสุข (ภน.)', 'dietitian@pluakdaeng.go.th', 'DIETITIAN', 'ACTIVE'),
(3, 'doctor1', '$2y$10$QNLsf3Dv92ZidgozkY7FIeuzEqdYKN8t1q/5H/Arv0WegqQh40mVu', 'นพ. สมชาย ใจดี', 'doctor@pluakdaeng.go.th', 'DOCTOR', 'ACTIVE'),
(4, 'nurse1', '$2y$10$QNLsf3Dv92ZidgozkY7FIeuzEqdYKN8t1q/5H/Arv0WegqQh40mVu', 'พว. สายฝน ห่วงใย', 'nurse@pluakdaeng.go.th', 'NURSE', 'ACTIVE')
ON DUPLICATE KEY UPDATE `fullname` = VALUES(`fullname`);

-- NAF Rules Engine Data
INSERT INTO `naf_rules` (`section`, `item_code`, `label_th`, `label_en`, `score`, `condition_type`, `min_value`, `max_value`, `operator`, `version`, `active`) VALUES
(2, 'BMI_LT_17', 'BMI น้อยกว่า 17.0 kg/m²', 'BMI < 17.0', 2, 'NUMERIC_RANGE', 0.00, 16.99, '<', 1, 1),
(2, 'BMI_17_18', 'BMI 17.0 - 18.0 kg/m²', 'BMI 17.0 - 18.0', 1, 'NUMERIC_RANGE', 17.00, 18.00, 'BETWEEN', 1, 1),
(2, 'BMI_18_29', 'BMI 18.1 - 29.9 kg/m²', 'BMI 18.1 - 29.9', 0, 'NUMERIC_RANGE', 18.10, 29.90, 'BETWEEN', 1, 1),
(2, 'BMI_GTE_30', 'BMI 30.0 kg/m² ขึ้นไป', 'BMI >= 30.0', 1, 'NUMERIC_RANGE', 30.00, 999.00, '>=', 1, 1),
(2, 'ALB_LT_2_5', 'Albumin < 2.5 g/dL', 'Albumin < 2.5', 3, 'NUMERIC_RANGE', 0.00, 2.49, '<', 1, 1),
(2, 'ALB_2_6_2_9', 'Albumin 2.6 - 2.9 g/dL', 'Albumin 2.6 - 2.9', 2, 'NUMERIC_RANGE', 2.60, 2.90, 'BETWEEN', 1, 1),
(2, 'ALB_3_0_3_5', 'Albumin 3.0 - 3.5 g/dL', 'Albumin 3.0 - 3.5', 1, 'NUMERIC_RANGE', 3.00, 3.50, 'BETWEEN', 1, 1),
(2, 'ALB_GT_3_5', 'Albumin > 3.5 g/dL', 'Albumin > 3.5', 0, 'NUMERIC_RANGE', 3.51, 99.00, '>', 1, 1),
(2, 'TLC_LTE_1000', 'TLC <= 1000 cells/mm³', 'TLC <= 1000', 3, 'NUMERIC_RANGE', 0.00, 1000.00, '<=', 1, 1),
(2, 'TLC_1001_1200', 'TLC 1001 - 1200 cells/mm³', 'TLC 1001 - 1200', 2, 'NUMERIC_RANGE', 1001.00, 1200.00, 'BETWEEN', 1, 1),
(2, 'TLC_1201_1500', 'TLC 1201 - 1500 cells/mm³', 'TLC 1201 - 1500', 1, 'NUMERIC_RANGE', 1201.00, 1500.00, 'BETWEEN', 1, 1),
(2, 'TLC_GT_1500', 'TLC > 1500 cells/mm³', 'TLC > 1500', 0, 'NUMERIC_RANGE', 1501.00, 99999.00, '>', 1, 1),
(3, 'BUILD_VERY_THIN', 'ผอมมาก (Very thin)', 'Very thin', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(3, 'BUILD_THIN', 'ผอม (Thin)', 'Thin', 1, 'OPTION', NULL, NULL, '=', 1, 1),
(3, 'BUILD_NORMAL', 'ปกติ (Normal)', 'Normal', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(3, 'BUILD_OBESE', 'อ้วน (Obese)', 'Obese', 1, 'OPTION', NULL, NULL, '=', 1, 1),
(4, 'WT_DECREASED', 'ลดลง / ผอมลง', 'Decreased / thinner', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(4, 'WT_INCREASED', 'เพิ่มขึ้น / อ้วนขึ้น', 'Increased / fatter', 1, 'OPTION', NULL, NULL, '=', 1, 1),
(4, 'WT_SAME', 'เท่าเดิม', 'Same', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(4, 'WT_UNKNOWN', 'ไม่ทราบ', 'Unknown', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(51, 'FOOD_WATER', 'อาหารน้ำ', 'Water / liquid diet', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(51, 'FOOD_LIQUID', 'อาหารเหลว', 'Full liquid diet', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(51, 'FOOD_SOFT', 'อาหารนุ่มกว่าปกติ', 'Soft diet', 1, 'OPTION', NULL, NULL, '=', 1, 1),
(51, 'FOOD_NORMAL', 'อาหารเหมือนปกติ', 'Normal diet', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(52, 'AMOUNT_VERY_LITTLE', 'กินน้อยมาก ( < 25% )', 'Eats very little', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(52, 'AMOUNT_LESS', 'กินน้อยลง ( 25-50% )', 'Eats less', 1, 'OPTION', NULL, NULL, '=', 1, 1),
(52, 'AMOUNT_NORMAL', 'กินเท่าปกติ', 'Eats normal', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(52, 'AMOUNT_MORE', 'กินมากขึ้น', 'Eats more', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(61, 'SYM_CHOKING', 'สำลักอาหาร/น้ำ', 'Choking', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(61, 'SYM_SWALLOW_DIFFICULT', 'เคี้ยว/กลืนลำบาก/ได้อาหารทางสายยาง', 'Swallowing difficulty / Tube feeding', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(61, 'SYM_SWALLOW_NORMAL', 'กลืนได้ปกติ', 'Normal swallowing', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(62, 'SYM_DIARRHEA', 'ท้องเสียเรื้อรัง', 'Chronic diarrhea', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(62, 'SYM_ABD_PAIN', 'ปวดท้องเรื้อรัง', 'Abdominal pain', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(62, 'SYM_GI_NORMAL', 'ระบบทางเดินอาหารปกติ', 'Normal GI', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(63, 'SYM_VOMITING', 'อาเจียนหลังอาหาร', 'Vomiting', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(63, 'SYM_NAUSEA', 'คลื่นไส้เป็นประจำ', 'Nausea', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(63, 'SYM_MEAL_NORMAL', 'ไม่มีอาการระหว่างกินอาหาร', 'Normal meal', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(7, 'ACCESS_LIMIT', 'มีข้อจำกัดในการเข้าถึงอาหาร/ซื้ออาหารเองไม่ได้', 'Limited access to food', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(7, 'ACCESS_ASSIST', 'ต้องมีผู้ช่วยเตรียมอาหาร', 'Needs assistance', 1, 'OPTION', NULL, NULL, '=', 1, 1),
(7, 'ACCESS_NORMAL', 'สามารถเข้าถึงและรับประทานอาหารได้ปกติ', 'Independent access', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(81, 'DIS_DM', 'โรคเบาหวาน (Diabetes Mellitus)', 'DM', 3, 'DISEASE', NULL, NULL, '=', 1, 1),
(81, 'DIS_CKD_ESRD', 'โรคไตเรื้อรัง/ไตวายระยะสุดท้าย (CKD/ESRD)', 'CKD/ESRD', 3, 'DISEASE', NULL, NULL, '=', 1, 1),
(81, 'DIS_SEPTICEMIA', 'ภาวะติดเชื้อในกระแสเลือด (Septicemia)', 'Septicemia', 3, 'DISEASE', NULL, NULL, '=', 1, 1),
(81, 'DIS_SOLID_CANCER', 'มะเร็งชนิดก้อน (Solid Cancer)', 'Solid Cancer', 3, 'DISEASE', NULL, NULL, '=', 1, 1),
(81, 'DIS_CHF', 'ภาวะหัวใจล้มเหลวเรื้อรัง (Chronic Heart Failure)', 'CHF', 3, 'DISEASE', NULL, NULL, '=', 1, 1),
(81, 'DIS_HIP_FX', 'กระดูกสะโพกหัก (Hip Fracture)', 'Hip Fracture', 3, 'DISEASE', NULL, NULL, '=', 1, 1),
(81, 'DIS_COPD', 'โรคปอดอุดกั้นเรื้อรัง (COPD)', 'COPD', 3, 'DISEASE', NULL, NULL, '=', 1, 1),
(81, 'DIS_HEAD_INJURY', 'การบาดเจ็บรุนแรงที่ศีรษะ (Severe Head Injury)', 'Head Injury', 3, 'DISEASE', NULL, NULL, '=', 1, 1),
(81, 'DIS_BURN_2DEG', 'แผลไฟไหม้ระดับ 2 ขึ้นไป (>= 2nd Degree Burn)', 'Burn >= 2nd deg', 3, 'DISEASE', NULL, NULL, '=', 1, 1),
(81, 'DIS_CLD', 'โรคตับเรื้อรัง/ตับแข็ง (CLD / Cirrhosis)', 'CLD / Cirrhosis', 3, 'DISEASE', NULL, NULL, '=', 1, 1),
(82, 'DIS_SEVERE_PNEUMONIA', 'ปอดบวมรุนแรง (Severe Pneumonia)', 'Severe Pneumonia', 6, 'DISEASE', NULL, NULL, '=', 1, 1),
(82, 'DIS_CRITICALLY_ILL', 'ผู้ป่วยภาวะวิกฤต (Critically Ill)', 'Critically Ill', 6, 'DISEASE', NULL, NULL, '=', 1, 1),
(82, 'DIS_MULTI_FX', 'กระดูกหักหลายตำแหน่ง (Multiple Fracture)', 'Multiple FX', 6, 'DISEASE', NULL, NULL, '=', 1, 1),
(82, 'DIS_STROKE_CVA', 'โรคหลอดเลือดสมอง (Stroke / CVA)', 'Stroke / CVA', 6, 'DISEASE', NULL, NULL, '=', 1, 1),
(82, 'DIS_MALIG_HEM', 'มะเร็งระบบเลือด (Malignant Hematologic Disease)', 'Malignant Hem', 6, 'DISEASE', NULL, NULL, '=', 1, 1),
(82, 'DIS_BMT', 'ปลูกถ่ายไขกระดูก (Bone Marrow Transplant)', 'BMT', 6, 'DISEASE', NULL, NULL, '=', 1, 1);

-- System Settings Initial Values
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('hospital_name_th', 'โรงพยาบาลปลวกแดง', 'ชื่อโรงพยาบาลภาษาไทย'),
('hospital_name_en', 'Pluakdaeng Hospital', 'ชื่อโรงพยาบาลภาษาอังกฤษ'),
('auto_create_queue_task', '1', 'สร้าง Nutrition Queue Task อัตโนมัติเมื่อผู้ป่วยใน Registry มา Visit'),
('naf_active_version', '1', 'เวอร์ชัน NAF Rule Engine ที่ใช้งานปัจจุบัน'),
('followup_default_interval_days', '14', 'จำนวนวันติดตามมาตรฐานสำหรับ Nutrition Follow-up')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

SET FOREIGN_KEY_CHECKS = 1;
