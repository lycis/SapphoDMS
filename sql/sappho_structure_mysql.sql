-- Sappho Database Structure Definition
-- Database     : MySQL
-- Version      : 5.1.41
-- last update  : 2011-08-18

-- 
-- Database has to exist before applying this script!
-- The database has to be empty (no tables defined)!

--
-- DO NOT use this script for updating your database to a newer version!
--

-- This script is transactional!

SET AUTOCOMMIT=0;
START TRANSACTION;
-- --------------------------------------------------------
--        D R O P    O L D    T A B L E S
-- --------------------------------------------------------

-- We start with dropping all existing tables
DROP TABLE IF EXISTS area;
DROP TABLE IF EXISTS object;
DROP TABLE IF EXISTS object_data;
DROP TABLE IF EXISTS profile;
DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS user_area;
DROP TABLE IF EXISTS versioned_data;

-- --------------------------------------------------------
--            T A B L E    S T R U C T U R E
-- --------------------------------------------------------

-- Areas

CREATE TABLE area (
   area_aid		INT(11)			NOT NULL	AUTO_INCREMENT,
   area_name	VARCHAR(255)	NOT NULL,
  PRIMARY KEY (area_aid)
);

-- --------------------------------------------------------

-- Objects

CREATE TABLE object (
   object_id 			INT(11) 		NOT NULL	AUTO_INCREMENT,
   object_type 			VARCHAR(1) 		NOT NULL,
   object_name 			VARCHAR(255) 	NOT NULL,
   object_areaid		INT(11) 		NOT NULL,
   object_parent		INT(11) 		NOT NULL,
   object_locked_uid	INT(11) 		NOT NULL,
  PRIMARY KEY (object_id)
);

-- --------------------------------------------------------

-- Object Data

CREATE TABLE object_data (
   object_data_id	INT(11) 		NOT NULL,
   object_data_text	TEXT 			NOT NULL,
   object_data_blob VARCHAR(256) 	NOT NULL,
  PRIMARY KEY (object_data_id)
);

-- --------------------------------------------------------

-- User Profiles

CREATE TABLE profile (
   profile_uid			INT(11) 		NOT NULL,
   profile_firstname	VARCHAR(255) 	NOT NULL,
   profile_lastname		VARCHAR(255) 	NOT NULL,
  PRIMARY KEY (profile_uid)
);

-- --------------------------------------------------------

-- User Login and ID data Tabellenstruktur für Tabelle `user`

CREATE TABLE user (
   user_uid 		INT(11) 		NOT NULL	AUTO_INCREMENT,
   user_name 		VARCHAR(30) 	NOT NULL,
   user_password 	VARCHAR(256)	NOT NULL,
  PRIMARY KEY (user_uid)
);

-- --------------------------------------------------------

-- Association of users to areas

CREATE TABLE user_area (
   user_area_uid	INT(11)		NOT NULL,
   user_area_aid	INT(11)		NOT NULL,
  PRIMARY KEY (user_area_uid, user_area_aid)
);

-- --------------------------------------------------------

-- Object data history

CREATE TABLE versioned_data (
   versioned_data_id	INT(11)			NOT NULL,
   versioned_data_lnr	INT(11)			NOT NULL,
   versioned_data_text	TEXT			NOT NULL,
   versioned_data_blob	VARCHAR(256)	NOT NULL,
   versioned_data_time	DATETIME		NOT NULL,
   versioned_data_user INT(11)			NOT NULL,
  PRIMARY KEY (versioned_data_id, versioned_data_lnr)
);


-- --------------------------------------------------------
--                 C O N S T R A I N T S
-- --------------------------------------------------------

-- unique keys
ALTER TABLE area ADD UNIQUE (area_name);

ALTER TABLE user ADD UNIQUE (user_name);

-- foreign keys
ALTER TABLE object_data	ADD FOREIGN KEY (object_data_id)
  REFERENCES object(object_id) ON DELETE CASCADE;
  
ALTER TABLE profile ADD FOREIGN KEY (profile_uid) REFERENCES user(user_uid)
  ON DELETE CASCADE;
  
ALTER TABLE user_area ADD FOREIGN KEY (user_area_uid) REFERENCES user(user_uid)
  ON DELETE CASCADE;
  
ALTER TABLE	user_area ADD FOREIGN KEY (user_area_aid) REFERENCES area(area_aid)
  ON DELETE CASCADE;
  
ALTER TABLE versioned_data ADD FOREIGN KEY(versioned_data_id) 
  REFERENCES object_data(object_data_id) ON DELETE CASCADE;

-- End transaction
COMMIT;

