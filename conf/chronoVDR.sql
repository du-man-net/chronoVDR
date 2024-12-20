-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : ven. 20 déc. 2024 à 08:06
-- Version du serveur : 10.11.6-MariaDB-0+deb12u1
-- Version de PHP : 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `chronoVDR`
--

-- --------------------------------------------------------

--
-- Structure de la table `activites`
--

CREATE TABLE `activites` (
  `id` int(11) NOT NULL,
  `nom` varchar(32) NOT NULL DEFAULT 'sans_nom',
  `organisateur` varchar(32) NOT NULL DEFAULT 'sans_nom',
  `password` char(40) DEFAULT NULL,
  `date_activite` datetime NOT NULL DEFAULT current_timestamp(),
  `vue` varchar(32) DEFAULT NULL,
  `repetition` varchar(10) DEFAULT NULL,
  `identification` varchar(10) DEFAULT NULL,
  `nb_max` int(11) DEFAULT 10,
  `temps_max` time DEFAULT '00:10:00',
  `archived` tinyint(1) NOT NULL DEFAULT 0,
  `etat` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `activites`
--

INSERT INTO `activites` (`id`, `nom`, `organisateur`, `password`, `date_activite`, `vue`, `repetition`, `identification`, `nb_max`, `temps_max`, `archived`, `etat`) VALUES
(6, 'Chronométrage_RFID', 'Gérard ', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', '2024-11-21 12:14:00', 'course_passages_limites.php', 'passages', 'rfid', 10, '00:00:50', 1, 1),
(7, 'chronométrage_ID', 'Hervé Lucas', '2abd55e001c524cb2cf6300a89ca6366848a77d5', '2024-10-18 11:30:00', 'score.php', 'essais', 'materiel', 10, '00:00:20', 0, 0),
(9, 'toto', 'sans_nom', '', '2024-10-29 19:44:00', 'course_temps_limite.php', 'essais', 'materiel', NULL, NULL, 0, 0),
(10, 'super', 'genial', '', '1970-01-01 00:00:00', 'course_temps_limite.php', 'essais', 'materiel', NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `datas`
--

CREATE TABLE `datas` (
  `id` int(11) NOT NULL,
  `id_participant` int(11) NOT NULL,
  `id_activite` int(11) NOT NULL,
  `data` float DEFAULT NULL,
  `temps` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `datas`
--

INSERT INTO `datas` (`id`, `id_participant`, `id_activite`, `data`, `temps`) VALUES
(40, 121, 6, NULL, '2024-11-15 16:25:53'),
(41, 125, 6, NULL, '2024-11-15 16:27:03'),
(42, 124, 6, NULL, '2024-11-15 16:27:08'),
(43, 123, 6, NULL, '2024-11-15 16:27:21'),
(44, 122, 6, NULL, '2024-11-15 16:27:27'),
(45, 121, 6, NULL, '2024-11-15 16:27:32'),
(46, 126, 6, NULL, '2024-11-15 16:27:43'),
(47, 120, 6, NULL, '2024-11-15 16:27:47'),
(48, 127, 6, NULL, '2024-11-15 16:27:58'),
(49, 127, 6, NULL, '2024-11-15 16:28:03'),
(50, 120, 6, NULL, '2024-11-15 16:28:28'),
(51, 126, 6, NULL, '2024-11-15 16:28:35'),
(52, 121, 6, NULL, '2024-11-15 16:28:53'),
(53, 122, 6, NULL, '2024-11-15 16:29:01'),
(54, 123, 6, NULL, '2024-11-15 16:29:08'),
(55, 124, 6, NULL, '2024-11-15 16:29:28'),
(56, 125, 6, NULL, '2024-11-15 16:29:35'),
(57, 127, 6, NULL, '2024-11-15 16:29:41'),
(58, 126, 6, NULL, '2024-11-15 16:29:50'),
(59, 120, 6, NULL, '2024-11-15 16:29:54'),
(60, 125, 6, NULL, '2024-11-15 16:30:00'),
(61, 124, 6, NULL, '2024-11-15 16:30:11'),
(62, 123, 6, NULL, '2024-11-15 16:30:20'),
(63, 122, 6, NULL, '2024-11-15 16:30:24'),
(64, 121, 6, NULL, '2024-11-15 16:30:29'),
(65, 127, 6, NULL, '2024-11-15 16:30:35'),
(66, 126, 6, NULL, '2024-11-15 16:30:40'),
(67, 120, 6, NULL, '2024-11-15 16:30:44'),
(68, 121, 6, NULL, '2024-11-15 16:30:50'),
(69, 122, 6, NULL, '2024-11-15 16:30:54'),
(70, 123, 6, NULL, '2024-11-15 16:31:00'),
(71, 124, 6, NULL, '2024-11-15 16:31:06'),
(72, 125, 6, NULL, '2024-11-15 16:31:11'),
(73, 126, 6, NULL, '2024-11-15 16:31:18'),
(74, 127, 6, NULL, '2024-11-15 16:31:24'),
(75, 120, 6, NULL, '2024-11-15 16:31:30'),
(76, 125, 6, NULL, '2024-11-15 16:31:35'),
(77, 124, 6, NULL, '2024-11-15 16:31:40'),
(78, 123, 6, NULL, '2024-11-15 16:31:44'),
(79, 122, 6, NULL, '2024-11-15 16:31:49'),
(80, 121, 6, NULL, '2024-11-15 16:31:54'),
(81, 126, 6, NULL, '2024-11-15 16:32:08'),
(82, 127, 6, NULL, '2024-11-15 16:32:11'),
(83, 121, 6, NULL, '2024-11-15 16:32:19'),
(84, 122, 6, NULL, '2024-11-15 16:32:22'),
(85, 123, 6, NULL, '2024-11-15 16:32:25'),
(86, 124, 6, NULL, '2024-11-15 16:32:29'),
(87, 125, 6, NULL, '2024-11-15 16:32:32'),
(88, 126, 6, NULL, '2024-11-15 16:32:39'),
(89, 120, 6, NULL, '2024-11-15 16:32:43'),
(90, 125, 6, NULL, '2024-11-15 16:33:24'),
(91, 124, 6, NULL, '2024-11-15 16:33:28'),
(92, 123, 6, NULL, '2024-11-15 16:33:37'),
(93, 122, 6, NULL, '2024-11-15 16:33:40'),
(94, 121, 6, NULL, '2024-11-15 16:33:43'),
(95, 127, 6, NULL, '2024-11-15 16:33:47'),
(96, 120, 6, NULL, '2024-11-15 16:33:50'),
(97, 126, 6, NULL, '2024-11-15 16:33:53'),
(98, 124, 6, NULL, '2024-11-15 16:34:03'),
(99, 122, 6, NULL, '2024-11-15 16:34:11'),
(100, 121, 6, NULL, '2024-11-15 16:34:14'),
(101, 123, 6, NULL, '2024-11-15 16:34:17'),
(102, 123, 6, NULL, '2024-11-15 16:34:25'),
(103, 121, 6, NULL, '2024-11-15 16:34:28'),
(104, 122, 6, NULL, '2024-11-15 16:34:32'),
(105, 124, 6, NULL, '2024-11-15 16:34:35'),
(106, 125, 6, NULL, '2024-11-15 16:34:38'),
(107, 126, 6, NULL, '2024-11-15 16:34:43'),
(108, 120, 6, NULL, '2024-11-15 16:34:47'),
(109, 127, 6, NULL, '2024-11-15 16:34:50'),
(110, 127, 6, NULL, '2024-11-15 16:34:54'),
(111, 120, 6, NULL, '2024-11-15 16:34:58'),
(112, 126, 6, NULL, '2024-11-15 16:35:02'),
(113, 126, 6, NULL, '2024-11-15 16:35:13'),
(114, 120, 6, NULL, '2024-11-15 16:35:17'),
(115, 127, 6, NULL, '2024-11-15 16:35:20'),
(116, 125, 6, NULL, '2024-11-15 16:35:26'),
(117, 124, 6, NULL, '2024-11-15 16:35:29'),
(118, 124, 6, NULL, '2024-11-15 16:35:33'),
(119, 122, 6, NULL, '2024-11-15 16:35:36'),
(120, 121, 6, NULL, '2024-11-15 16:35:40'),
(121, 123, 6, NULL, '2024-11-15 16:35:53'),
(122, 123, 6, NULL, '2024-11-15 16:39:34'),
(123, 121, 6, NULL, '2024-11-15 16:39:38'),
(124, 124, 6, NULL, '2024-11-15 16:39:42'),
(125, 125, 6, NULL, '2024-11-15 16:39:45'),
(126, 127, 6, NULL, '2024-11-15 16:39:48'),
(127, 120, 6, NULL, '2024-11-15 16:39:54'),
(128, 120, 6, NULL, '2024-11-15 16:40:01'),
(129, 126, 6, NULL, '2024-11-15 16:40:04'),
(130, 127, 6, NULL, '2024-11-15 16:40:07'),
(131, 121, 6, NULL, '2024-11-15 16:40:11'),
(132, 125, 6, NULL, '2024-11-15 16:40:16'),
(133, 124, 6, NULL, '2024-11-15 16:40:20'),
(134, 122, 6, NULL, '2024-11-15 16:40:25'),
(135, 123, 6, NULL, '2024-11-15 16:40:29'),
(136, 122, 6, NULL, '2024-11-15 16:41:21'),
(137, 124, 6, NULL, '2024-11-15 16:41:27'),
(138, 125, 6, NULL, '2024-11-15 16:41:32'),
(139, 121, 6, NULL, '2024-11-15 16:41:38'),
(140, 122, 6, NULL, '2024-11-15 16:41:44'),
(141, 124, 6, NULL, '2024-11-15 16:41:48'),
(142, 126, 6, NULL, '2024-11-15 16:41:54'),
(143, 120, 6, NULL, '2024-11-15 16:41:58'),
(144, 127, 6, NULL, '2024-11-15 16:42:01'),
(145, 122, 6, NULL, '2024-11-18 14:32:33'),
(146, 122, 6, NULL, '2024-11-18 14:33:24'),
(147, 122, 6, NULL, '2024-11-18 14:35:02'),
(148, 120, 6, NULL, '2024-11-18 15:04:47'),
(149, 120, 6, NULL, '2024-11-18 15:04:58'),
(150, 120, 6, NULL, '2024-11-18 15:05:10'),
(151, 120, 6, NULL, '2024-11-18 15:15:23'),
(152, 120, 6, NULL, '2024-11-18 15:18:43'),
(153, 120, 6, NULL, '2024-11-18 15:19:08'),
(211, 148, 7, 4, '2024-11-21 01:00:44'),
(212, 148, 7, 3, '2024-11-21 01:01:13'),
(213, 148, 7, 3, '2024-11-21 01:01:21'),
(214, 148, 7, 3, '2024-11-21 01:01:24'),
(215, 153, 7, 27, '2024-11-21 08:38:17'),
(216, 153, 7, 28, '2024-11-21 08:38:23'),
(217, 153, 7, 28, '2024-11-21 08:38:31'),
(218, 153, 7, 28, '2024-11-21 08:38:34'),
(219, 153, 7, 28, '2024-11-21 08:38:35'),
(220, 153, 7, 25, '2024-11-21 08:53:26'),
(221, 153, 7, 25, '2024-11-21 08:53:32'),
(222, 153, 7, 25, '2024-11-21 08:53:34'),
(223, 153, 7, 25, '2024-11-21 08:53:35'),
(224, 153, 7, 25, '2024-11-21 08:53:36'),
(225, 147, 7, 21, '2024-11-21 09:06:01'),
(226, 147, 7, 22, '2024-11-21 09:07:34'),
(227, 152, 7, 25, '2024-11-21 09:07:37'),
(228, 152, 7, 25, '2024-11-21 09:07:41'),
(229, 147, 7, 23, '2024-11-21 09:07:42'),
(230, 152, 7, 25, '2024-11-21 09:07:43'),
(231, 147, 7, 23, '2024-11-21 09:07:44'),
(232, 152, 7, 25, '2024-11-21 09:07:46'),
(233, 147, 7, 23, '2024-11-21 09:07:47'),
(234, 152, 7, 25, '2024-11-21 09:07:48'),
(235, 152, 7, 25, '2024-11-21 09:07:51'),
(236, 147, 7, 23, '2024-11-21 09:07:52'),
(237, 152, 7, 25, '2024-11-21 09:07:53'),
(238, 147, 7, 23, '2024-11-21 09:07:54'),
(239, 147, 7, 25, '2024-11-21 09:09:33'),
(240, 152, 7, 24, '2024-11-21 09:09:34'),
(241, 147, 7, 25, '2024-11-21 09:09:35'),
(242, 152, 7, 24, '2024-11-21 09:09:35'),
(243, 147, 7, 25, '2024-11-21 09:09:36'),
(244, 152, 7, 24, '2024-11-21 09:09:36'),
(245, 147, 7, 25, '2024-11-21 09:09:37'),
(246, 152, 7, 24, '2024-11-21 09:09:37'),
(247, 147, 7, 25, '2024-11-21 09:09:38'),
(248, 152, 7, 25, '2024-11-21 09:09:41'),
(249, 147, 7, 25, '2024-11-21 09:09:42'),
(250, 152, 7, 25, '2024-11-21 09:09:43'),
(251, 147, 7, 25, '2024-11-21 09:09:43'),
(252, 154, 7, 12.46, '2024-11-21 19:46:25'),
(253, 152, 7, NULL, '2024-12-02 15:33:53'),
(254, 152, 7, 3, '2024-12-02 15:39:12'),
(255, 152, 7, 3, '2024-12-02 15:48:33'),
(256, 152, 7, 3, '2024-12-02 15:49:14'),
(257, 152, 7, 3, '2024-12-02 15:49:50'),
(258, 152, 7, 3, '2024-12-02 15:50:04'),
(259, 152, 7, 3, '2024-12-02 15:50:35'),
(260, 152, 7, 3, '2024-12-02 15:51:15'),
(261, 152, 7, 3, '2024-12-02 15:51:46'),
(262, 152, 7, 3, '2024-12-02 15:52:17'),
(263, 152, 7, 3, '2024-12-02 15:52:48'),
(264, 152, 7, 3, '2024-12-02 15:53:20'),
(265, 152, 7, 3, '2024-12-02 15:53:51'),
(266, 152, 7, 3, '2024-12-02 15:54:22'),
(267, 152, 7, 3, '2024-12-02 15:54:53'),
(268, 152, 7, 3, '2024-12-02 15:55:24'),
(269, 152, 7, 3, '2024-12-02 15:55:55'),
(270, 152, 7, 3, '2024-12-02 15:56:26'),
(271, 152, 7, 3, '2024-12-02 15:56:58'),
(272, 152, 7, 3, '2024-12-02 15:57:29'),
(273, 152, 7, 3, '2024-12-02 15:58:45'),
(274, 152, 7, 3, '2024-12-02 15:59:16'),
(275, 152, 7, 3, '2024-12-02 15:59:47'),
(276, 152, 7, 3, '2024-12-02 16:00:18'),
(277, 152, 7, 3, '2024-12-02 16:00:50'),
(278, 152, 7, 3, '2024-12-02 16:01:21'),
(279, 152, 7, 3, '2024-12-02 16:01:52');

-- --------------------------------------------------------

--
-- Structure de la table `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_activite` int(11) NOT NULL,
  `ref_id` varchar(8) DEFAULT NULL,
  `association` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `participants`
--

INSERT INTO `participants` (`id`, `id_user`, `id_activite`, `ref_id`, `association`) VALUES
(120, 487, 6, '33420E2D', 120),
(121, 488, 6, '93F20D28', 121),
(122, 489, 6, 'B3AA2B28', 122),
(123, 490, 6, '239D0428', 123),
(124, 491, 6, 'B3AE0028', 124),
(125, 492, 6, 'D37DED27', 125),
(126, 493, 6, '2388E113', 126),
(127, 494, 6, '0347DD13', 127),
(128, 495, 6, NULL, 127),
(129, 496, 6, NULL, 124),
(130, 497, 6, NULL, 123),
(131, 498, 6, NULL, 122),
(132, 499, 6, NULL, 121),
(133, 500, 6, NULL, 120),
(134, 501, 6, NULL, 126),
(135, 502, 6, NULL, 125),
(136, 503, 6, NULL, 127),
(137, 504, 6, NULL, 127),
(138, 505, 6, NULL, 126),
(139, 506, 6, NULL, 125),
(140, 507, 6, NULL, 124),
(141, 508, 6, NULL, 123),
(142, 509, 6, NULL, 122),
(143, 510, 6, NULL, 121),
(144, 511, 6, NULL, 120),
(145, 487, 7, '1', 145),
(146, 488, 7, '2', 146),
(147, 489, 7, '6', 147),
(148, 490, 7, '5', 148),
(149, 491, 7, '6', 147),
(150, 492, 7, '5', 150),
(151, 493, 7, '6', 150),
(152, 494, 7, '7', 152),
(153, 495, 7, '10', 153),
(154, 496, 7, '8', 154),
(155, 497, 7, '7', 155),
(156, 498, 7, NULL, 145),
(157, 499, 7, NULL, 145),
(158, 500, 7, NULL, 145),
(159, 501, 7, NULL, 146),
(160, 502, 7, NULL, 146),
(161, 503, 7, NULL, 155),
(162, 504, 7, NULL, 155),
(163, 505, 7, NULL, 153),
(164, 506, 7, NULL, 153),
(165, 507, 7, NULL, 152),
(166, 508, 7, NULL, 154),
(167, 509, 7, NULL, 154),
(168, 510, 7, NULL, 148),
(169, 511, 7, NULL, 148),
(177, 543, 9, NULL, NULL),
(178, 544, 9, NULL, NULL),
(179, 545, 9, NULL, NULL),
(180, 546, 9, NULL, NULL),
(181, 547, 9, NULL, NULL),
(182, 548, 9, NULL, NULL),
(183, 549, 9, NULL, NULL),
(184, 550, 9, NULL, NULL),
(185, 551, 9, NULL, NULL),
(186, 552, 9, NULL, NULL),
(187, 553, 9, NULL, NULL),
(188, 554, 9, NULL, NULL),
(189, 555, 9, NULL, NULL),
(190, 556, 9, NULL, NULL),
(191, 557, 9, NULL, NULL),
(192, 558, 9, NULL, NULL),
(193, 559, 9, NULL, NULL),
(194, 560, 9, NULL, NULL),
(195, 561, 9, NULL, NULL),
(196, 562, 9, NULL, NULL),
(197, 563, 9, NULL, NULL),
(198, 564, 9, NULL, NULL),
(199, 565, 9, NULL, NULL),
(200, 566, 9, NULL, NULL),
(201, 567, 9, NULL, NULL),
(202, 568, 9, NULL, NULL),
(203, 569, 9, NULL, NULL),
(204, 570, 9, NULL, NULL),
(205, 571, 9, NULL, NULL),
(206, 572, 9, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(32) NOT NULL,
  `prenom` varchar(32) NOT NULL,
  `classe` varchar(8) NOT NULL DEFAULT 'n_classé',
  `nais` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `classe`, `nais`) VALUES
(461, 'ALLAIN', 'Agathe', '3A', '17/08/2010'),
(462, 'ALLAIN', 'Axelle', '3A', '17/08/2010'),
(463, 'ANISIS', 'Gabin', '3A', '09/08/2010'),
(464, 'BESRET', 'Matéo', '3A', '06/06/2010'),
(465, 'CAILLY', 'Noé', '3A', '07/10/2010'),
(466, 'CHEVALIER', 'Nolan', '3A', '08/07/2010'),
(467, 'CORREIA CHESNAIS', 'Louis-Marie', '3A', '21/12/2010'),
(468, 'DUCHESNE', 'Thibaud', '3A', '27/10/2010'),
(469, 'DUMAS--JAVAUDIN', 'Johann', '3A', '13/09/2010'),
(470, 'GAULTIER', 'Lilou', '3A', '16/08/2010'),
(471, 'GOUMONT', 'Kaïna', '3A', '27/07/2010'),
(472, 'HARDY', 'Gwendoline', '3A', '12/03/2010'),
(473, 'HARROUARD', 'Lilly', '3A', '11/04/2010'),
(474, 'HUDE', 'Juliette', '3A', '08/07/2010'),
(475, 'LE GAT', 'Maelann', '3A', '01/10/2010'),
(476, 'LE TINEVEZ', 'Kenan', '3A', '14/09/2010'),
(477, 'LECOT', 'Esteban', '3A', '24/06/2010'),
(478, 'LEROY', 'Cindèle', '3A', '28/11/2009'),
(479, 'PELETTE', 'Renan', '3A', '14/06/2010'),
(480, 'REAUD', 'Lou', '3A', '10/02/2010'),
(481, 'RICHARDSON', 'Maël', '3A', '09/11/2010'),
(482, 'RIMBAULT', 'Anthone', '3A', '31/12/2009'),
(483, 'RUEL', 'Soann', '3A', '01/03/2010'),
(484, 'SOULOUMIAC', 'Lenny', '3A', '03/08/2010'),
(485, 'VORIMORE', 'Malo', '3A', '09/06/2010'),
(486, 'VOSGHIEN', 'Tim', '3A', '20/10/2010'),
(487, 'BESANÇON', 'Manon', '3D', '22/02/2010'),
(488, 'BESANÇON', 'Thibault', '3D', '22/02/2010'),
(489, 'BLIE', 'Jules', '3D', '08/04/2010'),
(490, 'COGHE', 'Titouan', '3D', '26/03/2010'),
(491, 'CSERNAK', 'Ethann', '3D', '24/01/2010'),
(492, 'DESLANDE', 'Romaric', '3D', '31/03/2010'),
(493, 'ESTOREZ', 'Evan', '3D', '06/04/2010'),
(494, 'FLAMENT', 'Alexandre', '3D', '31/12/2010'),
(495, 'FOFANA--REVEL', 'Marius', '3D', '05/07/2010'),
(496, 'GOUGUET', 'Gwendal', '3D', '22/09/2010'),
(497, 'GUEGAN', 'Elwyn', '3D', '23/04/2010'),
(498, 'GUYOMARD', 'Louane', '3D', '29/10/2010'),
(499, 'HAMON', 'Maxence', '3D', '22/04/2010'),
(500, 'IDROBO', 'Emilio', '3D', '23/03/2010'),
(501, 'JOUAN--TARCHALA', 'Django', '3D', '04/02/2010'),
(502, 'LE BOLLOCH', 'Noémie', '3D', '27/01/2010'),
(503, 'LELIEVRE', 'Brieuc', '3D', '04/03/2010'),
(504, 'LEMOINE', 'Marine', '3D', '02/04/2010'),
(505, 'LESAGE', 'Jules', '3D', '11/01/2010'),
(506, 'LONCLE', 'Annaig', '3D', '28/05/2010'),
(507, 'MAZE DIT MIEUSEMENT', 'Anaelle', '3D', '27/04/2010'),
(508, 'MORIN', 'Lana', '3D', '08/12/2010'),
(509, 'PERROT', 'Mathéo', '3D', '04/09/2010'),
(510, 'ROULAND', 'Lindsey', '3D', '05/11/2009'),
(511, 'THEBAULT', 'Léna', '3D', '21/12/2010'),
(512, 'BERHAULT', 'Louann', '5A', '10/11/2012'),
(513, 'BESRET', 'Sacha', '5A', '31/05/2012'),
(514, 'BLANCHARD', 'Tïa', '5A', '30/09/2012'),
(515, 'BRIOT', 'Clement', '5A', '26/10/2012'),
(516, 'CARCELLE', 'Maxine', '5A', '25/10/2012'),
(517, 'CHOLET', 'Loris', '5A', '04/12/2012'),
(518, 'CSERNAK', 'Chloe', '5A', '10/09/2012'),
(519, 'DE LA FUENTE', 'Esteban', '5A', '03/08/2012'),
(520, 'DELAUNAY', 'Svetlana', '5A', '12/09/2011'),
(521, 'DESHAIS', 'Leslie', '5A', '14/06/2012'),
(522, 'DUFAIT', 'Julie', '5A', '26/11/2012'),
(523, 'DUMAS', 'Baptiste', '5A', '02/12/2012'),
(524, 'FLAGEUL', 'Diane', '5A', '03/05/2012'),
(525, 'HUCK', 'Gabriel', '5A', '13/12/2011'),
(526, 'IMBERTI', 'Robin', '5A', '05/04/2012'),
(527, 'LABARBE', 'Héloïse', '5A', '02/09/2012'),
(528, 'LATOUCHE', 'Naël', '5A', '03/07/2012'),
(529, 'LAUTE BOUCHER', 'Eeva', '5A', '04/08/2012'),
(530, 'LE COZ', 'Enzo', '5A', '26/12/2012'),
(531, 'LEVAVASSEUR MANDARD', 'Lindsay', '5A', '02/08/2012'),
(532, 'MAILLARD', 'Clothilde', '5A', '24/10/2012'),
(533, 'MEKKAOUI', 'Yacine', '5A', '26/10/2012'),
(534, 'MOREAU', 'Maxine', '5A', '23/04/2012'),
(535, 'MORICE', 'Orlane', '5A', '09/06/2012'),
(536, 'MORVAN', 'Gaby', '5A', '31/10/2012'),
(537, 'MUSU SIROIT', 'Manoé', '5A', '07/04/2012'),
(538, 'PANFILI', 'Nina', '5A', '23/12/2012'),
(539, 'ROUAULT', 'Kélyan', '5A', '09/03/2012'),
(540, 'TANGUY', 'Alice', '5A', '03/01/2012'),
(541, 'VAZOU', 'Terii', '5A', '26/02/2012'),
(542, 'VIOLEAU RULLIERE', 'Erwan', '5A', '09/09/2012'),
(543, 'AYRAULT', 'Zélie', '5B', '10/11/2012'),
(544, 'CHATELET', 'Melen', '5B', '21/10/2012'),
(545, 'COLLET', 'Lou', '5B', '09/07/2012'),
(546, 'DIUZET', 'Romane', '5B', '04/09/2012'),
(547, 'DOUABIN', 'Eva', '5B', '10/09/2012'),
(548, 'GAUTHIER', 'Mael', '5B', '29/07/2012'),
(549, 'GICQUEL HESRY', 'Erwan', '5B', '20/07/2012'),
(550, 'HARDY', 'Soline', '5B', '27/04/2012'),
(551, 'KIKANOI', 'Moana', '5B', '22/02/2012'),
(552, 'LE BELLEC', 'Mahé', '5B', '15/11/2012'),
(553, 'LE ROY ADRIAN', 'Matteo', '5B', '05/01/2012'),
(554, 'LEMOUSSU COQUELIN', 'Katell', '5B', '16/09/2012'),
(555, 'LENOIR', 'Erine', '5B', '14/12/2012'),
(556, 'LEROUX BACHELOT', 'François', '5B', '17/01/2012'),
(557, 'MARC', 'Zoé', '5B', '20/09/2012'),
(558, 'MARTIN DROUARD', 'Jade', '5B', '17/11/2012'),
(559, 'MORVAN', 'Mano', '5B', '31/10/2012'),
(560, 'NADAL', 'Enora', '5B', '22/05/2012'),
(561, 'NHIANOU', 'Tao', '5B', '18/11/2012'),
(562, 'PADE', 'Ethan', '5B', '02/06/2012'),
(563, 'PARMENTIER', 'Théo', '5B', '31/03/2012'),
(564, 'PERRE-REGNIER', 'Elouen', '5B', '30/11/2012'),
(565, 'PERROT', 'Line', '5B', '19/02/2012'),
(566, 'POUSSIN', 'Laura', '5B', '02/01/2012'),
(567, 'PUIG', 'Gaizka', '5B', '10/12/2012'),
(568, 'ROBIOU LEVENE', 'Virgil', '5B', '25/06/2012'),
(569, 'SALMAGNE', 'Anais', '5B', '06/01/2012'),
(570, 'SIFI', 'Adam', '5B', '30/09/2012'),
(571, 'TREHEL', 'Marie', '5B', '21/05/2012'),
(572, 'VIGLIANO--BINSFELD', 'Manoa', '5B', '15/06/2011'),
(573, 'BIDAULT', 'Kaïs', '5C', '26/03/2012'),
(574, 'BOCHER', 'Léo', '5C', '09/10/2012'),
(575, 'BOURGEAULT', 'Arthur', '5C', '07/06/2012'),
(576, 'BRICHE', 'Cassy', '5C', '25/01/2012'),
(577, 'CATHELINE', 'Romy', '5C', '10/06/2012'),
(578, 'CHABAUD CANTIAN', 'Margo', '5C', '03/09/2012'),
(579, 'CHESNEL-MORVAN', 'Manon', '5C', '21/07/2012'),
(580, 'CHOLVY', 'Pauline', '5C', '10/02/2012'),
(581, 'CHON', 'Pauline', '5C', '22/05/2012'),
(582, 'CLAYE', 'Yanis', '5C', '27/09/2012'),
(583, 'CLECH BOURDIER', 'Aloïs', '5C', '28/05/2012'),
(584, 'COLIN', 'Darry', '5C', '20/06/2012'),
(585, 'COUEFFE', 'Léna', '5C', '07/01/2012'),
(586, 'DOS SANTOS LE GAT', 'Aécio', '5C', '03/06/2012'),
(587, 'FAYE', 'Yuna', '5C', '11/10/2012'),
(588, 'FELIN', 'Kiliann', '5C', '29/04/2012'),
(589, 'FERRON', 'Marius', '5C', '05/09/2012'),
(590, 'GRIPON', 'Melvyn', '5C', '16/06/2012'),
(591, 'HUMBERT', 'Jasmine', '5C', '01/07/2012'),
(592, 'HUMBERT-DROZ', 'Laurent', '5C', '06/09/2012'),
(593, 'JOLIVEL', 'Timéo', '5C', '02/12/2012'),
(594, 'JOSSELIN', 'Simon', '5C', '27/11/2012'),
(595, 'LE BOLLOCH', 'Tiphaine', '5C', '03/04/2012'),
(596, 'LEBRETON', 'Lily', '5C', '01/03/2012'),
(597, 'LEGAULT', 'Aline', '5C', '13/04/2012'),
(598, 'LEROY', 'Lucas', '5C', '14/06/2012'),
(599, 'LEUX', 'Louna', '5C', '10/10/2012'),
(600, 'MANDARD', 'Ella', '5C', '19/04/2012'),
(601, 'MENARD', 'Sidwenn', '5C', '24/07/2012'),
(602, 'NODARI', 'Giovani', '5C', '08/10/2012');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `activites`
--
ALTER TABLE `activites`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `datas`
--
ALTER TABLE `datas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_epreuve` (`id_activite`),
  ADD KEY `idx_id_participant` (`id_participant`);

--
-- Index pour la table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_epreuve` (`id_activite`),
  ADD KEY `idx_id_user` (`id_user`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `activites`
--
ALTER TABLE `activites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `datas`
--
ALTER TABLE `datas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=280;

--
-- AUTO_INCREMENT pour la table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=207;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=603;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `datas`
--
ALTER TABLE `datas`
  ADD CONSTRAINT `datas_ibfk_1` FOREIGN KEY (`id_activite`) REFERENCES `activites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `datas_ibfk_2` FOREIGN KEY (`id_participant`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `participants_ibfk_2` FOREIGN KEY (`id_activite`) REFERENCES `activites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
