-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  Dim 06 juil. 2025 à 19:01
-- Version du serveur :  10.1.28-MariaDB
-- Version de PHP :  7.1.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `banque`
--

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

CREATE TABLE `client` (
  `idClient` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(250) NOT NULL,
  `photoPasseport` varchar(255) DEFAULT NULL,
  `copieCarteIdentite` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`idClient`, `nom`, `prenom`, `adresse`, `telephone`, `email`, `password`, `photoPasseport`, `copieCarteIdentite`) VALUES
(8, 'Iratukunda', 'Justine', 'Buterere', '65343096', 'chalij@gmail.com', '$2y$10$TYaqKyuWs7Ve4BzwahYljejpuwbYsi2MA2at9CPIiD34ro8iwdG9G', '../photos/BingWallpaper.jpg', '../photos/OIP.jpeg'),
(9, 'iratutunga', 'christine', 'kigobe', '72334565', 'iratukunda@gmail.com', '$2y$10$RHRFpDft0ddeNK6xLgOrceuIjkayKP2p8l1vXLgMcKbiBZSlaVtR6', '../photos/BingWallpaper (2).jpg', '../photos/status_me_status_IMG-20230206-WA0153.jpg'),
(13, 'timiza', 'bijoux ', 'bwiza', '62345462', 'timiza@gmail.com', 'Pass1234', '../photos/chris photo.jpg', '../photos/IMG-20240222-WA0005.jpg'),
(15, 'michael', 'chali', 'jabe', '6948574', 'michael@gmail.com', '$2y$10$2oTmVM6aQ2WLdw33tdXyFenVdIa5KX8wWqRB7VEmHGkCjI/blgVaa', '../photos/WorldChat.png', '../photos/Africa Chat.png'),
(17, 'Joël', 'piano', 'kamenge', '69227132', 'joel@gmail.com', '', '../photos/logo.jpg', '../photos/secur1.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `compte`
--

CREATE TABLE `compte` (
  `idCompte` int(11) NOT NULL,
  `numeroCompte` varchar(20) DEFAULT NULL,
  `typeCompte` enum('Courant','Épargne') DEFAULT NULL,
  `solde` decimal(15,2) DEFAULT '0.00',
  `dateCreation` date DEFAULT NULL,
  `idClient` int(11) DEFAULT NULL,
  `statut` varchar(20) NOT NULL DEFAULT 'en_attente',
  `motif_rejet` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `compte`
--

INSERT INTO `compte` (`idCompte`, `numeroCompte`, `typeCompte`, `solde`, `dateCreation`, `idClient`, `statut`, `motif_rejet`) VALUES
(7, '6534', 'Courant', '3850000.00', '2025-01-14', 8, 'actif', NULL),
(8, '6960', 'Courant', '4000000.00', '2025-01-15', 9, 'actif', ''),
(15, '67654', 'Épargne', '1800000.00', '2025-05-16', 15, 'actif', NULL),
(16, '002035', 'Courant', '300000.00', '2025-06-13', 17, 'actif', NULL),
(17, '004315', 'Épargne', '90000000.00', '2025-06-27', 17, 'en_attente', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `employe`
--

CREATE TABLE `employe` (
  `id_employe` int(11) NOT NULL,
  `matricule` varchar(20) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `role` enum('admin','agent') NOT NULL DEFAULT 'agent',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dernier_connexion` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `employe`
--

INSERT INTO `employe` (`id_employe`, `matricule`, `nom`, `prenom`, `email`, `telephone`, `password`, `photo`, `role`, `date_creation`, `dernier_connexion`, `remember_token`, `actif`) VALUES
(1, 'ADM001', 'Joël', 'admin', 'admin@banque.com', '65822223', '$2y$10$VawH8Y8A3kmv/w1YTPMe7.9InIysgeS0cmNfnfrOOAE0YOX5XBaLG', '../photos/BingWallpaper (2).jpg', 'admin', '2025-06-11 23:26:57', NULL, NULL, 1),
(2, 'AGT001', 'Agent', 'Joël', 'agent@banque.com', '65822223', '$2y$10$5eoBNB2QtP8Fi5pWxTcdNOo.UzvWCeSsP3ADQhFGdbdMrUGe2MBtq', '../photos/BingWallpaper (2).jpg', 'agent', '2025-06-11 23:26:57', NULL, NULL, 1),
(3, 'ADM0019', 'karume', 'Kojocampany', 'chalij68@gmail.com', '69227132', '$2y$10$HbIR3vPz/9CDAIonSZ39cetIKbRsJosDlmtK0MVtayr/s9tCqmAj6', '685e89c8ac536_cadena.jpg', 'admin', '2025-06-27 14:07:07', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `operation`
--

CREATE TABLE `operation` (
  `idOperation` int(11) NOT NULL,
  `typeOperation` enum('Retrait','Depot','Virement') DEFAULT NULL,
  `montant` decimal(15,2) DEFAULT NULL,
  `dateOperation` datetime DEFAULT CURRENT_TIMESTAMP,
  `idCompteDebiteur` int(11) DEFAULT NULL,
  `idCompteCrediteur` int(11) DEFAULT NULL,
  `motif` text NOT NULL,
  `is_read` tinyint(4) NOT NULL,
  `id_employe` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `operation`
--

INSERT INTO `operation` (`idOperation`, `typeOperation`, `montant`, `dateOperation`, `idCompteDebiteur`, `idCompteCrediteur`, `motif`, `is_read`, `id_employe`) VALUES
(8, 'Retrait', '500000.00', '2025-01-16 15:58:59', 7, NULL, '', 0, NULL),
(14, 'Virement', '100000.00', '2025-06-12 09:34:08', 7, 15, 'paiement loyer du mois de juillet 2025', 1, NULL),
(15, 'Virement', '500000.00', '2025-06-12 09:39:18', 7, 7, 'remboursé le dettes payer', 1, 2),
(16, 'Virement', '550000.00', '2025-06-13 01:40:06', 7, 15, 'paiement', 1, 2),
(17, 'Virement', '50000.00', '2025-06-13 02:10:05', 15, 7, 'argent de poche', 0, 2),
(18, 'Virement', '50000.00', '2025-06-13 02:11:05', 15, 7, 'argent de poche', 0, 2),
(19, 'Virement', '200000.00', '2025-06-13 23:45:46', 7, 15, 'PYL', 0, 2);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`idClient`);

--
-- Index pour la table `compte`
--
ALTER TABLE `compte`
  ADD PRIMARY KEY (`idCompte`),
  ADD UNIQUE KEY `numeroCompte` (`numeroCompte`),
  ADD KEY `idClient` (`idClient`);

--
-- Index pour la table `employe`
--
ALTER TABLE `employe`
  ADD PRIMARY KEY (`id_employe`),
  ADD UNIQUE KEY `matricule` (`matricule`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `operation`
--
ALTER TABLE `operation`
  ADD PRIMARY KEY (`idOperation`),
  ADD KEY `idCompteDebiteur` (`idCompteDebiteur`),
  ADD KEY `idCompteCrediteur` (`idCompteCrediteur`),
  ADD KEY `fk_operation_employer` (`id_employe`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `client`
--
ALTER TABLE `client`
  MODIFY `idClient` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `compte`
--
ALTER TABLE `compte`
  MODIFY `idCompte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `employe`
--
ALTER TABLE `employe`
  MODIFY `id_employe` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `operation`
--
ALTER TABLE `operation`
  MODIFY `idOperation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `compte`
--
ALTER TABLE `compte`
  ADD CONSTRAINT `compte_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`idClient`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `operation`
--
ALTER TABLE `operation`
  ADD CONSTRAINT `fk_operation_employer` FOREIGN KEY (`id_employe`) REFERENCES `employe` (`id_employe`) ON DELETE SET NULL,
  ADD CONSTRAINT `operation_ibfk_1` FOREIGN KEY (`idCompteDebiteur`) REFERENCES `compte` (`idCompte`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `operation_ibfk_2` FOREIGN KEY (`idCompteCrediteur`) REFERENCES `compte` (`idCompte`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
