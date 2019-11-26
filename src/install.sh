CREATE DATABASE drop_uiwnsdoioh;
USE drop_uiwnsdoioh;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    last_login TIMESTAMP,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=INNODB;

CREATE TABLE drops (
    drop_id INT AUTO_INCREMENT PRIMARY KEY,
    owning_user_id INT NOT NULL,
    drop_key VARCHAR(32) NOT NULL,
    create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    drop_date DATETIME,
    drop_status VARCHAR(16) NOT NULL,
    drop_note VARCHAR(1024),
    file_name VARCHAR(128) NOT NULL,
    file_path VARCHAR(256) NOT NULL,
    sha_256_hash VARCHAR(64) NOT NULL,
    source_ip VARCHAR(64) NOT NULL
) ENGINE=INNODB;

#CREATE USER 'drop_xio89vweoifh'@'localhost' IDENTIFIED BY 'hiHWOIjfoiwj342';
GRANT ALL PRIVILEGES ON drop_uiwnsdoioh.* TO 'drop_xio89vweoifh'@'%' IDENTIFIED BY 'hiHWOIjfoiwj342';

# insert into drops (owning_user_id, drop_key, drop_status, drop_note, file_name, file_path, sha_256_hash, source_ip) values (1, 'adcdefg', 'NEW', 'Testing', 'test.jpg', '/tmp', 'fffffff', '1.2.3.4');