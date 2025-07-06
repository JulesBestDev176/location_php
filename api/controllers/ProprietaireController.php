<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Proprietaire.php';
require_once __DIR__ . '/../utils/Helper.php';

class ProprietaireController extends BaseController {

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
            SELECT p.*, pr.Ninea, pr.Rccm
            FROM personnes p
            JOIN proprietaires pr ON p.IdPersonne = pr.IdPersonne
            ORDER BY p.Nom, p.Prenom
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $this->sendJsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getById($id) {
        $query = "
            SELECT p.*, pr.Ninea, pr.Rccm
            FROM personnes p
            JOIN proprietaires pr ON p.IdPersonne = pr.IdPersonne
            WHERE p.IdPersonne = :id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $proprietaire = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($proprietaire) {
            $this->sendJsonResponse($proprietaire);
        } else {
            $this->sendJsonResponse(['erreur' => 'Propriétaire non trouvé'], 404);
        }
    }

    private function create() {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->Nom) || empty($data->Prenom) || empty($data->Telephone) || empty($data->Email) || empty($data->Ninea) || empty($data->Rccm)) {
            $this->sendJsonResponse(['erreur' => 'Données incomplètes pour la création du propriétaire.'], 400);
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

            $stmtProprietaire = $this->db->prepare("INSERT INTO proprietaires (IdPersonne, Ninea, Rccm) VALUES (:IdPersonne, :Ninea, :Rccm)");
            $stmtProprietaire->bindValue(':IdPersonne', $idPersonne, PDO::PARAM_INT);
            $stmtProprietaire->bindValue(':Ninea', htmlspecialchars(strip_tags($data->Ninea)));
            $stmtProprietaire->bindValue(':Rccm', htmlspecialchars(strip_tags($data->Rccm)));
            $stmtProprietaire->execute();

            $this->db->commit();
            $this->sendJsonResponse(['message' => 'Propriétaire créé avec succès.', 'id' => $idPersonne], 201);

        } catch (Exception $e) {
            $this->db->rollBack();
            Helper::WriteDataError('CreateProprietaire', $e->getMessage());
            $this->sendJsonResponse(['erreur' => "Erreur lors de la création du propriétaire: " . $e->getMessage()], 500);
        }
    }

    private function delete($id) {
        $this->db->beginTransaction();
        try {
            
            $this->db->exec("DELETE FROM proprietaires WHERE IdPersonne = $id");
            $stmt = $this->db->prepare("DELETE FROM personnes WHERE IdPersonne = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                 $this->sendJsonResponse(['erreur' => 'Propriétaire non trouvé.'], 404);
                 $this->db->rollBack();
                 return;
            }

            $this->db->commit();
            $this->sendJsonResponse(['message' => 'Propriétaire supprimé avec succès.']);

        } catch (Exception $e) {
            $this->db->rollBack();
            Helper::WriteDataError('DeleteProprietaire', $e->getMessage());
            $this->sendJsonResponse(['erreur' => "Erreur lors de la suppression du propriétaire. Il peut être lié à des appartements existants."], 500);
        }
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?> 