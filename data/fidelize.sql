-- phpMyAdmin SQL Dump
-- version 3.5.0
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Ago 26, 2020 alle 11:45
-- Versione del server: 5.5.24-log
-- Versione PHP: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `fidelize`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `bolt_contacts`
--

CREATE TABLE IF NOT EXISTS `bolt_contacts` (
  `id_contact` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_social` int(11) NOT NULL,
  PRIMARY KEY (`id_contact`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bolt_settings_user`
--

CREATE TABLE IF NOT EXISTS `bolt_settings_user` (
  `id_setting` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `setting_name` varchar(50) NOT NULL,
  `setting_value` varchar(1000) NOT NULL,
  PRIMARY KEY (`id_setting`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bolt_socialusers`
--

CREATE TABLE IF NOT EXISTS `bolt_socialusers` (
  `id_social` int(11) NOT NULL AUTO_INCREMENT,
  `oauth_provider` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `oauth_uid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `id_user` int(11) NOT NULL,
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `picture` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_social`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bolt_tokens`
--

CREATE TABLE IF NOT EXISTS `bolt_tokens` (
  `id_token` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `type` varchar(250) DEFAULT NULL,
  `status` varchar(250) NOT NULL DEFAULT 'null',
  `token_price` float NOT NULL,
  `token_ricevuti` float NOT NULL,
  `fiat_price` float NOT NULL,
  `currency` varchar(10) NOT NULL,
  `item_desc` varchar(60) NOT NULL,
  `item_code` varchar(60) NOT NULL,
  `invoice_timestamp` int(11) NOT NULL,
  `expiration_timestamp` int(11) NOT NULL,
  `rate` float NOT NULL,
  `from_address` varchar(250) NOT NULL,
  `to_address` varchar(250) NOT NULL,
  `blocknumber` float NOT NULL,
  `txhash` varchar(80) NOT NULL,
  PRIMARY KEY (`id_token`),
  KEY `to_address` (`to_address`),
  KEY `from_address` (`from_address`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bolt_tokens_memo`
--

CREATE TABLE IF NOT EXISTS `bolt_tokens_memo` (
  `id_tmemo` int(11) NOT NULL AUTO_INCREMENT,
  `id_token` int(11) NOT NULL,
  `memo` varchar(550) NOT NULL,
  PRIMARY KEY (`id_tmemo`),
  KEY `id_tokens` (`id_token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bolt_users`
--

CREATE TABLE IF NOT EXISTS `bolt_users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ga_secret_key` varchar(16) DEFAULT NULL,
  `activation_code` varchar(50) NOT NULL,
  `status_activation_code` int(11) NOT NULL,
  `oauth_provider` varchar(8) NOT NULL,
  `oauth_uid` varchar(100) NOT NULL,
  PRIMARY KEY (`id_user`),
  KEY `index email` (`email`),
  KEY `index password` (`password`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bolt_vapid_subscription`
--

CREATE TABLE IF NOT EXISTS `bolt_vapid_subscription` (
  `id_subscription` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `browser` varchar(1000) NOT NULL,
  `endpoint` varchar(1000) NOT NULL,
  `auth` varchar(1000) NOT NULL,
  `p256dh` varchar(1000) NOT NULL,
  PRIMARY KEY (`id_subscription`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bolt_wallets`
--

CREATE TABLE IF NOT EXISTS `bolt_wallets` (
  `id_wallet` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `wallet_address` varchar(50) NOT NULL,
  `blocknumber` varchar(50) NOT NULL DEFAULT '0x0',
  PRIMARY KEY (`id_wallet`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `np_log`
--

CREATE TABLE IF NOT EXISTS `np_log` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `remote_address` varchar(60) COLLATE utf8_bin NOT NULL,
  `browser` varchar(500) COLLATE utf8_bin NOT NULL,
  `app` varchar(50) COLLATE utf8_bin NOT NULL,
  `controller` varchar(50) COLLATE utf8_bin NOT NULL,
  `action` varchar(50) COLLATE utf8_bin NOT NULL,
  `description` longtext COLLATE utf8_bin NOT NULL,
  `die` int(1) NOT NULL,
  PRIMARY KEY (`id_log`),
  KEY `id_log` (`id_log`),
  KEY `FK_LOG_USER` (`id_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=5167 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `np_nodes`
--

CREATE TABLE IF NOT EXISTS `np_nodes` (
  `id_node` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(100) NOT NULL,
  `port` varchar(10) NOT NULL,
  PRIMARY KEY (`id_node`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `np_notifications`
--

CREATE TABLE IF NOT EXISTS `np_notifications` (
  `id_notification` int(11) NOT NULL AUTO_INCREMENT,
  `type_notification` varchar(250) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_tocheck` int(11) NOT NULL,
  `status` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `url` varchar(250) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `price` float NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_notification`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `np_notifications_readers`
--

CREATE TABLE IF NOT EXISTS `np_notifications_readers` (
  `id_notifications_reader` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_notification` int(11) NOT NULL,
  `alreadyread` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_notifications_reader`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `np_settings_webapp`
--

CREATE TABLE IF NOT EXISTS `np_settings_webapp` (
  `id_setting` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(50) NOT NULL,
  `setting_value` varchar(15000) NOT NULL,
  PRIMARY KEY (`id_setting`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=112 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `np_wallets`
--

CREATE TABLE IF NOT EXISTS `np_wallets` (
  `id_wallet` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `wallet_address` varchar(50) NOT NULL,
  `blocknumber` varchar(50) NOT NULL DEFAULT '0x0',
  PRIMARY KEY (`id_wallet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
