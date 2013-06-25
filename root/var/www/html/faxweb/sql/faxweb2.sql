-- MySQL dump 10.9
--
-- Host: localhost    Database: faxdb
-- ------------------------------------------------------
-- Server version	4.1.20

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


CREATE DATABASE IF NOT EXISTS faxdb;

USE faxdb;

CREATE TABLE IF NOT EXISTS `faxweb_association` (
  `fax` varchar(64) NOT NULL default '',
  `nome` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`fax`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `faxweb_documents` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `id_m` varchar(40) default NULL,
  `data` datetime default '0000-00-00 00:00:00',
  `descrizione` varchar(255) default NULL,
  `utente` varchar(100) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `faxweb_fax` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `fax_type` char(1) default NULL,
  `number` varchar(64) default NULL,
  `name` varchar(64) default NULL,
  `device` varchar(20) default NULL,
  `filename` varchar(40) default NULL,
  `path` longtext,
  `rpath` longtext,
  `type` varchar(32) NOT NULL default '',
  `downloads` int(11) default '0',
  `status` enum('found','lost') default 'found',
  `flags` enum('hot','emergency','normal') default 'normal',
  `description` longtext,
  `id_m` varchar(20) default NULL,
  `thumb` blob,
  `thumbC` tinyint(1) default '0',
  `sendto` varchar(40) default NULL,
  `msg` varchar(240) default NULL,
  `com_id` int(11) default '0',
  `date` datetime NOT NULL,
  `pages` int(11) default '0',
  `duration` varchar(10) default '0',
  `quality` varchar(20) default '0',
  `rate` varchar(30) default '0',
  `data` varchar(30) default '0',
  `errcorr` varchar(30) default '0',
  `page` varchar(30) default '0',
  `resends` int(11) default '0',
  `resend_rcp` varchar(240) default NULL,
  `forward_rcp` varchar(240) default NULL,
  `letto` varchar(20) default '0',
  `doc_id` bigint(20) default '0',
  `tts` varchar(20) default '??:??',
  `ktime` bigint(20) default '0',
  `rtime` bigint(20) default '0',
  `job_id` int(11) default '0',
  `state` varchar(100) default NULL,
  `user` varchar(100) default NULL,
  `attempts` int(11) default '0',
  `esito` varchar(100) default 'In Corso ...',
  `tipo` char(1) default 'W',
  `deleted` char(1) default '0',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `name` (`name`,`number`,`description`,`filename`),
  FULLTEXT KEY `name_2` (`name`,`number`,`description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `faxweb_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `ip` text NOT NULL,
  `type` text NOT NULL,
  `details` text NOT NULL,
  `user` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


GRANT ALL ON faxdb.* TO faxuser@localhost  IDENTIFIED BY 'faxpass';
FLUSH PRIVILEGES;


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

