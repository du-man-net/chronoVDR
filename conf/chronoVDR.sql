-- phpMyAdmin SQL Dump
-- version 5.2.2deb1+deb13u1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : ven. 12 juin 2026 à 20:31
-- Version du serveur : 11.8.6-MariaDB-0+deb13u1 from Debian
-- Version de PHP : 8.4.16

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
  `id_admin` int(11) DEFAULT NULL,
  `flag` int(10) DEFAULT NULL,
  `vue` varchar(32) DEFAULT NULL,
  `nb_max` int(11) DEFAULT NULL,
  `temps_max` int(11) DEFAULT NULL,
  `delais_min` int(11) NOT NULL DEFAULT 0,
  `etat` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `login` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`id`, `nom`, `login`, `password`, `create_at`) VALUES
(1, 'Admin ChronoVDR', 'chronovdr', '', '2026-05-28 16:14:08');

-- --------------------------------------------------------

--
-- Structure de la table `balises`
--

CREATE TABLE `balises` (
  `id` int(11) NOT NULL,
  `tag` varchar(8) NOT NULL,
  `nom` varchar(8) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `co`
--

CREATE TABLE `co` (
  `id` int(11) NOT NULL,
  `id_activite` int(11) NOT NULL,
  `id_parcours` int(11) DEFAULT NULL,
  `id_participant` int(11) NOT NULL,
  `t_start` datetime DEFAULT NULL,
  `t_end` datetime DEFAULT NULL,
  `etat` int(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `co_datas`
--

CREATE TABLE `co_datas` (
  `id` int(11) NOT NULL,
  `id_co` int(11) NOT NULL,
  `temps` datetime NOT NULL,
  `tag` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `datas`
--

CREATE TABLE `datas` (
  `id` int(11) NOT NULL,
  `id_participant` int(11) NOT NULL,
  `data` float DEFAULT NULL,
  `temps` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `liste_balises`
--

CREATE TABLE `liste_balises` (
  `id` int(11) NOT NULL,
  `id_balise` int(11) NOT NULL,
  `id_parcours` int(11) NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `nom` varchar(20) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 1,
  `value` int(8) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `parcours`
--

CREATE TABLE `parcours` (
  `id` int(11) NOT NULL,
  `nom` varchar(20) DEFAULT NULL,
  `ordre` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_activite` int(11) NOT NULL,
  `ref_id` varchar(8) DEFAULT '',
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
  `classe` varchar(15) NOT NULL DEFAULT 'n_classé',
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

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `activites`
--
ALTER TABLE `activites`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Index pour la table `balises`
--
ALTER TABLE `balises`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `co`
--
ALTER TABLE `co`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_activite` (`id_parcours`),
  ADD KEY `id_participant` (`id_participant`),
  ADD KEY `id_activite_2` (`id_activite`);

--
-- Index pour la table `co_datas`
--
ALTER TABLE `co_datas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_co` (`id_co`);

--
-- Index pour la table `datas`
--
ALTER TABLE `datas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_participant` (`id_participant`);

--
-- Index pour la table `liste_balises`
--
ALTER TABLE `liste_balises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_balise` (`id_balise`),
  ADD KEY `id_parcours` (`id_parcours`);

--
-- Index pour la table `parcours`
--
ALTER TABLE `parcours`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `balises`
--
ALTER TABLE `balises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `co`
--
ALTER TABLE `co`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `co_datas`
--
ALTER TABLE `co_datas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `datas`
--
ALTER TABLE `datas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `liste_balises`
--
ALTER TABLE `liste_balises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `parcours`
--
ALTER TABLE `parcours`
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

-- --------------------------------------------------------

--
-- Structure de la vue `v_rec_participants`
--
DROP TABLE IF EXISTS `v_rec_participants`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_rec_participants`  AS SELECT `activites`.`nb_max` AS `nb_max`, `participants`.`id` AS `idp`, `participants`.`ref_id` AS `ref_id`, (select count(`datas`.`id`) from (`datas` join `participants`) where `datas`.`id_participant` = `idp` group by `datas`.`id_participant`) AS `nb_datas` FROM (`activites` join `participants`) WHERE `activites`.`etat` = '2' AND `activites`.`id` = `participants`.`id_activite` ;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `co`
--
ALTER TABLE `co`
  ADD CONSTRAINT `co_ibfk_3` FOREIGN KEY (`id_participant`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `co_ibfk_4` FOREIGN KEY (`id_activite`) REFERENCES `activites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `co_datas`
--
ALTER TABLE `co_datas`
  ADD CONSTRAINT `co_datas_ibfk_2` FOREIGN KEY (`id_co`) REFERENCES `co` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `datas`
--
ALTER TABLE `datas`
  ADD CONSTRAINT `datas_ibfk_2` FOREIGN KEY (`id_participant`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `liste_balises`
--
ALTER TABLE `liste_balises`
  ADD CONSTRAINT `liste_balises_ibfk_1` FOREIGN KEY (`id_balise`) REFERENCES `balises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `liste_balises_ibfk_2` FOREIGN KEY (`id_parcours`) REFERENCES `parcours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
