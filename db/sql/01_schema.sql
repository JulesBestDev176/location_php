-- Script de création de la structure de la base de données `bdlocation`
-- A exécuter en premier.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de données : `bdlocation`
--

-- --------------------------------------------------------

--
-- Structure de la table `personnes`
--
CREATE TABLE IF NOT EXISTS `personnes` (
    `IdPersonne` INT AUTO_INCREMENT PRIMARY KEY,
    `Nom` VARCHAR(50) NOT NULL,
    `Prenom` VARCHAR(80) NOT NULL,
    `Telephone` VARCHAR(20) NOT NULL UNIQUE,
    `Email` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--
CREATE TABLE IF NOT EXISTS `utilisateurs` (
    `IdPersonne` INT PRIMARY KEY,
    `Identifiant` VARCHAR(50) NOT NULL UNIQUE,
    `MotDePasse` VARCHAR(255) NOT NULL,
    `profil` VARCHAR(50) NOT NULL,
    `Statut` VARCHAR(50) NOT NULL,
    FOREIGN KEY (`IdPersonne`) REFERENCES `personnes`(`IdPersonne`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `gestionnaires`
--
CREATE TABLE IF NOT EXISTS `gestionnaires` (
    `IdPersonne` INT PRIMARY KEY,
    `Ninea` VARCHAR(50) NOT NULL,
    `Rccm` VARCHAR(50) NOT NULL,
    FOREIGN KEY (`IdPersonne`) REFERENCES `personnes`(`IdPersonne`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `proprietaires`
--
CREATE TABLE IF NOT EXISTS `proprietaires` (
    `IdPersonne` INT PRIMARY KEY,
    `Ninea` VARCHAR(50) NOT NULL,
    `Rccm` VARCHAR(50) NOT NULL,
    FOREIGN KEY (`IdPersonne`) REFERENCES `personnes`(`IdPersonne`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `locataires`
--
CREATE TABLE IF NOT EXISTS `locataires` (
    `IdPersonne` INT PRIMARY KEY,
    `CNI` VARCHAR(50) NOT NULL UNIQUE,
    FOREIGN KEY (`IdPersonne`) REFERENCES `personnes`(`IdPersonne`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `appartements`
--
CREATE TABLE IF NOT EXISTS `appartements` (
    `IdAppartement` INT AUTO_INCREMENT PRIMARY KEY,
    `AdresseAppartement` VARCHAR(255) NOT NULL,
    `Surface` FLOAT,
    `NombrePiece` INT,
    `Capacite` INT NOT NULL,
    `Disponiblite` BOOLEAN NOT NULL DEFAULT TRUE,
    `nbrLocataire` INT NOT NULL DEFAULT 0,
    `IdProprietaire` INT,
    FOREIGN KEY (`IdProprietaire`) REFERENCES `proprietaires`(`IdPersonne`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `locations`
--
CREATE TABLE IF NOT EXISTS `locations` (
    `IdLocation` INT AUTO_INCREMENT PRIMARY KEY,
    `NumeroLocation` VARCHAR(50) NOT NULL UNIQUE,
    `MontantLocation` INT NOT NULL,
    `DateDebut` DATE NOT NULL,
    `DateFin` DATE,
    `DateCreation` DATETIME NOT NULL,
    `Statut` BOOLEAN NOT NULL DEFAULT TRUE,
    `IdAppartement` INT,
    `IdLocataire` INT,
    FOREIGN KEY (`IdAppartement`) REFERENCES `appartements`(`IdAppartement`) ON DELETE CASCADE,
    FOREIGN KEY (`IdLocataire`) REFERENCES `locataires`(`IdPersonne`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `modepaiements`
--
CREATE TABLE IF NOT EXISTS `modepaiements` (
    `IdModePaiement` INT AUTO_INCREMENT PRIMARY KEY,
    `LibelleModePaiement` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--
CREATE TABLE IF NOT EXISTS `paiements` (
    `IdPaiement` INT AUTO_INCREMENT PRIMARY KEY,
    `DatePaiement` DATETIME,
    `MontantPaiement` INT NOT NULL,
    `NumeroFacture` VARCHAR(50) NOT NULL UNIQUE,
    `Statut` BOOLEAN NOT NULL,
    `IdLocation` INT,
    `IdModePaiement` INT,
    FOREIGN KEY (`IdLocation`) REFERENCES `locations`(`IdLocation`) ON DELETE SET NULL,
    FOREIGN KEY (`IdModePaiement`) REFERENCES `modepaiements`(`IdModePaiement`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `td_erreurs`
--
CREATE TABLE IF NOT EXISTS `td_erreurs` (
    `Id` INT AUTO_INCREMENT PRIMARY KEY,
    `DateErreur` DATETIME NOT NULL,
    `DescriptionErreur` TEXT,
    `TitreErreur` VARCHAR(200)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


COMMIT; 