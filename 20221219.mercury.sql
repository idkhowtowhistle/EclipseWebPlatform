-- MySQL dump 10.19  Distrib 10.3.37-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: krypton
-- ------------------------------------------------------
-- Server version	10.3.37-MariaDB-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `krypton`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `krypton` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `krypton`;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `blurb` text NOT NULL,
  `price` int(11) DEFAULT NULL,
  `type` tinyint(11) NOT NULL,
  `creatorId` int(11) NOT NULL,
  `approved` int(11) NOT NULL DEFAULT 0,
  `deleted` int(11) NOT NULL DEFAULT 0,
  `createdOn` int(11) NOT NULL,
  `updatedOn` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit`
--

DROP TABLE IF EXISTS `audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userIdModerated` int(11) NOT NULL,
  `moderatorId` int(11) NOT NULL,
  `type` text NOT NULL,
  `modoption` text NOT NULL,
  `action` text NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit`
--

LOCK TABLES `audit` WRITE;
/*!40000 ALTER TABLE `audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `color` varchar(100) NOT NULL,
  `textcolor` text NOT NULL,
  `username` text NOT NULL,
  `message` text NOT NULL,
  `date` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bans`
--

DROP TABLE IF EXISTS `bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `bannerId` int(11) NOT NULL,
  `timeStarted` int(11) NOT NULL,
  `timeEnds` int(11) NOT NULL,
  `reason` text NOT NULL,
  `type` tinyint(4) NOT NULL,
  `undone` tinyint(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bans`
--

LOCK TABLES `bans` WRITE;
/*!40000 ALTER TABLE `bans` DISABLE KEYS */;
/*!40000 ALTER TABLE `bans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `friends`
--

DROP TABLE IF EXISTS `friends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friends` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requesterId` int(11) NOT NULL,
  `receiverId` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `friends`
--

LOCK TABLES `friends` WRITE;
/*!40000 ALTER TABLE `friends` DISABLE KEYS */;
/*!40000 ALTER TABLE `friends` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_sessions`
--

DROP TABLE IF EXISTS `game_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serverId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `ticket` text NOT NULL,
  `ping` int(11) NOT NULL DEFAULT 0,
  `valid` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_sessions`
--

LOCK TABLES `game_sessions` WRITE;
/*!40000 ALTER TABLE `game_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `game_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `games`
--

DROP TABLE IF EXISTS `games`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serverName` text NOT NULL,
  `serverDesc` text NOT NULL,
  `serverIp` text NOT NULL,
  `serverPort` int(11) NOT NULL,
  `serverSize` int(11) NOT NULL,
  `serverVisits` int(11) DEFAULT 0,
  `createdOn` int(11) NOT NULL,
  `lastUpdated` int(11) NOT NULL,
  `ownerId` int(11) NOT NULL,
  `isPrivate` tinyint(4) DEFAULT 0,
  `privateKey` varchar(200) NOT NULL,
  `serverTicket` text NOT NULL,
  `isDeleted` tinyint(4) NOT NULL DEFAULT 0,
  `serverPing` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `games`
--

LOCK TABLES `games` WRITE;
/*!40000 ALTER TABLE `games` DISABLE KEYS */;
/*!40000 ALTER TABLE `games` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invitekeys`
--

DROP TABLE IF EXISTS `invitekeys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invitekeys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inviteKey` varchar(128) NOT NULL,
  `creatorId` int(11) NOT NULL,
  `createdOn` int(11) NOT NULL,
  `usedById` int(11) DEFAULT NULL,
  `keyUsed` tinyint(4) DEFAULT 0,
  `deleted` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invitekeys`
--

LOCK TABLES `invitekeys` WRITE;
/*!40000 ALTER TABLE `invitekeys` DISABLE KEYS */;
/*!40000 ALTER TABLE `invitekeys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `messageContent` text NOT NULL,
  `messageFrom` int(11) NOT NULL,
  `messageTo` int(11) NOT NULL,
  `messageDate` int(11) NOT NULL,
  `senderStatus` tinyint(4) DEFAULT 0,
  `sentToStatus` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `date` int(11) NOT NULL,
  `news` text NOT NULL,
  `createdBy` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporterID` int(11) NOT NULL,
  `abuserID` int(11) NOT NULL,
  `abuse` tinytext NOT NULL,
  `description` tinytext NOT NULL,
  `messages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `sessionKey` varchar(256) NOT NULL,
  `userId` int(11) NOT NULL,
  `valid` int(11) NOT NULL DEFAULT 1,
  `userAgent` text NOT NULL,
  `ip` tinytext NOT NULL,
  `created` int(11) NOT NULL,
  `lastonline` int(11) NOT NULL,
  `csrf` tinytext NOT NULL,
  `twofaVerified` tinyint(4) DEFAULT 0,
  `expires` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subforum_replies`
--

DROP TABLE IF EXISTS `subforum_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subforum_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `replyTo` int(11) NOT NULL,
  `subForumId` int(11) NOT NULL,
  `authorId` int(11) NOT NULL,
  `datePosted` int(11) NOT NULL,
  `replyContents` text NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subforum_replies`
--

LOCK TABLES `subforum_replies` WRITE;
/*!40000 ALTER TABLE `subforum_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `subforum_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subforum_threads`
--

DROP TABLE IF EXISTS `subforum_threads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subforum_threads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subForumId` int(11) NOT NULL,
  `postTitle` text NOT NULL,
  `postContents` text NOT NULL,
  `authorId` int(11) NOT NULL,
  `datePosted` int(11) NOT NULL,
  `canReply` tinyint(4) NOT NULL DEFAULT 1,
  `bumpIndex` int(11) NOT NULL,
  `deleted` tinyint(4) DEFAULT 0,
  `locked` tinyint(4) NOT NULL DEFAULT 0,
  `pinned` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subforum_threads`
--

LOCK TABLES `subforum_threads` WRITE;
/*!40000 ALTER TABLE `subforum_threads` DISABLE KEYS */;
/*!40000 ALTER TABLE `subforum_threads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subforums`
--

DROP TABLE IF EXISTS `subforums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subforums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subName` text NOT NULL,
  `subDesc` text NOT NULL,
  `isPostingAllowed` tinyint(4) NOT NULL DEFAULT 1,
  `subImportance` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subforums`
--

LOCK TABLES `subforums` WRITE;
/*!40000 ALTER TABLE `subforums` DISABLE KEYS */;
INSERT INTO `subforums` VALUES (1,'Generic category','Talk about anything here',1,1);
/*!40000 ALTER TABLE `subforums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT '1: created 2: sold 3: bought',
  `creator` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `soldToId` int(11) DEFAULT NULL,
  `boughtFromId` int(11) DEFAULT NULL,
  `itemId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` tinytext NOT NULL,
  `pwdchange` int(11) DEFAULT 0,
  `createdInv` int(11) DEFAULT 0,
  `email` tinytext NOT NULL,
  `blurb` varchar(255) NOT NULL,
  `messages` tinyint(4) DEFAULT 1,
  `ssc` tinyint(4) NOT NULL DEFAULT 0,
  `canCreateKeys` tinyint(4) NOT NULL DEFAULT 1,
  `theme` tinyint(4) NOT NULL DEFAULT 0,
  `gameBan` tinyint(4) NOT NULL DEFAULT 0,
  `currency` int(11) DEFAULT 0,
  `nextStipend` int(11) DEFAULT 0,
  `admin` tinyint(4) NOT NULL DEFAULT 1,
  `joined` int(11) NOT NULL,
  `lastonline` int(11) NOT NULL,
  `lastaction` int(11) DEFAULT 0,
  `twofa` tinyint(4) DEFAULT 0,
  `twofaRecoveryCodes` longtext DEFAULT NULL,
  `twofaSecret` varchar(110) DEFAULT '0',
  `ip` tinytext DEFAULT NULL,
  `discordKey` tinytext DEFAULT NULL,
  `discordId` bigint(20) DEFAULT NULL,
  `discordVerifyTime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-12-19 22:44:41
