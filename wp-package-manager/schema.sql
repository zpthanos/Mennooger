-- Packages (built-in WooCommerce-style)
CREATE TABLE wp_pm_packages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(191),
  description TEXT,
  price DECIMAL(10,2),
  status ENUM('published','draft'),
  created_at DATETIME, updated_at DATETIME
);

-- Submissions (generic form data storage)
CREATE TABLE wp_pm_submissions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('partner','interest','payment','subscription'),
  user_id BIGINT NULL,
  data JSON,
  status ENUM('pending','completed','cancelled'),
  created_at DATETIME
);

-- Payments
CREATE TABLE wp_pm_payments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  submission_id BIGINT,
  gateway VARCHAR(32),
  amount DECIMAL(10,2),
  recurring_period ENUM('μηνιαία','ετήσια') NULL,
  status ENUM('pending','success','failed','refunded'),
  txn_id VARCHAR(128),
  created_at DATETIME
);

-- User Subscriptions
CREATE TABLE wp_pm_user_subscriptions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  subscription_type ENUM('μηνιαία','ετήσια'),
  start_date DATE, end_date DATE,
  status ENUM('active','expired','pending'),
  label VARCHAR(50),
  afm VARCHAR(20),
  last_payment_id BIGINT,
  meta_data JSON,
  created_at DATETIME, updated_at DATETIME,
  INDEX(user_id), INDEX(status), INDEX(end_date)
);

-- Action Logs
CREATE TABLE wp_pm_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NULL,
  action VARCHAR(64),
  object_type VARCHAR(32),
  object_id BIGINT,
  ip VARCHAR(45),
  timestamp DATETIME
);

-- Email Logs
CREATE TABLE wp_pm_email_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  template_key VARCHAR(64),
  recipient TEXT,
  subject TEXT,
  status ENUM('queued','sent','failed'),
  error_msg TEXT NULL,
  timestamp DATETIME
);
