-- phpMyAdmin SQL Dump
-- version 5.2.1deb1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : sam. 26 avr. 2025 à 00:55
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
  `flag` int(10) DEFAULT NULL,
  `vue` varchar(32) DEFAULT NULL,
  `nb_max` int(11) DEFAULT NULL,
  `temps_max` int(11) DEFAULT NULL,
  `delais_min` int(11) NOT NULL DEFAULT 0,
  `etat` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `datas`
--

CREATE TABLE `datas` (
  `id` int(11) NOT NULL,
  `id_participant` int(11) NOT NULL,
  `data` float DEFAULT NULL,
  `temps` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(32) NOT NULL,
  `prenom` varchar(32) NOT NULL,
  `classe` varchar(8) NOT NULL DEFAULT 'n_classé',
  `nais` varchar(10) DEFAULT NULL,
  `sexe` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_rec_participants`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_rec_participants` (
`nb_max` int(11)
,`idp` int(11)
,`ref_id` varchar(8)
,`nb_datas` bigint(21)
);

-- --------------------------------------------------------

--
-- Structure de la vue `v_rec_participants`
--
DROP TABLE IF EXISTS `v_rec_participants`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_rec_participants`  AS SELECT `activites`.`nb_max` AS `nb_max`, `participants`.`id` AS `idp`, `participants`.`ref_id` AS `ref_id`, (select count(`datas`.`id`) from (`datas` join `participants`) where `datas`.`id_participant` = `idp` group by `datas`.`id_participant`) AS `nb_datas` FROM (`activites` join `participants`) WHERE `activites`.`etat` = '2' AND `activites`.`id` = `participants`.`id_activite` ;

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
  ADD KEY `idx_id_participant` (`id_participant`);

--
-- Index pour la table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_epreuve` (`id_activite`),
  ADD KEY `idx_id_user` (`id_user`),
  ADD KEY `ref_idx` (`ref_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `datas`
--
ALTER TABLE `datas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `datas`
--
ALTER TABLE `datas`
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
