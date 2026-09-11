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
