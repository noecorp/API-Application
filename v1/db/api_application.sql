-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Jeu 14 Juillet 2016 à 16:33
-- Version du serveur :  5.6.27-log
-- Version de PHP :  5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `api_application`
--

-- --------------------------------------------------------

--
-- Structure de la table `application`
--

CREATE TABLE IF NOT EXISTS `application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `maintainer_id` int(11) DEFAULT NULL,
  `title` varchar(50) NOT NULL,
  `web` varchar(100) DEFAULT NULL,
  `slogan` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `application_title` (`title`),
  KEY `author_id` (`author_id`),
  KEY `maintainer_id` (`maintainer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Contenu de la table `application`
--

INSERT INTO `application` (`id`, `author_id`, `maintainer_id`, `title`, `web`, `slogan`) VALUES
(1, 11, 11, 'Adminer', 'http://www.adminer.org/', 'Database management in single PHP file'),
(2, 12, 12, 'JUSH', 'http://jush.sourceforge.net/', 'JavaScript Syntax Highlighter'),
(6, 15, 15, 'MyApp', 'http://github/slyRush/My-App', 'Do more app');

-- --------------------------------------------------------

--
-- Structure de la table `application_tag`
--

CREATE TABLE IF NOT EXISTS `application_tag` (
  `application_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`application_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `application_tag`
--

INSERT INTO `application_tag` (`application_id`, `tag_id`) VALUES
(1, 21),
(1, 22);

-- --------------------------------------------------------

--
-- Structure de la table `author`
--

CREATE TABLE IF NOT EXISTS `author` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `api_key` varchar(32) NOT NULL,
  `password_hash` longtext NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email_index` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Contenu de la table `author`
--

INSERT INTO `author` (`id`, `name`, `email`, `api_key`, `password_hash`, `status`, `created_at`) VALUES
(11, 'Jakub Vrana', 'jakub@mail.com', 'd116f4d76eb945f02cc0c36a2f082f94', '$2a$10$2369e48260a538e80e1ede/t07R7856KXp8WRVeIk3cQzUJryGE/2', 0, '2016-07-12 12:29:26'),
(12, 'David Grudl', 'david@mail.com', 'b78af50c0456a8af7ae0e28dad8a5f73', '$2a$10$a9bc723db953f0982292euWgCUovhefdUlhXxBHgfnKb4uqtXOKze', 0, '2016-07-12 12:29:46'),
(13, 'Rico Maddy', 'rico@mail.com', 'c5d6c39b2502421b2fda760b31f623ec', '$2a$10$6548da6abccd55a638aafu2gKVgxIjWPo1Iepui.Ff6/7o.uryjba', 0, '2016-07-12 12:29:26'),
(14, 'Ra Em', 'em@mail.com', '7b9431d653beaecd3b4cf070d34c08df', '$2a$10$b20cc7787c6c36f306cf1uVfous1exbxotIxg0BRD8me1MJcuWPpi', 0, '2016-07-13 12:45:06'),
(15, 'Tex com', 'tex@mail.com', '763e3c17c8a70506e7b3681c2300f79a', '$2a$10$6aa07dbd20f1e31b1dfe8ul0ws48x4wh8tUutQzNvPZmr.u5aO0UC', 0, '2016-07-13 13:16:49');

-- --------------------------------------------------------

--
-- Structure de la table `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

--
-- Contenu de la table `tag`
--

INSERT INTO `tag` (`id`, `name`) VALUES
(21, 'PHP'),
(22, 'MySQL'),
(24, 'Ruby'),
(25, 'XP/Vista/7'),
(26, 'HMO'),
(27, 'Digital Journalism'),
(29, 'LDPE'),
(30, 'Wholesale Lending'),
(31, 'Utilities Management'),
(32, 'Commercial Lending');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` text NOT NULL,
  `api_key` varchar(32) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `application`
--
ALTER TABLE `application`
  ADD CONSTRAINT `application_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`),
  ADD CONSTRAINT `application_ibfk_2` FOREIGN KEY (`maintainer_id`) REFERENCES `author` (`id`);

--
-- Contraintes pour la table `application_tag`
--
ALTER TABLE `application_tag`
  ADD CONSTRAINT `application_tag_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`),
  ADD CONSTRAINT `application_tag_ibfk_2` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
