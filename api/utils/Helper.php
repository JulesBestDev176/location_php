<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/Td_Erreur.php';

class Helper {

    // --- Fonctions de CryptApp ---

    public static function GetMd5Hash(string $input): string {
        return md5($input);
    }

    public static function VerifyMd5Hash(string $input, string $hash): bool {
        $hashOfInput = self::GetMd5Hash($input);
        // Utilise hash_equals pour une comparaison sécurisée 
        return hash_equals($hashOfInput, $hash);
    }

    public static function ValidatePassword(string $password): bool {
        if (strlen($password) < 8) {
            return false;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        return true;
    }

    // --- Fonctions de Helper ---

    /**
     * Enregistre une erreur dans la table td_erreurs de la base de données.
     * @param string $TitreErreur Le titre de l'erreur.
     * @param string $erreur La description de l'erreur.
     */
    public static function WriteDataError(string $TitreErreur, string $erreur): void {
        try {
            $database = new Database();
            $db = $database->connect();

            $query = 'INSERT INTO td_erreurs (DateErreur, DescriptionErreur, TitreErreur) VALUES (:DateErreur, :DescriptionErreur, :TitreErreur)';
            
            $stmt = $db->prepare($query);

            $dateErreur = date('Y-m-d H:i:s');
            $description = strlen($erreur) > 2000 ? substr($erreur, 0, 2000) : $erreur;

            $stmt->bindParam(':DateErreur', $dateErreur);
            $stmt->bindParam(':DescriptionErreur', $description);
            $stmt->bindParam(':TitreErreur', $TitreErreur);

            $stmt->execute();

        } catch (Exception $e) {
            self::WriteLogSystem($e->getMessage(), "Helper::WriteDataError - Echec BDD");
        }
    }

    /**
     * Enregistre un message dans le log système de PHP (ex: error.log d'Apache).
     * @param string $erreur La description de l'erreur.
     * @param string $libelle Le contexte/libellé de l'erreur.
     */
    public static function WriteLogSystem(string $erreur, string $libelle): void {
        $logMessage = sprintf("Date: %s, Libelle: %s, Description: %s", date('Y-m-d H:i:s'), $libelle, $erreur);
        error_log($logMessage);
    }
}
?> 