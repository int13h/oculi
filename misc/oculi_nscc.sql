CREATE TABLE IF NOT EXISTS host_info (
  timestamp             DATETIME,
  hostname              VARCHAR(255),
  mac                   CHAR(17),
  ip                    INT(10) UNSIGNED NOT NULL DEFAULT 0,
  version               TINYINT NOT NULL DEFAULT 0,
  service_pack          VARCHAR(20) NOT NULL DEFAULT 0,
  age			DATETIME,
  status		TINYINT NOT NULL DEFAULT 1,
  location		TINYINT NOT NULL DEFAULT 0,
  alert_av		TINYINT UNSIGNED NOT NULL DEFAULT 0,
  alert_os              TINYINT UNSIGNED NOT NULL DEFAULT 0,
  alert_status          TINYINT UNSIGNED NOT NULL DEFAULT 0,
  alert_history         TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (hostname),
  INDEX timestamp (timestamp),
  INDEX ip (ip),
  INDEX version (version),
  INDEX service_pack (service_pack),
  INDEX age (age),
  INDEX status (status),
  INDEX location (location))
  ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS updates (
  timestamp             DATETIME,
  hostname              VARCHAR(255),
  update_type           VARCHAR(50) NOT NULL DEFAULT 'unknown',
  update_id             VARCHAR(20) NOT NULL DEFAULT 'unknown',
  update_installed_by   VARCHAR(255),
  update_installed_on   DATETIME,
  PRIMARY KEY (hostname,update_id),
  INDEX timestamp (timestamp),
  INDEX update_type (update_type),
  INDEX update_installed_by (update_installed_by),
  INDEX update_installed_on (update_installed_on))
  ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS av (
  timestamp             DATETIME,
  hostname              VARCHAR(255),
  engine_version        VARCHAR(20) NOT NULL DEFAULT 'unknown',
  assig_version         VARCHAR(20) NOT NULL DEFAULT 'unknown',
  assig_applied         DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  avsig_version         VARCHAR(20) NOT NULL DEFAULT 'unknown',
  avsig_applied         DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  last_scan             DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (hostname),
  INDEX timestamp (timestamp),
  INDEX engine_version (engine_version),
  INDEX assig_version (assig_version),
  INDEX assig_applied (assig_applied),
  INDEX avsig_version (avsig_version),
  INDEX avsig_applied (avsig_applied),
  INDEX last_scan (last_scan))
  ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS asset (
  timestamp             DATETIME,
  hostname              VARCHAR(255),
  manufacturer          VARCHAR(50) NOT NULL DEFAULT 'unknown',
  model                 VARCHAR(50) NOT NULL DEFAULT 'unknown',
  serial_number         VARCHAR(255) NOT NULL DEFAULT 'unknown',
  asset_tag             VARCHAR(50) NOT NULL DEFAULT 'unknown',
  processor             VARCHAR(50) NOT NULL DEFAULT 'unknown',
  frequency		FLOAT(3,2) NOT NULL DEFAULT 0,
  memory                SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  storage		SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  user_id               VARCHAR(8) NOT NULL DEFAULT 'unknown',
  ooo                   ENUM('no','yes') DEFAULT 'no',
  PRIMARY KEY (hostname),
  INDEX timestamp (timestamp),
  INDEX manufacturer (manufacturer),
  INDEX model (model),
  INDEX serial_number (serial_number),
  INDEX asset_tag (asset_tag),
  INDEX processor (processor),
  INDEX frequency (frequency),
  INDEX memory (memory),
  INDEX storage (storage))
  ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ad (
  timestamp             TIMESTAMP,
  hostname              VARCHAR(255),
  PRIMARY KEY (hostname),
  INDEX timestamp (timestamp))
  ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS history (
  timestamp	DATETIME,
  site		VARCHAR(255),
  type          VARCHAR(2),
  n_sev		SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  l_sev		SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  m_sev		SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  h_sev		SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (timestamp,site,type),
  INDEX site (site),
  INDEX type (type),
  INDEX n_sev (n_sev),
  INDEX l_sev (l_sev),
  INDEX m_sev (m_sev),
  INDEX h_sev (h_sev))
  ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sites (
  site		VARCHAR(2),
  name		VARCHAR(100),
  type          TINYINT(1),
  address       VARCHAR(255),
  PRIMARY KEY (site))
  ENGINE=InnoDB;

INSERT IGNORE INTO sites (site,name,type,address) VALUES ('AK','Akerley',0,'10.9.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('AM','Amherst',0,'10.30.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('AN','AVCM',0,'10.25.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('BU','Burridge',0,'10.39.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('CC','Citrix',1,'10.%.40.%,10.%.41.%');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('CO','AVCL',0,'10.41.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('CU','Cumberland',0,'10.36.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('DG','Dartmouth Gate',0,'10.75.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('DI','Digby',0,'10.20.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('DW','Waterfront',0,'10.69.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('IN','Institute',0,'10.13.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('KI','Kingstec',0,'10.17.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('LU','Lunenburg',0,'10.15.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('MA','Marconi',0,'10.37.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('NS','Servers',1,'10.%.1.%');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('PI','Pictou',0,'10.19.0.0/16');
INSERT IGNORE INTO sites (site,name,type,address) VALUES ('SH','Shelburne',0,'10.11.0.0/16');
INSERT IGNORE INTO sites (site,name) VALUES ('ST','Strait Area');
INSERT IGNORE INTO sites (site,name) VALUES ('TR','Truro');
