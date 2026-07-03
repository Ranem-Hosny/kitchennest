-- ============================================================
--  KitchenNest Database Schema
--  Run this in your MySQL/phpMyAdmin to create the tables
-- ============================================================

CREATE DATABASE IF NOT EXISTS `kitchennest_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `kitchennest_db`;

-- ── Orders ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orders` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `order_num`       VARCHAR(20)     NOT NULL UNIQUE,
  `customer_name`   VARCHAR(150)    NOT NULL,
  `phone`           VARCHAR(30)     NOT NULL,
  `whatsapp`        VARCHAR(30)     NOT NULL,
  `city`            VARCHAR(100)    NOT NULL,
  `area`            VARCHAR(150)    NOT NULL,
  `address`         TEXT            NOT NULL,
  `notes`           TEXT                     DEFAULT NULL,
  `delivery_method` ENUM('delivery','pickup') NOT NULL DEFAULT 'delivery',
  `shipping_fee`    DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  `subtotal`        DECIMAL(10,2)   NOT NULL,
  `total`           DECIMAL(10,2)   NOT NULL,
  `trans_ref`       VARCHAR(100)             DEFAULT NULL COMMENT 'InstaPay transaction reference',
  `status`          ENUM('pending','confirmed','shipped','delivered','cancelled')
                                    NOT NULL DEFAULT 'pending',
  `created_at`      DATETIME        NOT NULL,
  `updated_at`      DATETIME                 DEFAULT NULL ON UPDATE NOW(),
  PRIMARY KEY (`id`),
  KEY `idx_order_num` (`order_num`),
  KEY `idx_status`    (`status`),
  KEY `idx_created`   (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Order Items ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `order_items` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `order_id`     INT UNSIGNED    NOT NULL,
  `product_id`   INT UNSIGNED    NOT NULL,
  `product_name` VARCHAR(255)    NOT NULL,
  `qty`          SMALLINT        NOT NULL DEFAULT 1,
  `unit_price`   DECIMAL(10,2)   NOT NULL,
  `total_price`  DECIMAL(10,2)   NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  CONSTRAINT `fk_items_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Contact Messages ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `contacts` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150)    NOT NULL,
  `phone`      VARCHAR(30)              DEFAULT NULL,
  `email`      VARCHAR(200)             DEFAULT NULL,
  `subject`    VARCHAR(200)    NOT NULL,
  `message`    TEXT            NOT NULL,
  `is_read`    TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at` DATETIME        NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_is_read`  (`is_read`),
  KEY `idx_created`  (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Optional: Products snapshot (for order reference) ───────
-- Uncomment if you want to store product catalogue in DB too
-- CREATE TABLE IF NOT EXISTS `products` (
--   `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
--   `name`        VARCHAR(255)    NOT NULL,
--   `category`    VARCHAR(50)     NOT NULL,
--   `price`       DECIMAL(10,2)   NOT NULL,
--   `old_price`   DECIMAL(10,2)            DEFAULT NULL,
--   `discount`    TINYINT         NOT NULL DEFAULT 0,
--   `in_stock`    TINYINT(1)      NOT NULL DEFAULT 1,
--   `image_url`   VARCHAR(500)             DEFAULT NULL,
--   `created_at`  DATETIME        NOT NULL DEFAULT NOW(),
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
