-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2020 at 12:30 AM
-- Server version: 5.6.21
-- PHP Version: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `covid_slayer`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_tokens`
--

CREATE TABLE IF NOT EXISTS `access_tokens` (
`access_token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access_token` varchar(300) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `game_configs`
--

CREATE TABLE IF NOT EXISTS `game_configs` (
`game_config_id` int(11) NOT NULL,
  `game_config_key` varchar(255) NOT NULL,
  `game_config_key_value` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `game_configs`
--

INSERT INTO `game_configs` (`game_config_id`, `game_config_key`, `game_config_key_value`, `date_created`, `date_updated`) VALUES
(1, 'blast_health_percent', '20', '2020-11-08 10:28:33', '2020-11-08 10:30:35'),
(2, 'game_duration', '60', '2020-11-08 10:28:33', '2020-11-08 10:30:38'),
(3, 'max_heal_count', '5', '2020-11-08 10:28:33', '2020-11-08 10:30:42'),
(4, 'max_heal_health_percent', '10', '2020-11-08 10:28:33', '2020-11-08 10:30:50'),
(5, 'max_blast_count', '2', '2020-11-08 10:28:33', '2020-11-08 10:32:05'),
(6, 'min_heal_health_percent', '5', '2020-11-08 10:28:33', '2020-11-08 10:30:58'),
(7, 'min_interval_for_next_turn', '2', '2020-11-08 10:28:33', '2020-11-08 10:31:02'),
(10, 'min_attack_health_percent', '3', '2020-11-08 15:17:48', '2020-11-08 15:17:48'),
(11, 'max_attack_health_percent', '10', '2020-11-08 15:17:48', '2020-11-08 15:17:48'),
(12, 'min_interval_for_next_blast', '15', '2020-11-08 22:37:06', '2020-11-08 22:37:06');

-- --------------------------------------------------------

--
-- Table structure for table `game_logs`
--

CREATE TABLE IF NOT EXISTS `game_logs` (
`game_log_id` int(11) NOT NULL,
  `game_session_id` int(11) NOT NULL,
  `operation_by` enum('','player','monster') NOT NULL DEFAULT '',
  `operation_type` enum('game_started','game_ended','attack','blast','heal','given_up','completed') NOT NULL,
  `operation_value` varchar(255) NOT NULL,
  `monster_health` int(11) NOT NULL DEFAULT '0',
  `player_health` int(11) NOT NULL DEFAULT '0',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=722 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `game_sessions`
--

CREATE TABLE IF NOT EXISTS `game_sessions` (
`game_session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_winner_type` enum('','player','monster') NOT NULL DEFAULT '',
  `game_status` enum('game_started','completed','given_up') NOT NULL DEFAULT 'game_started',
  `game_duration` int(11) NOT NULL,
  `game_start_timestamp` int(30) NOT NULL,
  `last_player_operation_timestamp` int(30) DEFAULT NULL,
  `last_monster_operation_timestamp` int(30) DEFAULT NULL,
  `next_attack_value_for_player` int(11) NOT NULL,
  `next_attack_value_for_monster` int(11) NOT NULL,
  `next_heal_value_for_player` int(11) NOT NULL,
  `next_heal_value_for_monster` int(11) NOT NULL,
  `player_current_health` int(11) NOT NULL,
  `monster_current_health` int(11) NOT NULL,
  `player_current_heal_count` int(11) NOT NULL,
  `player_current_blast_count` int(11) NOT NULL,
  `monster_current_heal_count` int(11) NOT NULL,
  `monster_current_blast_count` int(11) NOT NULL,
  `game_config` text NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
`user_id` int(11) NOT NULL,
  `user_full_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_avatar` varchar(1000) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_tokens`
--
ALTER TABLE `access_tokens`
 ADD PRIMARY KEY (`access_token_id`), ADD UNIQUE KEY `access_token` (`access_token`);

--
-- Indexes for table `game_configs`
--
ALTER TABLE `game_configs`
 ADD PRIMARY KEY (`game_config_id`), ADD UNIQUE KEY `game_config_key` (`game_config_key`);

--
-- Indexes for table `game_logs`
--
ALTER TABLE `game_logs`
 ADD PRIMARY KEY (`game_log_id`);

--
-- Indexes for table `game_sessions`
--
ALTER TABLE `game_sessions`
 ADD PRIMARY KEY (`game_session_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`user_id`), ADD UNIQUE KEY `user_email` (`user_email`), ADD UNIQUE KEY `user_email_2` (`user_email`), ADD UNIQUE KEY `user_email_3` (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_tokens`
--
ALTER TABLE `access_tokens`
MODIFY `access_token_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `game_configs`
--
ALTER TABLE `game_configs`
MODIFY `game_config_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `game_logs`
--
ALTER TABLE `game_logs`
MODIFY `game_log_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=722;
--
-- AUTO_INCREMENT for table `game_sessions`
--
ALTER TABLE `game_sessions`
MODIFY `game_session_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=98;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=40;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
