CREATE DATABASE IF NOT EXISTS warwa_school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE warwa_school;
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(20) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_role (role), INDEX idx_users_created (created_at)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS courses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL UNIQUE,
  title VARCHAR(160) NOT NULL,
  description TEXT NOT NULL,
  duration VARCHAR(80) NOT NULL DEFAULT 'Self-paced',
  status ENUM('active','archived') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  provider ENUM('paystack','mpesa') NOT NULL,
  reference VARCHAR(100) NOT NULL UNIQUE,
  provider_reference VARCHAR(120) NULL,
  provider_transaction VARCHAR(120) NULL,
  amount_kes DECIMAL(10,2) NOT NULL DEFAULT 5.00,
  phone VARCHAR(20) NULL,
  status ENUM('pending','success','failed') NOT NULL DEFAULT 'pending',
  failure_reason VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  paid_at TIMESTAMP NULL,
  UNIQUE KEY uq_provider_reference (provider, provider_reference),
  INDEX idx_payments_status (status), INDEX idx_payments_user (user_id), INDEX idx_payments_created (created_at),
  CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_payments_course FOREIGN KEY (course_id) REFERENCES courses(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS enrollments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  payment_id BIGINT UNSIGNED NOT NULL,
  enrolled_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_course (user_id, course_id),
  CONSTRAINT fk_enroll_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_enroll_course FOREIGN KEY (course_id) REFERENCES courses(id),
  CONSTRAINT fk_enroll_payment FOREIGN KEY (payment_id) REFERENCES payments(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  otp_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  consumed_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_reset_user (user_id, expires_at),
  CONSTRAINT fk_reset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
ALTER TABLE payments ALTER COLUMN amount_kes SET DEFAULT 5.00;
INSERT IGNORE INTO courses (code, title, description, duration) VALUES
('WIS-101','Digital Literacy','Build practical confidence with computers, the internet and everyday productivity tools.','4 weeks'),
('WIS-201','Entrepreneurship Essentials','Shape a business idea, understand customers and learn the basics of sustainable growth.','6 weeks'),
('WIS-110','Introduction to Data Skills','Work with spreadsheets, understand data quality and communicate useful insights.','5 weeks');
