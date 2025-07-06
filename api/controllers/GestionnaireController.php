<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Gestionnaire.php';
require_once __DIR__ . '/../utils/Helper.php';

class GestionnaireController extends BaseController {

    public function handleRequest(?string $param): void {
        $id = is_numeric($param) ? (int)$param : null;

        switch ($this->requestMethod) {
            case 'GET':
                if ($id) {
                    $this->getById($id);
                } else {
                    $this->getAll();
                }
                break;
            case 'POST':
                $this->create();
                break;
            case 'DELETE':
                if ($id) {
                    $this->delete($id);
                } else {
                    $this->sendJsonResponse(['erreur' => "ID manquant pour la suppression."], 400);
                }
                break;
            default:
                $this->sendJsonResponse(['erreur' => 'Méthode non autorisée'], 405);
                break;
        }
    }

    private function getAll() {
        $query = "
            SELECT p.*, u.Identifiant, u.profil, u.Statut, g.Ninea, g.Rccm
            FROM personnes p
            JOIN utilisateurs u ON p.IdPersonne = u.IdPersonne
            JOIN gestionnaires g ON p.IdPersonne = g.IdPersonne
            WHERE u.profil = 'Gestionnaire'
            ORDER BY p.Nom, p.Prenom
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $this->sendJsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getById($id) {
        $query = "
            SELECT p.*, u.Identifiant, u.profil, u.Statut, g.Ninea, g.Rccm
            FROM personnes p
            JOIN utilisateurs u ON p.IdPersonne = u.IdPersonne
            JOIN gestionnaires g ON p.IdPersonne = g.IdPersonne
            WHERE p.IdPersonne = :id AND u.profil = 'Gestionnaire'
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $gestionnaire = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($gestionnaire) {
            $this->sendJsonResponse($gestionnaire);
        } else {
            $this->sendJsonResponse(['erreur' => 'Gestionnaire non trouvé'], 404);
        }
    }

    private function create() {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->Nom) || empty($data->Prenom) || empty($data->Telephone) || empty($data->Email) || empty($data->Identifiant) || empty($data->MotDePasse) || empty($data->Ninea) || empty($data->Rccm)) {
            $this->sendJsonResponse(['erreur' => 'Données incomplètes pour la création du gestionnaire.'], 400);
            return;
        }

        if (!Helper::ValidatePassword($data->MotDePasse)) {
             $this->sendJsonResponse(['erreur' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.'], 400);
            return;
        }
        
        $this->db->beginTransaction();

        try {
            $stmtPersonne = $this->db->prepare("INSERT INTO personnes (Nom, Prenom, Telephone, Email) VALUES (:Nom, :Prenom, :Telephone, :Email)");
            $stmtPersonne->bindValue(':Nom', htmlspecialchars(strip_tags($data->Nom)));
            $stmtPersonne->bindValue(':Prenom', htmlspecialchars(strip_tags($data->Prenom)));
            $stmtPersonne->bindValue(':Telephone', htmlspecialchars(strip_tags($data->Telephone)));
            $stmtPersonne->bindValue(':Email', htmlspecialchars(strip_tags($data->Email)));
            $stmtPersonne->execute();
            $idPersonne = $this->db->lastInsertId();

            $hashedPassword = Helper::GetMd5Hash($data->MotDePasse);
            $stmtUtilisateur = $this->db->prepare("INSERT INTO utilisateurs (IdPersonne, Identifiant, MotDePasse, profil, Statut) VALUES (:IdPersonne, :Identifiant, :MotDePasse, 'Gestionnaire', 'Actif')");
            $stmtUtilisateur->bindValue(':IdPersonne', $idPersonne, PDO::PARAM_INT);
            $stmtUtilisateur->bindValue(':Identifiant', htmlspecialchars(strip_tags($data->Identifiant)));
            $stmtUtilisateur->bindValue(':MotDePasse', $hashedPassword);
            $stmtUtilisateur->execute();

            $stmtGestionnaire = $this->db->prepare("INSERT INTO gestionnaires (IdPersonne, Ninea, Rccm) VALUES (:IdPersonne, :Ninea, :Rccm)");
            $stmtGestionnaire->bindValue(':IdPersonne', $idPersonne, PDO::PARAM_INT);
            $stmtGestionnaire->bindValue(':Ninea', htmlspecialchars(strip_tags($data->Ninea)));
            $stmtGestionnaire->bindValue(':Rccm', htmlspecialchars(strip_tags($data->Rccm)));
            $stmtGestionnaire->execute();

            $this->db->commit();
            $this->sendJsonResponse(['message' => 'Gestionnaire créé avec succès.', 'id' => $idPersonne], 201);

        } catch (Exception $e) {
            $this->db->rollBack();
            Helper::WriteDataError('CreateGestionnaire', $e->getMessage());
            $this->sendJsonResponse(['erreur' => "Erreur lors de la création du gestionnaire: " . $e->getMessage()], 500);
        }
    }

    private function delete($id) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM gestionnaires WHERE IdPersonne = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $stmt = $this->db->prepare("DELETE FROM utilisateurs WHERE IdPersonne = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM personnes WHERE IdPersonne = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                 $this->sendJsonResponse(['erreur' => 'Gestionnaire non trouvé.'], 404);
                 $this->db->rollBack();
                 return;
            }

            $this->db->commit();
            $this->sendJsonResponse(['message' => 'Gestionnaire supprimé avec succès.']);

        } catch (Exception $e) {
            $this->db->rollBack();
            Helper::WriteDataError('DeleteGestionnaire', $e->getMessage());
            $this->sendJsonResponse(['erreur' => "Erreur lors de la suppression du gestionnaire."], 500);
        }
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?> 