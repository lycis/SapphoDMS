-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 15. August 2011 um 23:07
-- Server Version: 5.5.8
-- PHP-Version: 5.3.5

--
-- Sappho DMS Backend Database
-- Last Change: 2011-08-15
--
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `sappho`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `area`
--
-- Erzeugt am: 14. August 2011 um 17:23
--

DROP TABLE IF EXISTS `area`;
CREATE TABLE IF NOT EXISTS `area` (
  `area_aid` int(11) NOT NULL AUTO_INCREMENT,
  `area_name` varchar(255) NOT NULL,
  PRIMARY KEY (`area_aid`),
  UNIQUE KEY `area_name` (`area_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `object`
--
-- Erzeugt am: 14. August 2011 um 19:31
--

DROP TABLE IF EXISTS `object`;
CREATE TABLE IF NOT EXISTS `object` (
  `object_id` int(11) NOT NULL AUTO_INCREMENT,
  `object_type` varchar(1) NOT NULL,
  `object_name` varchar(255) NOT NULL,
  `object_areaid` int(11) NOT NULL,
  `object_parent` int(11) NOT NULL,
  `object_locked_uid` int(11) NOT NULL,
  PRIMARY KEY (`object_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `object_data`
--
-- Erzeugt am: 15. August 2011 um 20:48
--

DROP TABLE IF EXISTS `object_data`;
CREATE TABLE IF NOT EXISTS `object_data` (
  `object_data_id` int(11) NOT NULL,
  `object_data_text` text NOT NULL,
  `object_data_blob` blob NOT NULL,
  PRIMARY KEY (`object_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `profile`
--
-- Erzeugt am: 14. August 2011 um 15:08
--

DROP TABLE IF EXISTS `profile`;
CREATE TABLE IF NOT EXISTS `profile` (
  `profile_uid` int(11) NOT NULL,
  `profile_firstname` varchar(255) NOT NULL,
  `profile_lastname` varchar(255) NOT NULL,
  PRIMARY KEY (`profile_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--
-- Erzeugt am: 14. August 2011 um 11:30
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_uid` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(30) NOT NULL,
  `user_password` varchar(256) NOT NULL,
  PRIMARY KEY (`user_uid`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_area`
--
-- Erzeugt am: 14. August 2011 um 17:24
--

DROP TABLE IF EXISTS `user_area`;
CREATE TABLE IF NOT EXISTS `user_area` (
  `user_area_uid` int(11) NOT NULL,
  `user_area_aid` int(11) NOT NULL,
  PRIMARY KEY (`user_area_uid`,`user_area_aid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
COMMIT;
