-- Sappho Database Structure Definition
-- Database     : PostgreSQL
-- Version      : 8.3
-- last update  : 2011-08-18

-- 
-- Database has to exist before applying this script!
-- The database has to be empty (no tables defined)!

--
-- DO NOT use this script for updating your database to a newer version!
--

-- This script is transactional!
BEGIN;

-- --------------------------------------------------------
--        D R O P    O L D    T A B L E S
-- --------------------------------------------------------

-- We start with dropping all existing tables
DROP TABLE IF EXISTS area;
DROP TABLE IF EXISTS object;
DROP TABLE IF EXISTS object_data;
DROP TABLE IF EXISTS profile;
DROP TABLE IF EXISTS "user";
DROP TABLE IF EXISTS user_area;
DROP TABLE IF EXISTS versioned_data;

-- --------------------------------------------------------
--            T A B L E    S T R U C T U R E
-- --------------------------------------------------------

-- Areas

CREATE TABLE area (
   area_aid	SERIAL 			NOT NULL,
   area_name VARCHAR(255) 	NOT NULL,
  PRIMARY KEY (area_aid)
);

-- --------------------------------------------------------

-- Objects

CREATE TABLE object (
   object_id 			SERIAL 			NOT NULL,
   object_type			VARCHAR(1)		NOT NULL,
   object_name			VARCHAR(255) 	NOT NULL,
   object_areaid		INTEGER 		NOT NULL,
   object_parent		INTEGER 		NOT NULL,
   object_locked_uid  	INTEGER 		NOT NULL,
  PRIMARY KEY (object_id)
);

-- --------------------------------------------------------

-- Object Data

CREATE TABLE object_data (
   object_data_id		INTEGER 			NOT NULL,
   object_data_text		TEXT			NOT NULL,
   object_data_blob		VARCHAR(255) 	NOT NULL,
  PRIMARY KEY (object_data_id)
);

-- --------------------------------------------------------

-- User Profiles

CREATE TABLE profile (
   profile_uid 			INTEGER			NOT NULL,
   profile_firstname 	VARCHAR(255)	NOT NULL,
   profile_lastname		VARCHAR(255)	NOT NULL,
  PRIMARY KEY (profile_uid)
);

-- --------------------------------------------------------

-- User Login and ID data
CREATE TABLE "user" (
   user_uid 		SERIAL 			NOT NULL,
   user_name 		VARCHAR(30) 	NOT NULL,
   user_password	VARCHAR(256) 	NOT NULL,
  PRIMARY KEY (user_uid)
);

-- --------------------------------------------------------

-- Association of users to areas

CREATE TABLE user_area (
   user_area_uid	INTEGER	NOT NULL,
   user_area_aid	INTEGER	NOT NULL,
  PRIMARY KEY (user_area_uid, user_area_aid)
);

-- --------------------------------------------------------

-- Object data history

CREATE TABLE versioned_data (
   versioned_data_id	INTEGER			NOT NULL,
   versioned_data_lnr	INTEGER			NOT NULL,
   versioned_data_text	TEXT			NOT NULL,
   versioned_data_blob	VARCHAR(255)	NOT NULL,
   versioned_data_time	TIMESTAMP		NOT NULL,
   versioned_data_user	INTEGER 		NOT NULL,
  PRIMARY KEY (versioned_data_id, versioned_data_lnr)
);

-- --------------------------------------------------------
--                 C O N S T R A I N T S
-- --------------------------------------------------------

-- unique keys
ALTER TABLE area	ADD UNIQUE (area_name);
ALTER TABLE "user"	ADD UNIQUE (user_name);

-- End transaction
COMMIT;

