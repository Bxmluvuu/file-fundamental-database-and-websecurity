# Lab 09 — แบบฝึกเพิ่มหนังสือ

ไฟล์พื้นฐานสำหรับฝึก **Input Validation** และ **Secure Error Handling** อิงเงื่อนไขจาก Lab 09 ของวิชา Database and Web Security เขียนชุดเริ่มต้นใหม่ให้เปิดจาก Docker ได้เลย

มีฟอร์ม, ตารางหนังสือ, การเชื่อม DB และ Prepared Statement ให้แล้ว เหลือแก้ **TODO 1 กับ TODO 2** ใน `www/Lab09_add_book.php` ยังไม่มีเฉลยสองส่วนนี้

## เริ่มทำ

ต้องมี Docker Desktop ที่เปิดอยู่ และ Git ถ้าจะ clone

```sh
git clone https://github.com/Bxmluvuu/file-fundamental-database-and-websecurity.git
cd file-fundamental-database-and-websecurity
docker compose up -d --build
docker compose ps
```

รอบแรกต้องรอโหลด image และ build PHP; DB จะสร้างตารางและข้อมูลตัวอย่างให้อัตโนมัติ

- เว็บ: http://localhost:8080
- phpMyAdmin: http://localhost:8081 — เข้าด้วย `webapp_user` / `lab09_demo_password`
- ใน phpMyAdmin เลือกฐาน `db_lab08` → ตาราง `books`
- Repo นี้เป็น private: ต้องได้รับสิทธิ์และล็อกอิน GitHub ก่อน clone หรือ Download ZIP

รหัสข้างบนเป็นรหัสตัวอย่างของชุดฝึกในเครื่อง ถ้าเปลี่ยนให้คัดลอก `.env.example` เป็น `.env` แล้วแก้ค่าในไฟล์นั้น

## ข้อ 1 — ตรวจข้อมูลก่อน INSERT

แก้ **TODO 1** ใน `www/Lab09_add_book.php` ให้ตรวจใน PHP แล้วเพิ่มข้อความลง `$errors[]` เมื่อข้อมูลผิด เมื่อมี Error ต้องไม่ INSERT และต้องแสดงข้อความให้ผู้ใช้แก้ข้อมูลได้

| ช่อง | เงื่อนไข |
|---|---|
| title | ไม่ว่างหลัง trim, ไม่เกิน 150 ตัวอักษร |
| author | ไม่ว่างหลัง trim, ไม่เกิน 100 ตัวอักษร |
| category | เฉพาะ Database / Programming / Security |
| price | ต้องเป็นตัวเลข, อยู่ในช่วง 0.01–999999.99 |
| isbn | ต้องกรอก; รูปแบบ ISBN-10 คือ 9 หลักตามด้วยตัวเลขหรือ X, หรือ ISBN-13 คือเลข 13 หลัก; ชุดนี้ใช้แบบไม่ใส่ขีดและไม่ตรวจ checksum |
| publish_year | จำนวนเต็ม 1900 ถึงปี ค.ศ. ปัจจุบัน |
| stock | จำนวนเต็ม 0–100000; ค่า 0 ต้องผ่าน |

ตรวจจากข้อความต้นฉบับ **ก่อน** cast เป็น int/float เพราะตัวอักษรที่ cast เป็น 0 แล้วจะตรวจย้อนหลังไม่ได้ ใช้ความยาวแบบรองรับภาษาไทย

ลองทดสอบให้ครบ:

1. ข้อมูลถูกต้อง → บันทึกได้และปรากฏในตาราง
2. ชื่อเป็นช่องว่าง, stock ติดลบ, ปี 3000, ISBN ผิดรูปแบบ → บันทึกไม่ได้
3. เปิด DevTools → Elements ลบ `required`, `min`, `max` และเพิ่ม `<option value="Hardware">Hardware</option>` → ส่งค่าผิดแล้ว PHP ก็ต้องปฏิเสธ
4. ลอง stock เป็น 0 → บันทึกได้; ลอง 1.5 หรือ abc ด้วยการแก้ HTML/ส่ง POST ตรง → ต้องปฏิเสธ

ใช้ข้อมูลถูกต้องชุดนี้ทดสอบได้:

```text
title: SQL Fundamentals
author: John Smith
category: Database
price: 100.00
isbn: 9780131873254
publish_year: 2022
stock: 0
```

## ข้อ 2 — จัดการ DB Error

แก้ **TODO 2** ใน `catch` ของไฟล์เดียวกัน:

1. หน้าเว็บแสดงข้อความทั่วไป เช่น “บันทึกไม่สำเร็จ กรุณาลองใหม่” โดยไม่เผย SQL, ชื่อตาราง/คอลัมน์ หรือ path ภายใน
2. รายละเอียดข้อผิดพลาดให้เก็บด้วย `error_log()` เพื่อดูฝั่ง Server ห้ามเก็บรหัสผ่านหรือข้อมูลลับ

เปิด http://localhost:8080/?database_error_test=1 กรอก **ข้อมูลถูกต้องทุกช่อง** แล้วกดบันทึก ระบบจะลองใช้ชื่อคอลัมน์ที่ไม่มีจริงเพื่อเข้า `catch` โดยไม่ลบหรือแก้โครงสร้างตาราง

จากนั้นดู Log:

```sh
docker compose logs --tail=50 web
```

เกณฑ์ผ่าน: หน้าเว็บไม่เผย `missing_title_column` แต่ Server Log มีสาเหตุ และไม่มีหนังสือรายการนั้นถูกเพิ่มเข้า DB

## ไฟล์ที่ใช้

```text
compose.yaml                เปิด PHP, MariaDB และ phpMyAdmin
docker/php/Dockerfile       ติดตั้ง mysqli และ mbstring
docker/db/01-init.sql        สร้าง books, ข้อมูลตัวอย่าง และสิทธิ์ DB
www/Lab09_config.php         เชื่อม DB และ h() สำหรับแสดง HTML
www/Lab09_add_book.php       ฟอร์มเพิ่มหนังสือ + TODO สองข้อ
www/index.php               เปิดหน้าเพิ่มหนังสือจาก URL หลัก
```

DB account ของเว็บมีเฉพาะ SELECT/INSERT ใน `books` ส่วน HTML output ใช้ `h()` ให้แล้ว สองอย่างนี้ให้คงไว้ขณะเพิ่ม validation

## คำสั่งที่ใช้บ่อย

```sh
docker compose down                           # ปิดระบบ เก็บข้อมูล DB ไว้
docker compose up -d                           # เปิดใหม่
docker compose logs --tail=50 web              # ดู PHP/Apache Log
docker compose exec web php -l Lab09_add_book.php  # เช็ก syntax
```

แก้ PHP แล้ว refresh เว็บได้เลย ถ้าแก้ Dockerfile ให้สั่ง `docker compose up -d --build`

ถ้าพอร์ต 8080/8081 ชนกับแลปเดิม ให้คัดลอก `.env.example` เป็น `.env` แล้วเปลี่ยน `WEB_PORT`/`PMA_PORT` เช่น 8090/8091 ก่อนเปิดใหม่ URL จะเปลี่ยนตามพอร์ตที่ตั้ง

ถ้าต้องการเริ่ม DB ใหม่ทั้งหมด: `docker compose down -v` แล้ว `docker compose up -d --build` **คำสั่งนี้ลบหนังสือทั้งหมดในชุดฝึกนี้** ใช้เมื่ออยากรีเซ็ตเท่านั้น SQL เริ่มต้นจะรันเฉพาะตอน volume DB ยังว่าง เปลี่ยนรหัสใน `.env` อย่างเดียวจะไม่เปลี่ยนรหัสของ DB เดิม

## ตรวจว่าชุดเริ่มต้นรันได้

GitHub Actions จะตรวจ syntax, build Docker และลองเพิ่มหนังสือ/ทดสอบเส้นทาง DB Error สามารถเรียกเองได้หลังเปิด Docker โดยมี Python 3 ในเครื่อง:

```sh
python3 scripts/smoke-test.py
```

ถ้าเปลี่ยนพอร์ต เช่น 8090 ให้ใช้ `python3 scripts/smoke-test.py http://localhost:8090`

การตรวจนี้เช็กว่าชุดเริ่มต้นทำงานได้ ส่วนเกณฑ์สองข้อให้ทดสอบตามรายการด้านบน

เอกสารประกอบการตั้ง Docker: [PHP Official Image](https://github.com/docker-library/docs/blob/master/php/README.md), [MariaDB Healthcheck](https://mariadb.com/docs/server/server-management/automated-mariadb-deployment-and-administration/docker-and-mariadb/using-healthcheck-sh)
