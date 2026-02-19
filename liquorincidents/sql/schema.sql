
-- BRAC Tools Incident/Register Module (WA) - MySQL 8+
-- Creates incident register, refusal register, staff link, audit trail, and photo attachments.

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  display_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NULL,
  role ENUM('staff','manager','admin') NOT NULL DEFAULT 'staff',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS incidents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  incident_no VARCHAR(32) NOT NULL UNIQUE,
  incident_date DATE NOT NULL,
  incident_time TIME NOT NULL,
  location VARCHAR(255) NULL,
  cctv_available TINYINT(1) NULL,
  approved_manager_name VARCHAR(255) NULL,
  reporting_person_name VARCHAR(255) NULL,
  incident_type VARCHAR(120) NOT NULL,
  authorities_notified_json JSON NULL,
  police_notified TINYINT(1) NOT NULL DEFAULT 0,
  physical_force TINYINT(1) NOT NULL DEFAULT 0,
  incident_details TEXT NULL,
  actions_taken TEXT NULL,
  soft_locked TINYINT(1) NOT NULL DEFAULT 1,
  created_by_user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS incident_staff_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  incident_id INT NOT NULL,
  user_id INT NOT NULL,
  role_in_incident VARCHAR(120) NULL, -- e.g. Employee, Crowd Controller, Witness
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Every edit creates a revision row with before/after snapshots + reason.
CREATE TABLE IF NOT EXISTS incident_revisions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  incident_id INT NOT NULL,
  edited_by_user_id INT NOT NULL,
  edited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  edit_reason VARCHAR(255) NULL,
  before_json JSON NOT NULL,
  after_json JSON NOT NULL,
  diff_json JSON NULL,
  FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
  FOREIGN KEY (edited_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS incident_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  incident_id INT NOT NULL,
  uploaded_by_user_id INT NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(120) NOT NULL,
  file_size INT NOT NULL,
  sha256 CHAR(64) NOT NULL,
  storage_path VARCHAR(500) NOT NULL,
  FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
  FOREIGN KEY (uploaded_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS refusals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  refusal_no VARCHAR(32) NOT NULL UNIQUE,
  refusal_date DATE NOT NULL,
  refusal_time TIME NOT NULL,
  location VARCHAR(255) NULL,
  cctv_available TINYINT(1) NULL,
  reason VARCHAR(255) NOT NULL,
  comments TEXT NULL,
  approved_manager_name VARCHAR(255) NULL,
  police_notified TINYINT(1) NOT NULL DEFAULT 0,
  created_by_user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS refusal_staff_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  refusal_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (refusal_id) REFERENCES refusals(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Generic audit log for all actions (view/export optional).
CREATE TABLE IF NOT EXISTS audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(60) NOT NULL, -- CREATE_INCIDENT, EDIT_INCIDENT, UPLOAD_FILE, CREATE_REFUSAL, EXPORT_REPORT, etc
  entity_type VARCHAR(40) NOT NULL, -- incident/refusal/report
  entity_id INT NULL,
  meta_json JSON NULL,
  ip_addr VARCHAR(60) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_action_created_at (action, created_at),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

