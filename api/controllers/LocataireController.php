<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Locataire.php';
require_once __DIR__ . '/../utils/Helper.php';

class LocataireController extends BaseController {

    public function handleRequest(?string $param): void {
        if ($param === 'sans_location' && $this->requestMethod === 'GET') {
            $this->getSansLocationEnCours();
            return;
        }

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
            SELECT p.*, l.CNI
            FROM personnes p
            JOIN locataires l ON p.IdPersonne = l.IdPersonne
            ORDER BY p.Nom, p.Prenom
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $this->sendJsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getById($id) {
        $query = "
            SELECT p.*, l.CNI
            FROM personnes p
            JOIN locataires l ON p.IdPersonne = l.IdPersonne
            WHERE p.IdPersonne = :id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $locataire = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($locataire) {
            $this->sendJsonResponse($locataire);
        } else {
            $this->sendJsonResponse(['erreur' => 'Locataire non trouvé'], 404);
        }
    }

    /**
     * Récupère les locataires qui n'ont pas de location avec Statut = 1 (en cours).
     */
    private function getSansLocationEnCours() {
        $query = "
            SELECT p.*, l.CNI
            FROM personnes p
            JOIN locataires l ON p.IdPersonne = l.IdPersonne
            WHERE NOT EXISTS (
                SELECT 1 FROM locations loc WHERE loc.IdLocataire = l.IdPersonne AND loc.Statut = 1
            )
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $this->sendJsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function create() {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->Nom) || empty($data->Prenom) || empty($data->Telephone) || empty($data->Email) || empty($data->CNI)) {
            $this->sendJsonResponse(['erreur' => 'Données incomplètes pour la création du locataire.'], 400);
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

            $stmtLocataire = $this->db->prepare("INSERT INTO locataires (IdPersonne, CNI) VALUES (:IdPersonne, :CNI)");
            $stmtLocataire->bindValue(':IdPersonne', $idPersonne, PDO::PARAM_INT);
            $stmtLocataire->bindValue(':CNI', htmlspecialchars(strip_tags($data->CNI)));
            $stmtLocataire->execute();

            $this->db->commit();
            $this->sendJsonResponse(['message' => 'Locataire créé avec succès.', 'id' => $idPersonne], 201);

        } catch (Exception $e) {
            $this->db->rollBack();
            Helper::WriteDataError('CreateLocataire', $e->getMessage());
            $this->sendJsonResponse(['erreur' => "Erreur lors de la création du locataire: " . $e->getMessage()], 500);
        }
    }

    private function delete($id) {
        $this->db->beginTransaction();
        try {
            
            $this->db->exec("DELETE FROM locataires WHERE IdPersonne = $id");
            $stmt = $this->db->prepare("DELETE FROM personnes WHERE IdPersonne = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                 $this->sendJsonResponse(['erreur' => 'Locataire non trouvé.'], 404);
                 $this->db->rollBack();
                 return;
            }

            $this->db->commit();
            $this->sendJsonResponse(['message' => 'Locataire supprimé avec succès.']);

        } catch (Exception $e) {
            $this->db->rollBack();
            Helper::WriteDataError('DeleteLocataire', $e->getMessage());
            $this->sendJsonResponse(['erreur' => "Erreur lors de la suppression du locataire. Il peut être lié à des locations existantes."], 500);
        }
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?> 