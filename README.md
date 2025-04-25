# Tiny Second-hand Shopping Platform (TSSP)
WHS3_Secure_Coding
중고거래 플랫폼 프로젝트  

**상품 등록, 검색, 메시지, 포인트 거래, 관리자 기능, 차단, 알림 등**  
현대적 UI/UX와 실전 보안이 적용된 웹 서비스

---

## 프로젝트 폴더 구조
/
├── css/
│ └── style.css
├── php/
│ ├── add_product.php
│ ├── admin.php
│ ├── block.php
│ ├── buy.php
│ ├── changePW.php
│ ├── db.php
│ ├── login.php
│ ├── logout.php
│ ├── message.php
│ ├── mypage.php
│ ├── points.php
│ ├── product_detail.php
│ ├── products.php
│ ├── register.php
│ ├── sanction.php
│ ├── user_delete.php
├── uploads/ 
├── index.php
├── TSSPlogo.png
├── TSSPlogofull.png
├── new.png
└── README.md

---

## 환경 설정

### 1. **필수 소프트웨어**
- PHP 8.x 이상
- MySQL 5.7 이상 (InnoDB)
- Apache/Nginx (XAMPP, MAMP, Laragon 등 웹서버 패키지 권장)
- GD 라이브러리 (이미지 리사이즈용, PHP 확장)

### 2. **DB 생성 및 테이블 구조**
CREATE DATABASE tssp DEFAULT CHARACTER SET utf8mb4;
USE tssp;

-- 주요 테이블 예시
CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(32) UNIQUE,
email VARCHAR(100) UNIQUE,
password_hash VARCHAR(255),
role ENUM('user','admin') DEFAULT 'user',
last_message_check DATETIME DEFAULT NULL,

-- 기타 필요 컬럼
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
id INT AUTO_INCREMENT PRIMARY KEY,
seller_id INT,
title VARCHAR(100),
description TEXT,
price INT,
image VARCHAR(255),
is_sold TINYINT(1) DEFAULT 0,
is_blocked TINYINT(1) DEFAULT 0,
FOREIGN KEY (seller_id) REFERENCES users(id)
);

CREATE TABLE messages (
id INT AUTO_INCREMENT PRIMARY KEY,
sender_id INT,
receiver_id INT,
content TEXT,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (sender_id) REFERENCES users(id),
FOREIGN KEY (receiver_id) REFERENCES users(id)
);

CREATE TABLE points (
user_id INT PRIMARY KEY,
balance INT DEFAULT 0,
FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE transactions (
id INT AUTO_INCREMENT PRIMARY KEY,
from_user INT,
to_user INT,
amount INT,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (from_user) REFERENCES users(id),
FOREIGN KEY (to_user) REFERENCES users(id)
);

CREATE TABLE blocks (
id INT AUTO_INCREMENT PRIMARY KEY,
blocker_id INT,
blocked_user_id INT,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (blocker_id) REFERENCES users(id),
FOREIGN KEY (blocked_user_id) REFERENCES users(id)
);

CREATE TABLE points_zero_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    zeroed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE sanction (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sanction_type ENUM('block', 'point_zero', 'other') NOT NULL,
    reason VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

---

### 3. **DB 접속 정보 설정**
$mysqli = new mysqli('localhost', 'root', '', 'tiny_secondhand');

---

## 실행 방법

1. **프로젝트 전체를 웹서버 루트(htdocs/www 등)에 복사**
2. `php/db.php`에서 DB 접속 정보 수정
3. MySQL에서 위의 테이블 생성 쿼리 실행
4. 웹브라우저에서 `http://localhost/프로젝트폴더/index.php` 접속
5. 회원가입 → 로그인 → 상품 등록/구매/메시지 등 기능 사용

---

## 관리자 계정 생성

- 회원가입 후, DB에서 해당 사용자의 `role`을 `admin`으로 직접 변경
UPDATE users SET role='admin' WHERE username='관리자아이디';

- 관리자 계정은 `admin.php`에서 모든 요소를 관리할 수 있습니다.

---

## 기타

- `images/` 폴더에 로고(`TSSPlogo.png`, `TSSPlogofull.png`)와 메시지 알림(`new.png`) 이미지를 넣으세요.
- GD 라이브러리가 없으면 이미지 리사이즈 기능이 제한될 수 있습니다.
- index.php와 같은 경로에 uploads/ 폴더를 생성하세요.(상품 등록 사진 올라감)
---



