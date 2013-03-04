
CREATE TABLE user(
	uid INT PRIMARY KEY AUTO_INCREMENT,
	active TINYINT NOT NULL DEFAULT 1,
	login VARCHAR(50) UNIQUE NOT NULL,
	email VARCHAR(100) UNIQUE NOT NULL,
	password VARCHAR(32) NOT NULL, -- MD5
	firstname VARCHAR(50),
	lastname VARCHAR(50),
	bir_date DATETIME, -- birthday
	cre_uid INT NULL,
	upd_uid INT NOT NULL,
	cre_date DATETIME NOT NULL,
	upd_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (cre_uid) REFERENCES user(uid),
FOREIGN KEY (upd_uid) REFERENCES user(uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE role(
	rid INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(50) NOT NULL UNIQUE,
	summary VARCHAR(200)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE user_roles(
	uid INT NOT NULL,
	rid INT NOT NULL,
FOREIGN KEY (uid) REFERENCES user(uid),
FOREIGN KEY (rid) REFERENCES role(rid) ON DELETE CASCADE,
PRIMARY KEY(uid, rid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE chatbox(
	tid INT PRIMARY KEY AUTO_INCREMENT,
	uid INT NOT NULL,
	message VARCHAR(300) NOT NULL,
	edit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (uid) REFERENCES user(uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE files(
	fid INT PRIMARY KEY AUTO_INCREMENT,
	path VARCHAR(255) NOT NULL UNIQUE,
	filename VARCHAR(128) NOT NULL,
	mime_type VARCHAR(25) NOT NULL,
	size int  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE article_category(
	catid INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(128) NOT NULL UNIQUE,
	label VARCHAR(128) NOT NULL,
	fid INT NULL,
	parent_catid INT NULL,
	cre_uid INT NOT NULL,
	upd_uid INT NOT NULL,
	cre_date DATETIME NOT NULL,
	upd_date  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (parent_catid) REFERENCES article_category(catid),
FOREIGN KEY (fid) REFERENCES files(fid) ON DELETE SET NULL,
FOREIGN KEY (cre_uid) REFERENCES user(uid),
FOREIGN KEY (upd_uid) REFERENCES user(uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE article(
	aid INT PRIMARY KEY AUTO_INCREMENT,
	uid INT NOT NULL,
	catid INT NULL,
	view VARCHAR(255) NOT NULL DEFAULT 'default', -- Must point to a specific view placed in the application
	access SMALLINT, -- degre of accessibility: 1=Only for super-admin, 2=For administrators
	request_uri VARCHAR(255) NOT NULL UNIQUE,
	title VARCHAR(128),
	summary VARCHAR(255),
	body TEXT,
	greeting_text VARCHAR(200),
	signature VARCHAR(100), -- if null, get the author
	publish TINYINT DEFAULT 0,
	edit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (uid) REFERENCES user(uid),
FOREIGN KEY (catid) REFERENCES article_category(catid) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE article_category ADD aid INT NULL UNIQUE AFTER catid;
ALTER TABLE article_category ADD CONSTRAINT FOREIGN KEY (aid) REFERENCES article(aid);

CREATE TABLE article_history(
	ahid INT PRIMARY KEY AUTO_INCREMENT,
	aid INT NOT NULL,
	uid INT NOT NULL,
	catid INT,
	view VARCHAR(255) NOT NULL DEFAULT 'default', -- Must point to a specific view placed in the application
	access SMALLINT, -- degre of accessibility: 1=Only for super-admin, 2=For administrators
	request_uri VARCHAR(255),
	title VARCHAR(128),
	summary VARCHAR(255),
	body TEXT,
	greeting_text VARCHAR(200),
	signature VARCHAR(100), -- if null, get the author
	edit_date TIMESTAMP,
FOREIGN KEY (aid) REFERENCES article(aid),
FOREIGN KEY (uid) REFERENCES user(uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE article_comments(
	acid INT PRIMARY KEY AUTO_INCREMENT,
	aid INT NOT NULL,
	uid INT NOT NULL,
	message VARCHAR(510) NOT NULL,
	edit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (aid) REFERENCES article(aid),
FOREIGN KEY (uid) REFERENCES user(uid)	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE article_file_type(
	aftid INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE article_files(
	afid INT PRIMARY KEY AUTO_INCREMENT,
	aid INT NOT NULL,
	fid INT NOT NULL,
	aftid INT NOT NULL,
FOREIGN KEY (aid) REFERENCES article(aid),
FOREIGN KEY (fid) REFERENCES files(fid) ON DELETE CASCADE,
FOREIGN KEY (aftid) REFERENCES article_file_type(aftid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE parameters(
	param_key VARCHAR(100) PRIMARY KEY,
	param_value VARCHAR(510)	-- This must be a non unlimited "text" string
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- INSERTS

-- users (the password is test)
INSERT INTO `user` VALUES
(1,1,'Admin','admin@domain.com','21232f297a57a5a743894a0e4a801fc3','','',null, 1, 1, CURRENT_DATE_TIME, CURRENT_DATE_TIME);

-- Roles
INSERT INTO role(name, summary) VALUES
('super-admin', 'The owner. Most powerful role.'), 
('admin', 'Common administrator.'),
('frontend-user', 'Common register user'),
('public', '');

-- User roles
INSERT INTO user_roles(uid, rid) VALUES (1, 1), (2, 2), (3, 2), (4, 2), (5, 2), (6, 2);

-- article file types
INSERT INTO article_file_type(name) VALUES('logo'), ('vignette'), ('image'), ('video'), ('downloadable'), ('any');

-- articles
INSERT INTO `article` VALUES
(1,1,NULL,'default',NULL,'home','Hello world!','Lorem ipsum','Lorem ipsum',1,CURRENT_DATE_TIME);
