# PDH Nutrition (Pluakdaeng Hospital Clinical Nutrition Management System)

**ระบบบริหารจัดการภาวะโภชนาการผู้ป่วย โรงพยาบาลปลวกแดง**

ระบบ Web Application สำหรับนักโภชนาการ พยาบาล แพทย์ และบุคลากรทางการแพทย์ เชื่อมต่อระบบ HIS (HIMPRO) ผ่าน PDH API Gateway ปลอดภัยด้วยสถาปัตยกรรมระดับองค์กร (Web Browser ไม่ติดต่อ HIS Database โดยตรง) พร้อมระบบแจ้งเตือนและประเมินโภชนาการล่วงหน้าก่อนพบแพทย์ (Nutrition Pre-Doctor Queue), NAF Rule Engine, Diet Orders, Clinical Notes (SOAP), Executive Dashboard และ 10 รายงานครบถ้วน

---

## 🌟 ฟีเจอร์หลัก (Key Modules & Features)

1. **HIS HIMPRO Integration via PDH API Gateway**
   - ดึงข้อมูลพื้นฐานผู้ป่วย (HN, CID masked, ชื่อ-สกุล, เพศ, อายุ, สิทธิ์)
   - ดึงรายการ Visit OPD ประจำวัน, ผลตรวจ Lab (Albumin, WBC, Lymphocyte, TLC) และการวินิจฉัยโรค (ICD-10)
   - มี **HIS Driver Abstraction**: รองรับ `HIS_DRIVER=mock` (สำหรับ Dev/Offline) และ `HIS_DRIVER=himpro` (ใช้งานจริงบน Production)
   - ระบบ **Offline Cache Fallback** และ Timeout handling ป้องกันระบบค้างเมื่อ API Gateway ไม่ตอบสนอง

2. **Nutrition Pre-Doctor Queue System (คิวประเมินก่อนพบแพทย์)**
   - สแกนผู้ป่วยในกลุ่มติดตามโภชนาการ (Nutrition Registry) ที่มา Visit วันนี้โดยอัตโนมัติ
   - สร้างคิวประเมินโภชนาการพร้อมสถานะ: `WAITING_NUTRITION` ➔ `IN_ASSESSMENT` ➔ `NUTRITION_COMPLETED` (READY FOR DOCTOR)
   - ระบบ **Task Locking** ป้องกันเจ้าหน้าที่สองท่านประเมินผู้ป่วยคนเดียวกันพร้อมกัน

3. **Nutrition Alert Form (NAF) Assessment & Dynamic Scoring Engine**
   - ประเมินคะแนน NAF (Section 1-8: Weight/Height/Arm span, BMI, Albumin, TLC, Body build, Weight change, Intake, Symptoms, Disease severity)
   - คำนวณ NAF Score และแปลผลอัตโนมัติ: **NAF A** (0-5: เขียว), **NAF B** (6-10: เหลือง/ส้ม), **NAF C** (>=11: แดง)
   - หน้าจอ Sticky Live Calculation Panel คำนวณและแสดงผลคะแนนแบบ Real-time ขณะกรอกฟอร์ม
   - ระบบ **NAF Rules Versioning** (คงผลการประเมินย้อนหลังตามเวอร์ชันของกฎเดิม)

4. **Guide to Make Diet Order System**
   - คำนวณ **IBW (Ideal Body Weight)**: ชาย (ส่วนสูง - 100), หญิง (ส่วนสูง - 105)
   - คำนวณ **Energy Requirement**: 25 / 30 / 35 kcal/kg IBW/day
   - คำนวณ **Protein Requirement**: 1.0 / 1.2 / 1.3 / 1.5 g/kg IBW/day
   - จัดทำ Diet Orders (Regular, Soft, Liquid, DM, Low salt, Low fat), Oral Supplement และ Tube Feeding Formulas

5. **SOAP Clinical Note & Longitudinal History**
   - บันทึกข้อความโภชนาการทางคลินิก (Subjective, Objective auto-populated, Assessment, Plan)
   - แสดงประวัติโภชนาการย้อนหลังแบบ Timeline เปรียบเทียบน้ำหนัก, BMI, Albumin, TLC และ NAF Score

6. **Executive & Operational Dashboard**
   - แสดงการ์ดตัวเลขสรุป (Visit วันนี้, รอประเมินก่อนพบแพทย์, ประเมินแล้ว, NAF A/B/C, High Risk, Follow-up Overdue)
   - กราฟ Chart.js (Donut chart สัดส่วน NAF Grade, Bar chart จำแนกตาม Clinic)

7. **10 Comprehensive Reports & Export Engine**
   - รายงานผู้ป่วยที่ต้องได้รับการประเมินโภชนาการก่อนพบแพทย์
   - รายงานผู้ป่วยค้างประเมิน (เรียงตาม NAF C/B และระยะเวลารอคอย)
   - รายงาน Daily Assessment, NAF Classification (A/B/C), High Risk Patients (NAF C), Follow-up Overdue
   - รายงานประวัติผู้ป่วยรายบุคคล, Monthly Summary & **Pre-Doctor Compliance Rate (%)**
   - สรุปตาม Clinic และ ภาระงานนักโภชนาการ (Dietitian Workload)
   - Export รองรับ **Excel (Unicode ภาษาไทย)**, **CSV**, **PDF** และ **พิมพ์แบบฟอร์มมาตรฐาน A4 (Sarabun font)**

8. **Security, RBAC & Audit Trail**
   - ระบบสิทธิ์ 7 ระดับ: `SUPER_ADMIN`, `ADMIN`, `DIETITIAN`, `DOCTOR`, `NURSE`, `PHARMACIST`, `VIEWER`
   - ป้องกันภัยคุกคาม: Session Timeout, Password Hashing (`password_hash`), CSRF Protection, XSS Protection, Masked CID (`1-2345-XXXXX-XX-X`)
   - **Audit Log System**: บันทึกทุกกิจกรรม (Login, View Patient, Create NAF, Create Diet Order, Export/Print Report) พร้อม IP Address และ User-Agent

---

## 🛠️ Technology Stack

- **Backend**: PHP 8.2+, PDO (Prepared Statements), REST API Gateway Adapter Pattern
- **Database**: MySQL 8 (utf8mb4_unicode_ci)
- **Frontend**: HTML5, Bootstrap 5, Font Awesome, Bootstrap Icons, SweetAlert2, DataTables, Chart.js, Sarabun Font
- **Development Server**: XAMPP (Apache + MySQL)

---

## 🚀 วิธีการติดตั้งในเครื่องการพัฒนา (XAMPP Installation Guide)

### 1. ตำแหน่งโฟลเดอร์โครงการ
นำโค้ดทั้งหมดไปวางที่:
`C:\xampp\htdocs\pdhnutrition`

### 2. ตั้งค่าไฟล์ `.env`
คัดลอกไฟล์ `.env.example` เป็น `.env`:
```ini
APP_NAME="PDH Nutrition"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/pdhnutrition

DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pdhnutrition_dev
DB_USERNAME=root
DB_PASSWORD=

# HIS Connection Driver: mock หรือ himpro
HIS_DRIVER=mock
PDH_API_BASE_URL=http://192.168.111.240/pdhapi
PDH_API_KEY=pdh_secret_key_2026
PDH_API_TIMEOUT=10

TIMEZONE=Asia/Bangkok
```

### 3. สร้างฐานข้อมูลอัตโนมัติ (Automated Database Setup)
เปิด XAMPP Control Panel แล้วสั่ง Start **Apache** และ **MySQL**
จากนั้นเปิด Command Prompt / PowerShell แล้วรันคำสั่ง:
```bash
cd C:\xampp\htdocs\pdhnutrition
php database/setup_db.php
```
*ระบบจะสร้างฐานข้อมูล `pdhnutrition_dev` พร้อมตาราง 19 ตาราง และข้อมูลเริ่มต้น (Seeder Data) ให้อัตโนมัติ*

### 4. การเข้าใช้งานผ่าน Web Browser
เปิดเบราว์เซอร์แล้วเข้า URL:
`http://localhost/pdhnutrition`

**บัญชีผู้ใช้งานเริ่มต้นสำหรับทดสอบ (Test Accounts):**
- **Admin**: Username `admin` | Password `password123`
- **Dietitian**: Username `dietitian1` | Password `password123`
- **Doctor**: Username `doctor1` | Password `password123`
- **Nurse**: Username `nurse1` | Password `password123`

---

## 🧪 การทดสอบระบบอัตโนมัติ (Automated Unit Tests)

สามารถรันคำสั่งทดสอบระบบ (BMI, TLC, NAF Scoring Rules A/B/C, IBW Male/Female, Energy & Protein formulas, Task Synchronization) ผ่าน CLI:
```bash
php tests/run_tests.php
```

---

## 🏥 แผนการติดตั้งในเซิร์ฟเวอร์จริงโรงพยาบาล (Production Deployment Guide)

### ข้อมูลสภาพแวดล้อม Production
- **Production Server IP**: `192.168.111.240`
- **Production Web URL**: `http://192.168.111.240/pdhnutrition`
- **PDH API Gateway URL**: `http://192.168.111.240/pdhapi`
- **Database Name**: `pdhnutrition`

### ขั้นตอนการ Deploy ขึ้น Production:
1. นำซอร์สโค้ดไปวางที่ Directory ของ Apache บน Production Server (`/var/www/html/pdhnutrition` หรือ `C:\xampp\htdocs\pdhnutrition`)
2. แก้ไขไฟล์ `.env` บน Production Server:
   ```ini
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://192.168.111.240/pdhnutrition
   
   DB_DATABASE=pdhnutrition
   DB_USERNAME=pdh_user
   DB_PASSWORD=your_secure_password
   
   # เปิดการเชื่อมต่อ HIS API จริง
   HIS_DRIVER=himpro
   PDH_API_BASE_URL=http://192.168.111.240/pdhapi
   PDH_API_KEY=your_production_api_key
   ```
3. รันคำสั่งสร้าง Schema ฐานข้อมูล Production:
   ```bash
   php database/setup_db.php
   ```
4. เปลี่ยนรหัสผ่านผู้ดูแลระบบในตาราง `users` เป็นรหัสผ่านที่ปลอดภัย

---

## 🔒 กฎความปลอดภัยทางคลินิก (Clinical Safety Rule)

> **IMPORTANT**: ระบบนี้เป็นระบบสนับสนุนการตัดสินใจทางคลินิก (Clinical Decision Support System) ไม่ใช่ระบบตัดสินใจแทนบุคลากรทางการแพทย์ ข้อมูล Diagnosis mapping จาก HIS เสนอเป็นเพียง Suggested Condition ซึ่งนักโภชนาการ/แพทย์ต้องตรวจสอบและยืนยันก่อนบันทึกลงในระบบทุกครั้ง

---

## 📄 License & Attribution
พัฒนาสำหรับ **โรงพยาบาลปลวกแดง (Pluakdaeng Hospital)**
สงวนลิขสิทธิ์ © 2026 Pluakdaeng Hospital Clinical Nutrition Team.
