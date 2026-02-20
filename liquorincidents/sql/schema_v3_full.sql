USE bractools;

CREATE TABLE IF NOT EXISTS register_sequences (
  yyyymm CHAR(6) PRIMARY KEY,
  last_num INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS record_locks (
  entity_type VARCHAR(32) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  locked_by_staff_id INT(10) UNSIGNED NOT NULL,
  locked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at TIMESTAMP NULL,
  PRIMARY KEY (entity_type, entity_id),
  INDEX idx_lock_expires (expires_at),
  CONSTRAINT fk_lock_staff FOREIGN KEY (locked_by_staff_id) REFERENCES staff_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS incidents (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  incident_no VARCHAR(32) NOT NULL,
  incident_date DATE NOT NULL,
  incident_time TIME NOT NULL,
  location VARCHAR(255) NULL,
  cctv_available TINYINT(1) NULL,
  approved_manager_name VARCHAR(255) NULL,
  reporting_person_name VARCHAR(255) NULL,
  incident_type VARCHAR(128) NOT NULL,
  authorities_notified_json JSON NULL,
  police_notified TINYINT(1) NOT NULL DEFAULT 0,
  physical_force TINYINT(1) NOT NULL DEFAULT 0,
  incident_details TEXT NULL,
  actions_taken TEXT NULL,
  soft_locked TINYINT(1) NOT NULL DEFAULT 1,
  created_by_staff_id INT(10) UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_incident_date (incident_date),
  UNIQUE KEY uq_incident_no (incident_no),
  CONSTRAINT fk_incidents_created_by_staff FOREIGN KEY (created_by_staff_id) REFERENCES staff_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS incident_revisions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  incident_id BIGINT UNSIGNED NOT NULL,
  edited_by_staff_id INT(10) UNSIGNED NOT NULL,
  edit_reason VARCHAR(255) NOT NULL,
  before_json JSON NOT NULL,
  after_json JSON NOT NULL,
  diff_json JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_inc_rev (incident_id, created_at),
  CONSTRAINT fk_inc_rev_incident FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
  CONSTRAINT fk_inc_rev_editor FOREIGN KEY (edited_by_staff_id) REFERENCES staff_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS incident_staff_links (
  incident_id BIGINT UNSIGNED NOT NULL,
  staff_id INT(10) UNSIGNED NOT NULL,
  role_in_incident VARCHAR(64) NULL,
  PRIMARY KEY (incident_id, staff_id),
  CONSTRAINT fk_inc_staff_incident FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
  CONSTRAINT fk_inc_staff_staff FOREIGN KEY (staff_id) REFERENCES staff_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS incident_files (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  incident_id BIGINT UNSIGNED NOT NULL,
  uploaded_by_staff_id INT(10) UNSIGNED NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(128) NOT NULL,
  size_bytes BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_inc_files (incident_id, created_at),
  CONSTRAINT fk_inc_files_incident FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
  CONSTRAINT fk_inc_files_uploader FOREIGN KEY (uploaded_by_staff_id) REFERENCES staff_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS refusals (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  refusal_no VARCHAR(32) NOT NULL,
  refusal_date DATE NOT NULL,
  refusal_time TIME NOT NULL,
  location VARCHAR(255) NULL,
  cctv_available TINYINT(1) NULL,
  reason VARCHAR(128) NOT NULL,
  comments TEXT NULL,
  approved_manager_name VARCHAR(255) NULL,
  police_notified TINYINT(1) NOT NULL DEFAULT 0,
  soft_locked TINYINT(1) NOT NULL DEFAULT 1,
  created_by_staff_id INT(10) UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_refusal_date (refusal_date),
  UNIQUE KEY uq_refusal_no (refusal_no),
  CONSTRAINT fk_refusals_created_by_staff FOREIGN KEY (created_by_staff_id) REFERENCES staff_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS refusal_revisions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  refusal_id BIGINT UNSIGNED NOT NULL,
  edited_by_staff_id INT(10) UNSIGNED NOT NULL,
  edit_reason VARCHAR(255) NOT NULL,
  before_json JSON NOT NULL,
  after_json JSON NOT NULL,
  diff_json JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ref_rev (refusal_id, created_at),
  CONSTRAINT fk_ref_rev_refusal FOREIGN KEY (refusal_id) REFERENCES refusals(id) ON DELETE CASCADE,
  CONSTRAINT fk_ref_rev_editor FOREIGN KEY (edited_by_staff_id) REFERENCES staff_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS refusal_staff_links (
  refusal_id BIGINT UNSIGNED NOT NULL,
  staff_id INT(10) UNSIGNED NOT NULL,
  role_in_refusal VARCHAR(64) NULL,
  PRIMARY KEY (refusal_id, staff_id),
  CONSTRAINT fk_ref_staff_refusal FOREIGN KEY (refusal_id) REFERENCES refusals(id) ON DELETE CASCADE,
  CONSTRAINT fk_ref_staff_staff FOREIGN KEY (staff_id) REFERENCES staff_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS refusal_files (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  refusal_id BIGINT UNSIGNED NOT NULL,
  uploaded_by_staff_id INT(10) UNSIGNED NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(128) NOT NULL,
  size_bytes BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ref_files (refusal_id, created_at),
  CONSTRAINT fk_ref_files_refusal FOREIGN KEY (refusal_id) REFERENCES refusals(id) ON DELETE CASCADE,
  CONSTRAINT fk_ref_files_uploader FOREIGN KEY (uploaded_by_staff_id) REFERENCES staff_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
