CREATE DATABASE IF NOT EXISTS warmth_grain
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE warmth_grain;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS messages, events, payments, order_items, orders,
  repairs, service_bookings, services, products, categories, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  user_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(100) NOT NULL UNIQUE,
  phone         VARCHAR(20),
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  status        ENUM('active','disabled') NOT NULL DEFAULT 'active',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE products (
  product_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name        VARCHAR(120) NOT NULL,
  description TEXT,
  price       DECIMAL(10,2) NOT NULL CHECK (price >= 0),
  stock       INT UNSIGNED NOT NULL DEFAULT 0,
  image       VARCHAR(255) DEFAULT 'placeholder.jpg',
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB;

CREATE TABLE services (
  service_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_type   ENUM('Developing','Scanning','Printing') NOT NULL,
  name           VARCHAR(100) NOT NULL,
  description    TEXT,
  price_per_roll DECIMAL(10,2) NOT NULL CHECK (price_per_roll >= 0),
  is_active      TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE service_bookings (
  booking_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED NOT NULL,
  service_id   INT UNSIGNED NOT NULL,
  film_format  ENUM('35mm','120','APS','Other') NOT NULL DEFAULT '35mm',
  roll_count   INT UNSIGNED NOT NULL CHECK (roll_count > 0),
  notes        TEXT,
  dropoff_date DATE NOT NULL,
  total_price  DECIMAL(10,2) NOT NULL CHECK (total_price >= 0),
  status       ENUM('Pending','Confirmed','Received','Processing','Completed','Cancelled')
               NOT NULL DEFAULT 'Pending',
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)    REFERENCES users(user_id),
  FOREIGN KEY (service_id) REFERENCES services(service_id)
) ENGINE=InnoDB;

CREATE TABLE repairs (
  repair_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  repair_number VARCHAR(20) NOT NULL UNIQUE,
  user_id       INT UNSIGNED NOT NULL,
  camera_brand  VARCHAR(60) NOT NULL,
  camera_model  VARCHAR(60) NOT NULL,
  issue         VARCHAR(255) NOT NULL,
  details       TEXT,
  diagnosis     TEXT NULL,
  repair_cost   DECIMAL(10,2) NULL CHECK (repair_cost >= 0),
  status        ENUM('Submitted','Received','Under Inspection','Repairing',
                     'Ready for Pickup','Completed') NOT NULL DEFAULT 'Submitted',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE orders (
  order_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id          INT UNSIGNED NOT NULL,
  total_amount     DECIMAL(10,2) NOT NULL CHECK (total_amount >= 0),
  fulfillment      ENUM('Pickup','Delivery') NOT NULL DEFAULT 'Pickup',
  contact_name     VARCHAR(100) NOT NULL,
  contact_phone    VARCHAR(20) NOT NULL,
  delivery_address VARCHAR(255) NULL,
  status           ENUM('Pending','Paid','Processing','Ready','Completed','Cancelled')
                   NOT NULL DEFAULT 'Pending',
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
  order_item_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id      INT UNSIGNED NOT NULL,
  product_id    INT UNSIGNED NOT NULL,
  quantity      INT UNSIGNED NOT NULL CHECK (quantity > 0),
  unit_price    DECIMAL(10,2) NOT NULL CHECK (unit_price >= 0),
  FOREIGN KEY (order_id)   REFERENCES orders(order_id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

CREATE TABLE payments (
  payment_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED NOT NULL,
  payment_type ENUM('booking','repair','order') NOT NULL,
  reference_id INT UNSIGNED NOT NULL,
  method       ENUM('GCash','Cash','Card') NOT NULL,
  amount       DECIMAL(10,2) NOT NULL CHECK (amount >= 0),
  status       ENUM('Pending','Paid','Failed') NOT NULL DEFAULT 'Pending',
  paid_at      DATETIME NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE events (
  event_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(150) NOT NULL,
  description TEXT,
  event_type  ENUM('Gear Swap','Trading Meetup','Other') NOT NULL DEFAULT 'Other',
  event_date  DATETIME NOT NULL,
  location    VARCHAR(150) NOT NULL,
  image       VARCHAR(255) DEFAULT 'placeholder.jpg',
  status      ENUM('Published','Cancelled') NOT NULL DEFAULT 'Published',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE messages (
  message_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(100) NOT NULL,
  subject    VARCHAR(150) NOT NULL,
  message    TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO categories (name) VALUES
  ('Cameras'), ('Film'), ('Analog Gear'), ('Accessories'), ('Merchandise');

INSERT INTO services (service_type, name, description, price_per_roll) VALUES
  ('Developing','C-41 Color Developing','Standard color negative development.',250.00),
  ('Developing','Black & White Developing','Hand-processed black and white film.',300.00),
  ('Scanning','Standard Scan','High-resolution digital scans of your negatives.',200.00),
  ('Scanning','Premium Scan','Larger, color-corrected scans for professional use.',400.00),
  ('Printing','4R Prints','Glossy or matte 4R prints per roll.',350.00),
  ('Printing','Fine Art Print','Archival paper prints, priced per roll.',800.00);

INSERT INTO products (category_id, name, description, price, stock) VALUES
  (1,'Canon AE-1 Program','Classic 35mm SLR, serviced and tested.',9500.00,3),
  (1,'Olympus Trip 35','Compact point-and-shoot, fully working.',6500.00,2),
  (2,'Kodak Gold 200 (35mm)','Warm, versatile color negative film, 36 exp.',650.00,40),
  (2,'Ilford HP5 Plus 400 (35mm)','Classic black and white film, 36 exp.',700.00,30),
  (3,'Light Meter (Vintage)','Handheld analog exposure meter.',1800.00,5),
  (4,'Leather Camera Strap','Hand-stitched leather strap.',900.00,12),
  (4,'35mm Film Canister Case','Hard case for 6 rolls.',350.00,25),
  (5,'W&G Tote Bag','Cotton tote with the W&G logo.',450.00,20);

INSERT INTO events (title, description, event_type, event_date, location) VALUES
  ('W&G Gear Swap Day','Bring your cameras and gear to swap with other film users.','Gear Swap','2026-11-15 10:00:00','Warmth&Grain Lab'),
  ('Film Trading Meetup','Trade rolls, share your recent shots, meet the community.','Trading Meetup','2026-12-06 15:00:00','Warmth&Grain Lab');