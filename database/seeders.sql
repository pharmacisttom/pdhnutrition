-- ========================================================
-- PDH Nutrition System - Database Seeders
-- Pluakdaeng Hospital Clinical Nutrition Management System
-- ========================================================

USE `pdhnutrition_dev`;

-- 1. Insert Roles
INSERT INTO `roles` (`role_code`, `role_name_th`, `description`) VALUES
('SUPER_ADMIN', 'ผู้ดูแลระบบสูงสุด', 'เข้าถึงได้ทุกส่วนของระบบ'),
('ADMIN', 'ผู้ดูแลระบบ', 'จัดการผู้ใช้งานและตั้งค่าระบบ'),
('DIETITIAN', 'นักโภชนาการ', 'ประเมิน NAF, Diet Order, Clinical Note, Registry'),
('DOCTOR', 'แพทย์', 'ดูผลการประเมิน Diet Order และอนุมัติ'),
('NURSE', 'พยาบาล', 'ดูผลการประเมิน คัดกรองเบื้องต้น'),
('PHARMACIST', 'เภสัชกร', 'ตรวจสอบโภชนบำบัดร่วมกับยา'),
('VIEWER', 'ผู้ดูข้อมูล', 'สิทธิ์อ่านอย่างเดียว')
ON DUPLICATE KEY UPDATE `role_name_th` = VALUES(`role_name_th`);

-- 2. Insert Default Users (password: "password123")
INSERT INTO `users` (`id`, `username`, `password_hash`, `fullname`, `email`, `role`, `status`) VALUES
(1, 'admin', '$2y$10$QNLsf3Dv92ZidgozkY7FIeuzEqdYKN8t1q/5H/Arv0WegqQh40mVu', 'ผู้ดูแลระบบ ปลวกแดง', 'admin@pluakdaeng.go.th', 'ADMIN', 'ACTIVE'),
(2, 'dietitian1', '$2y$10$QNLsf3Dv92ZidgozkY7FIeuzEqdYKN8t1q/5H/Arv0WegqQh40mVu', 'นักโภชนาการ สมศรี มีสุข (ภน.)', 'dietitian@pluakdaeng.go.th', 'DIETITIAN', 'ACTIVE'),
(3, 'doctor1', '$2y$10$QNLsf3Dv92ZidgozkY7FIeuzEqdYKN8t1q/5H/Arv0WegqQh40mVu', 'นพ. สมชาย ใจดี', 'doctor@pluakdaeng.go.th', 'DOCTOR', 'ACTIVE'),
(4, 'nurse1', '$2y$10$QNLsf3Dv92ZidgozkY7FIeuzEqdYKN8t1q/5H/Arv0WegqQh40mVu', 'พว. สายฝน ห่วงใย', 'nurse@pluakdaeng.go.th', 'NURSE', 'ACTIVE')
ON DUPLICATE KEY UPDATE `fullname` = VALUES(`fullname`);

-- 3. NAF Rules Engine Data (Version 1)
-- Section 2: BMI Score Rules
INSERT INTO `naf_rules` (`section`, `item_code`, `label_th`, `label_en`, `score`, `condition_type`, `min_value`, `max_value`, `operator`, `version`, `active`) VALUES
(2, 'BMI_LT_17', 'BMI น้อยกว่า 17.0 kg/m²', 'BMI < 17.0', 2, 'NUMERIC_RANGE', 0.00, 16.99, '<', 1, 1),
(2, 'BMI_17_18', 'BMI 17.0 - 18.0 kg/m²', 'BMI 17.0 - 18.0', 1, 'NUMERIC_RANGE', 17.00, 18.00, 'BETWEEN', 1, 1),
(2, 'BMI_18_29', 'BMI 18.1 - 29.9 kg/m²', 'BMI 18.1 - 29.9', 0, 'NUMERIC_RANGE', 18.10, 29.90, 'BETWEEN', 1, 1),
(2, 'BMI_GTE_30', 'BMI 30.0 kg/m² ขึ้นไป', 'BMI >= 30.0', 1, 'NUMERIC_RANGE', 30.00, 999.00, '>=', 1, 1),

-- Section 2: Albumin Rules
(2, 'ALB_LT_2_5', 'Albumin < 2.5 g/dL', 'Albumin < 2.5', 3, 'NUMERIC_RANGE', 0.00, 2.49, '<', 1, 1),
(2, 'ALB_2_6_2_9', 'Albumin 2.6 - 2.9 g/dL', 'Albumin 2.6 - 2.9', 2, 'NUMERIC_RANGE', 2.60, 2.90, 'BETWEEN', 1, 1),
(2, 'ALB_3_0_3_5', 'Albumin 3.0 - 3.5 g/dL', 'Albumin 3.0 - 3.5', 1, 'NUMERIC_RANGE', 3.00, 3.50, 'BETWEEN', 1, 1),
(2, 'ALB_GT_3_5', 'Albumin > 3.5 g/dL', 'Albumin > 3.5', 0, 'NUMERIC_RANGE', 3.51, 99.00, '>', 1, 1),

-- Section 2: TLC Rules
(2, 'TLC_LTE_1000', 'TLC <= 1000 cells/mm³', 'TLC <= 1000', 3, 'NUMERIC_RANGE', 0.00, 1000.00, '<=', 1, 1),
(2, 'TLC_1001_1200', 'TLC 1001 - 1200 cells/mm³', 'TLC 1001 - 1200', 2, 'NUMERIC_RANGE', 1001.00, 1200.00, 'BETWEEN', 1, 1),
(2, 'TLC_1201_1500', 'TLC 1201 - 1500 cells/mm³', 'TLC 1201 - 1500', 1, 'NUMERIC_RANGE', 1201.00, 1500.00, 'BETWEEN', 1, 1),
(2, 'TLC_GT_1500', 'TLC > 1500 cells/mm³', 'TLC > 1500', 0, 'NUMERIC_RANGE', 1501.00, 99999.00, '>', 1, 1),

-- Section 3: Body Build
(3, 'BUILD_VERY_THIN', 'ผอมมาก (Very thin)', 'Very thin', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(3, 'BUILD_THIN', 'ผอม (Thin)', 'Thin', 1, 'OPTION', NULL, NULL, '=', 1, 1),
(3, 'BUILD_NORMAL', 'ปกติ (Normal)', 'Normal', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(3, 'BUILD_OBESE', 'อ้วน (Obese)', 'Obese', 1, 'OPTION', NULL, NULL, '=', 1, 1),

-- Section 4: Weight Change in 4 Weeks
(4, 'WT_DECREASED', 'ลดลง / ผอมลง', 'Decreased / thinner', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(4, 'WT_INCREASED', 'เพิ่มขึ้น / อ้วนขึ้น', 'Increased / fatter', 1, 'OPTION', NULL, NULL, '=', 1, 1),
(4, 'WT_SAME', 'เท่าเดิม', 'Same', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(4, 'WT_UNKNOWN', 'ไม่ทราบ', 'Unknown', 0, 'OPTION', NULL, NULL, '=', 1, 1),

-- Section 5.1: Dietary Intake Texture (2 Weeks)
(51, 'FOOD_WATER', 'อาหารน้ำ', 'Water / liquid diet', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(51, 'FOOD_LIQUID', 'อาหารเหลว', 'Full liquid diet', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(51, 'FOOD_SOFT', 'อาหารนุ่มกว่าปกติ', 'Soft diet', 1, 'OPTION', NULL, NULL, '=', 1, 1),
(51, 'FOOD_NORMAL', 'อาหารเหมือนปกติ', 'Normal diet', 0, 'OPTION', NULL, NULL, '=', 1, 1),

-- Section 5.2: Dietary Intake Amount (2 Weeks)
(52, 'AMOUNT_VERY_LITTLE', 'กินน้อยมาก ( < 25% )', 'Eats very little', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(52, 'AMOUNT_LESS', 'กินน้อยลง ( 25-50% )', 'Eats less', 1, 'OPTION', NULL, NULL, '=', 1, 1),
(52, 'AMOUNT_NORMAL', 'กินเท่าปกติ', 'Eats normal', 0, 'OPTION', NULL, NULL, '=', 1, 1),
(52, 'AMOUNT_MORE', 'กินมากขึ้น', 'Eats more', 0, 'OPTION', NULL, NULL, '=', 1, 1),

-- Section 6.1: Chewing / Swallowing Symptoms (> 2 Weeks)
(61, 'SYM_CHOKING', 'สำลักอาหาร/น้ำ', 'Choking', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(61, 'SYM_SWALLOW_DIFFICULT', 'เคี้ยว/กลืนลำบาก/ได้อาหารทางสายยาง', 'Swallowing difficulty / Tube feeding', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(61, 'SYM_SWALLOW_NORMAL', 'กลืนได้ปกติ', 'Normal swallowing', 0, 'OPTION', NULL, NULL, '=', 1, 1),

-- Section 6.2: Gastrointestinal Symptoms (> 2 Weeks)
(62, 'SYM_DIARRHEA', 'ท้องเสียเรื้อรัง', 'Chronic diarrhea', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(62, 'SYM_ABD_PAIN', 'ปวดท้องเรื้อรัง', 'Abdominal pain', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(62, 'SYM_GI_NORMAL', 'ระบบทางเดินอาหารปกติ', 'Normal GI', 0, 'OPTION', NULL, NULL, '=', 1, 1),

-- Section 6.3: Symptoms During Meal (> 2 Weeks)
(63, 'SYM_VOMITING', 'อาเจียนหลังอาหาร', 'Vomiting', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(63, 'SYM_NAUSEA', 'คลื่นไส้เป็นประจำ', 'Nausea', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(63, 'SYM_MEAL_NORMAL', 'ไม่มีอาการระหว่างกินอาหาร', 'Normal meal', 0, 'OPTION', NULL, NULL, '=', 1, 1),

-- Section 7: Food Accessibility
(7, 'ACCESS_LIMIT', 'มีข้อจำกัดในการเข้าถึงอาหาร/ซื้ออาหารเองไม่ได้', 'Limited access to food', 2, 'OPTION', NULL, NULL, '=', 1, 1),
(7, 'ACCESS_ASSIST', 'ต้องมีผู้ช่วยเตรียมอาหาร', 'Needs assistance', 1, 'OPTION', NULL, NULL, '=', 1, 1),
(7, 'ACCESS_NORMAL', 'สามารถเข้าถึงและรับประทานอาหารได้ปกติ', 'Independent access', 0, 'OPTION', NULL, NULL, '=', 1, 1),

-- Section 8: Disease Severity 3 Points
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

-- Section 8: Disease Severity 6 Points
(82, 'DIS_SEVERE_PNEUMONIA', 'ปอดบวมรุนแรง (Severe Pneumonia)', 'Severe Pneumonia', 6, 'DISEASE', NULL, NULL, '=', 1, 1),
(82, 'DIS_CRITICALLY_ILL', 'ผู้ป่วยภาวะวิกฤต (Critically Ill)', 'Critically Ill', 6, 'DISEASE', NULL, NULL, '=', 1, 1),
(82, 'DIS_MULTI_FX', 'กระดูกหักหลายตำแหน่ง (Multiple Fracture)', 'Multiple FX', 6, 'DISEASE', NULL, NULL, '=', 1, 1),
(82, 'DIS_STROKE_CVA', 'โรคหลอดเลือดสมอง (Stroke / CVA)', 'Stroke / CVA', 6, 'DISEASE', NULL, NULL, '=', 1, 1),
(82, 'DIS_MALIG_HEM', 'มะเร็งระบบเลือด (Malignant Hematologic Disease)', 'Malignant Hem', 6, 'DISEASE', NULL, NULL, '=', 1, 1),
(82, 'DIS_BMT', 'ปลูกถ่ายไขกระดูก (Bone Marrow Transplant)', 'BMT', 6, 'DISEASE', NULL, NULL, '=', 1, 1);

-- 4. System Settings Initial Values
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('hospital_name_th', 'โรงพยาบาลปลวกแดง', 'ชื่อโรงพยาบาลภาษาไทย'),
('hospital_name_en', 'Pluakdaeng Hospital', 'ชื่อโรงพยาบาลภาษาอังกฤษ'),
('auto_create_queue_task', '1', 'สร้าง Nutrition Queue Task อัตโนมัติเมื่อผู้ป่วยใน Registry มา Visit'),
('naf_active_version', '1', 'เวอร์ชัน NAF Rule Engine ที่ใช้งานปัจจุบัน'),
('followup_default_interval_days', '14', 'จำนวนวันติดตามมาตรฐานสำหรับ Nutrition Follow-up')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- 5. Demo Patients Cache (Pluakdaeng Hospital Mock Patients)
INSERT INTO `patients_cache` (`hn`, `cid`, `prefix`, `first_name`, `last_name`, `fullname`, `gender`, `birthdate`, `age`, `phone`, `weight`, `height`, `bmi`) VALUES
('66000101', '1-2101-00123-45-1', 'นาย', 'สมศักดิ์', 'พูนสวัสดิ์', 'นาย สมศักดิ์ พูนสวัสดิ์', 'MALE', '1965-04-12', 61, '081-234-5678', 48.50, 168.00, 17.18),
('66000102', '3-2102-00567-89-2', 'นาง', 'ปราณี', 'มั่นคง', 'นาง ปราณี มั่นคง', 'FEMALE', '1958-11-25', 67, '089-876-5432', 42.00, 152.00, 18.18),
('66000103', '1-2103-00999-11-3', 'นาย', 'วิชัย', 'เจริญสุข', 'นาย วิชัย เจริญสุข', 'MALE', '1950-08-05', 76, '086-555-1234', 45.00, 165.00, 16.53),
('66000104', '2-2104-00444-22-4', 'นางสาว', 'จินตนา', 'รุ่งเรือง', 'นางสาว จินตนา รุ่งเรือง', 'FEMALE', '1975-02-14', 51, '084-111-2233', 68.00, 158.00, 27.24),
('66000105', '1-2105-00777-33-5', 'นาย', 'บุญมี', 'แก้วสว่าง', 'นาย บุญมี แก้วสว่าง', 'MALE', '1948-09-30', 77, '082-999-8877', 41.50, 162.00, 15.81)
ON DUPLICATE KEY UPDATE `fullname` = VALUES(`fullname`);

-- 6. Demo Visits (Today's visits at Pluakdaeng Hospital OPD)
INSERT INTO `visits_cache` (`vn`, `hn`, `visit_date`, `visit_time`, `clinic`, `department`, `doctor`, `queue_number`) VALUES
('VN690911001', '66000101', CURRENT_DATE(), '08:30:00', 'คลินิกโรคเรื้อรัง (NCD)', 'OPD', 'นพ. สมชาย ใจดี', 'A001'),
('VN690911002', '66000102', CURRENT_DATE(), '08:45:00', 'คลินิกผู้สูงอายุ', 'OPD', 'พญ. สุดา สุขสันต์', 'A002'),
('VN690911003', '66000103', CURRENT_DATE(), '09:10:00', 'คลินิกโรคไต (CKD)', 'OPD', 'นพ. สมชาย ใจดี', 'B005'),
('VN690911004', '66000104', CURRENT_DATE(), '09:30:00', 'คลินิกเบาหวาน', 'OPD', 'พญ. สุดา สุขสันต์', 'B008'),
('VN690911005', '66000105', CURRENT_DATE(), '10:00:00', 'คลินิกอายุรกรรม', 'OPD', 'นพ. สมชาย ใจดี', 'C003')
ON DUPLICATE KEY UPDATE `doctor` = VALUES(`doctor`);

-- 7. Demo Diagnoses
INSERT INTO `diagnosis_cache` (`vn`, `hn`, `icd10`, `diagnosis_name`, `category`) VALUES
('VN690911001', '66000101', 'E11.9', 'Non-insulin-dependent diabetes mellitus without complications', 'PRIMARY'),
('VN690911001', '66000101', 'N18.3', 'Chronic kidney disease, stage 3', 'SECONDARY'),
('VN690911002', '66000102', 'E44.0', 'Moderate protein-calorie malnutrition', 'PRIMARY'),
('VN690911003', '66000103', 'N18.5', 'End stage renal disease', 'PRIMARY'),
('VN690911005', '66000105', 'E43', 'Unspecified severe protein-calorie malnutrition', 'PRIMARY');

-- 8. Demo Lab Cache
INSERT INTO `lab_cache` (`hn`, `vn`, `test_name`, `result_value`, `result_unit`, `result_date`, `result_time`) VALUES
('66000101', 'VN690911001', 'Albumin', 3.20, 'g/dL', CURRENT_DATE(), '07:30:00'),
('66000101', 'VN690911001', 'WBC', 6500.00, 'cells/mm³', CURRENT_DATE(), '07:30:00'),
('66000101', 'VN690911001', 'Lymphocyte', 22.00, '%', CURRENT_DATE(), '07:30:00'),
('66000102', 'VN690911002', 'Albumin', 2.80, 'g/dL', CURRENT_DATE(), '07:45:00'),
('66000103', 'VN690911003', 'Albumin', 2.40, 'g/dL', CURRENT_DATE(), '08:00:00'),
('66000103', 'VN690911003', 'WBC', 4800.00, 'cells/mm³', CURRENT_DATE(), '08:00:00'),
('66000103', 'VN690911003', 'Lymphocyte', 18.00, '%', CURRENT_DATE(), '08:00:00'),
('66000105', 'VN690911005', 'Albumin', 2.10, 'g/dL', CURRENT_DATE(), '08:15:00');

-- 9. Demo Nutrition Registry
INSERT INTO `nutrition_registry` (`hn`, `active`, `reason`, `risk_level`, `start_date`, `follow_up_interval_days`, `must_review_every_visit`, `note`) VALUES
('66000101', 1, 'ผู้ป่วย CKD Stage 3 ร่วมกับเสี่ยง Malnutrition', 'MEDIUM', CURRENT_DATE(), 14, 1, 'ติดตาม intake และผล Albumin ทุกครั้งที่มา visit'),
('66000103', 1, 'ผู้ป่วย ESRD โภชนาการต่ำรุนแรง NAF C', 'HIGH', CURRENT_DATE(), 7, 1, 'ต้องประเมินโภชนาการก่อนพบแพทย์ทุกครั้ง'),
('66000105', 1, 'Severe Malnutrition NAF C', 'SEVERE', CURRENT_DATE(), 7, 1, 'ผู้ป่วยสูงอายุ ผอมมาก กินอาหารได้น้อยมาก')
ON DUPLICATE KEY UPDATE `reason` = VALUES(`reason`);

-- 10. Demo Nutrition Tasks (Pre-Doctor Queue Tasks for Today)
INSERT INTO `nutrition_tasks` (`hn`, `vn`, `visit_date`, `clinic`, `doctor`, `task_type`, `risk_level`, `status`, `priority`) VALUES
('66000103', 'VN690911003', CURRENT_DATE(), 'คลินิกโรคไต (CKD)', 'นพ. สมชาย ใจดี', 'PRE_DOCTOR_ASSESSMENT', 'HIGH', 'WAITING_NUTRITION', 1),
('66000105', 'VN690911005', CURRENT_DATE(), 'คลินิกอายุรกรรม', 'นพ. สมชาย ใจดี', 'PRE_DOCTOR_ASSESSMENT', 'SEVERE', 'WAITING_NUTRITION', 1),
('66000101', 'VN690911001', CURRENT_DATE(), 'คลินิกโรคเรื้อรัง (NCD)', 'นพ. สมชาย ใจดี', 'PRE_DOCTOR_ASSESSMENT', 'MEDIUM', 'WAITING_NUTRITION', 2)
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);
