-- Script d'insertion de l'administrateur par défaut.
-- A exécuter APRES 01_schema.sql.
-- Ce script ne fait rien si un utilisateur avec le profil 'Administrateur' existe déjà.

START TRANSACTION;

-- On définit des variables pour ne pas avoir à les répéter
SET @admin_email = 'admin@gmail.com';
SET @admin_identifiant = 'Admin';
SET @admin_nom = 'Administrateur';
SET @admin_prenom = 'Administrateur';
SET @admin_tel = '+221765432345';
SET @admin_profil = 'Administrateur';
-- Le mot de passe est 'Admin123', haché en MD5
SET @admin_mdp_hash = 'e64b78fc3bc91bcbc7dc232ba8ec59e0'; 
SET @admin_statut = 'Actif';

-- Insertion de la personne et de l'utilisateur uniquement si aucun admin n'existe
INSERT INTO personnes (Nom, Prenom, Telephone, Email)
SELECT @admin_nom, @admin_prenom, @admin_tel, @admin_email
WHERE NOT EXISTS (SELECT 1 FROM utilisateurs WHERE profil = @admin_profil);

INSERT INTO utilisateurs (IdPersonne, Identifiant, MotDePasse, profil, Statut)
SELECT LAST_INSERT_ID(), @admin_identifiant, @admin_mdp_hash, @admin_profil, @admin_statut
WHERE NOT EXISTS (SELECT 1 FROM (SELECT * FROM utilisateurs) as u WHERE profil = @admin_profil);


COMMIT; 