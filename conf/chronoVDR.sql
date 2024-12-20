-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : jeu. 07 nov. 2024 à 14:14
-- Version du serveur : 10.11.6-MariaDB-0+deb12u1
-- Version de PHP : 8.2.24

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
  `date_activite` datetime NOT NULL DEFAULT current_timestamp(),
  `repetition` varchar(10) DEFAULT NULL,
  `identification` varchar(10) DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0,
  `start` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `activites`
--

INSERT INTO `activites` (`id`, `nom`, `organisateur`, `date_activite`, `repetition`, `identification`, `archived`, `start`) VALUES
(6, 'activité 1', 'Gérard LEON', '2024-10-21 06:30:00', 'essais', 'rfid', 1, 1),
(7, 'activité2', 'Hervé Lucas', '2024-10-18 11:30:00', 'essais', 'materiel', 0, 0),
(9, 'toto', 'sans_nom', '2024-10-29 19:44:00', 'essais', 'materiel', 0, 0),
(10, 'super', 'genial', '1970-01-01 00:00:00', 'essais', 'materiel', 0, 0);

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
(1, 69, 6, NULL, '2024-07-11 14:01:18'),
(2, 81, 6, NULL, '2024-07-11 14:01:50'),
(3, 87, 6, NULL, '2024-07-11 14:01:59'),
(4, 81, 6, NULL, '2024-07-11 14:03:17'),
(5, 80, 6, NULL, '2024-07-11 14:11:32');

-- --------------------------------------------------------

--
-- Structure de la table `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_activite` int(11) NOT NULL,
  `dossard` varchar(4) DEFAULT NULL,
  `RFID` varchar(8) DEFAULT NULL,
  `association` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `participants`
--

INSERT INTO `participants` (`id`, `id_user`, `id_activite`, `dossard`, `RFID`, `association`) VALUES
(69, 461, 6, NULL, '239D0428', 69),
(70, 462, 6, NULL, NULL, 69),
(71, 463, 6, NULL, '239D0428', 71),
(72, 464, 6, NULL, NULL, 71),
(73, 465, 6, NULL, NULL, 71),
(74, 466, 6, NULL, '239D0428', 74),
(75, 467, 6, NULL, NULL, 74),
(76, 468, 6, NULL, NULL, 74),
(77, 469, 6, NULL, NULL, 74),
(78, 470, 6, NULL, '239D0428', NULL),
(79, 471, 6, NULL, '239D0428', NULL),
(80, 472, 6, NULL, 'D37DED27', NULL),
(81, 473, 6, NULL, 'B3AE0028', NULL),
(82, 474, 6, NULL, 'D37DED27', NULL),
(83, 475, 6, NULL, '239D0428', NULL),
(84, 476, 6, NULL, '239D0428', NULL),
(85, 477, 6, NULL, '239D0428', NULL),
(86, 478, 6, NULL, '239D0428', NULL),
(87, 479, 6, NULL, '43DE3028', NULL),
(88, 480, 6, NULL, NULL, NULL),
(89, 481, 6, NULL, '43DE3028', NULL),
(90, 482, 6, NULL, NULL, NULL),
(91, 483, 6, NULL, '43DE3028', NULL),
(92, 484, 6, NULL, 'B3AE0028', NULL),
(93, 485, 6, NULL, NULL, NULL),
(94, 486, 6, NULL, NULL, NULL),
(95, 487, 6, NULL, 'B3AE0028', NULL),
(96, 488, 6, NULL, 'B3AE0028', NULL),
(97, 489, 6, NULL, 'B3AE0028', NULL),
(98, 490, 6, NULL, NULL, NULL),
(99, 491, 6, NULL, NULL, NULL),
(100, 492, 6, NULL, NULL, NULL),
(101, 493, 6, NULL, NULL, NULL),
(102, 494, 6, NULL, NULL, NULL),
(103, 495, 6, NULL, '43DE3028', NULL),
(104, 496, 6, NULL, NULL, NULL),
(105, 497, 6, NULL, NULL, NULL),
(106, 498, 6, NULL, NULL, NULL),
(107, 499, 6, NULL, NULL, NULL),
(108, 500, 6, NULL, NULL, NULL),
(109, 501, 6, NULL, NULL, NULL),
(110, 502, 6, NULL, NULL, NULL),
(111, 503, 6, NULL, '43DE3028', NULL),
(112, 504, 6, NULL, NULL, NULL),
(113, 505, 6, NULL, NULL, NULL),
(114, 506, 6, NULL, NULL, NULL),
(115, 507, 6, NULL, NULL, NULL),
(116, 508, 6, NULL, NULL, NULL),
(117, 509, 6, NULL, '43DE3028', NULL),
(118, 510, 6, NULL, NULL, NULL),
(119, 511, 6, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(32) NOT NULL,
  `prenom` varchar(32) NOT NULL,
  `classe` varchar(8) NOT NULL,
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
(542, 'VIOLEAU RULLIERE', 'Erwan', '5A', '09/09/2012');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=543;

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
