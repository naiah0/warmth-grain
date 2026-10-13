USE warmth_grain;

ALTER TABLE messages
  ADD COLUMN user_id INT UNSIGNED NULL AFTER message_id,
  ADD COLUMN admin_reply TEXT NULL AFTER message,
  ADD COLUMN status ENUM('New','Replied') NOT NULL DEFAULT 'New' AFTER admin_reply,
  ADD COLUMN replied_at DATETIME NULL AFTER status,
  ADD COLUMN customer_ack TINYINT(1) NOT NULL DEFAULT 0 AFTER replied_at,
  ADD COLUMN acked_at DATETIME NULL AFTER customer_ack,
  ADD CONSTRAINT fk_messages_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS event_photos (
  photo_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id INT UNSIGNED NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_event_photos_event FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
) ENGINE=InnoDB;
