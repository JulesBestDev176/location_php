<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Appartement.php';

class AppartementController extends BaseController {

    
    public function handleRequest(?string $param): void {
        $id = is_numeric($param) ? (int)$param : null;

        switch ($this->requestMethod) {
            case 'GET':
                if ($id) {
                    $this->getById($id);
                } else {
                    if (isset($_GET['disponible']) && filter_var($_GET['disponible'], FILTER_VALIDATE_BOOLEAN)) {
                        $this->getDisponibles();
                    } else {
                        $this->getAll();
                    }
                }
                break;
            case 'POST':
                $this->create();
                break;
            case 'PUT':
                if ($id) {
                    $this->update($id);
                } else {
                    $this->sendJsonResponse(['erreur' => "ID manquant pour la mise à jour."], 400);
                }
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

    /**
     * Récupère tous les appartements.
     * GET /appartement
     */
    private function getAll() {
        $query = "SELECT IdAppartement, AdresseAppartement, Surface, NombrePiece, Capacite, Disponiblite, nbrLocataire, IdProprietaire FROM appartements ORDER BY IdAppartement DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $appartements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sendJsonResponse($appartements);
    }

    /**
     * Récupère uniquement les appartements disponibles.
     * GET /appartement?disponible=true
     */
    private function getDisponibles() {
        $query = "SELECT IdAppartement, AdresseAppartement, Surface, NombrePiece, Capacite, Disponiblite, nbrLocataire, IdProprietaire FROM appartements WHERE Disponiblite = 1 ORDER BY IdAppartement DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $appartements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sendJsonResponse($appartements);
    }

    /**
     * Récupère un appartement par son ID.
     * GET /appartement/{id}
     */
    private function getById($id) {
        $query = "SELECT IdAppartement, AdresseAppartement, Surface, NombrePiece, Capacite, Disponiblite, nbrLocataire, IdProprietaire FROM appartements WHERE IdAppartement = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $appartement = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($appartement) {
            $this->sendJsonResponse($appartement);
        } else {
            $this->sendJsonResponse(['erreur' => 'Appartement non trouvé'], 404);
        }
    }

    /**
     * Crée un nouvel appartement.
     * POST /appartement
     */
    private function create() {
        $data = json_decode(file_get_contents("php://input"));

        
        if (empty($data->AdresseAppartement) || !isset($data->Capacite) || empty($data->IdProprietaire)) {
            $this->sendJsonResponse(['erreur' => 'Données incomplètes. Adresse, capacité et ID propriétaire sont requis.'], 400);
            return;
        }

        $query = "INSERT INTO appartements (AdresseAppartement, Surface, NombrePiece, Capacite, Disponiblite, nbrLocataire, IdProprietaire) VALUES (:Adresse, :Surface, :Pieces, :Capacite, :Dispo, :Locataires, :IdProprio)";
        $stmt = $this->db->prepare($query);

        
        $stmt->bindValue(':Adresse', htmlspecialchars(strip_tags($data->AdresseAppartement)));
        $stmt->bindValue(':Surface', !empty($data->Surface) ? (float)$data->Surface : null, PDO::PARAM_STR); // PDO traite mieux NULL avec STR/INT que des types float dédiés
        $stmt->bindValue(':Pieces', !empty($data->NombrePiece) ? (int)$data->NombrePiece : null, PDO::PARAM_INT);
        $stmt->bindValue(':Capacite', (int)$data->Capacite, PDO::PARAM_INT);
        $stmt->bindValue(':Dispo', isset($data->Disponiblite) ? (bool)$data->Disponiblite : true, PDO::PARAM_BOOL);
        $stmt->bindValue(':Locataires', isset($data->nbrLocataire) ? (int)$data->nbrLocataire : 0, PDO::PARAM_INT);
        $stmt->bindValue(':IdProprio', (int)$data->IdProprietaire, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->sendJsonResponse(['message' => 'Appartement créé avec succès.', 'id' => $this->db->lastInsertId()], 201);
        } else {
            $this->sendJsonResponse(['erreur' => "Erreur lors de la création de l'appartement."], 500);
        }
    }

    /**
     * Met à jour un appartement existant.
     * PUT /appartement/{id}
     */
    private function update($id) {
        $checkStmt = $this->db->prepare("SELECT IdAppartement FROM appartements WHERE IdAppartement = :id");
        $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();
        if ($checkStmt->fetch(PDO::FETCH_ASSOC) === false) {
            $this->sendJsonResponse(['erreur' => 'Appartement non trouvé.'], 404);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->AdresseAppartement) || !isset($data->Capacite) || empty($data->IdProprietaire)) {
            $this->sendJsonResponse(['erreur' => 'Données incomplètes. Adresse, capacité et ID propriétaire sont requis.'], 400);
            return;
        }

        $query = "UPDATE appartements SET 
                    AdresseAppartement = :Adresse, 
                    Surface = :Surface, 
                    NombrePiece = :Pieces, 
                    Capacite = :Capacite, 
                    Disponiblite = :Dispo, 
                    nbrLocataire = :Locataires, 
                    IdProprietaire = :IdProprio
                  WHERE IdAppartement = :id";
        
        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':Adresse', htmlspecialchars(strip_tags($data->AdresseAppartement)));
        $stmt->bindValue(':Surface', !empty($data->Surface) ? (float)$data->Surface : null, PDO::PARAM_STR);
        $stmt->bindValue(':Pieces', !empty($data->NombrePiece) ? (int)$data->NombrePiece : null, PDO::PARAM_INT);
        $stmt->bindValue(':Capacite', (int)$data->Capacite, PDO::PARAM_INT);
        $stmt->bindValue(':Dispo', isset($data->Disponiblite) ? (bool)$data->Disponiblite : true, PDO::PARAM_BOOL);
        $stmt->bindValue(':Locataires', isset($data->nbrLocataire) ? (int)$data->nbrLocataire : 0, PDO::PARAM_INT);
        $stmt->bindValue(':IdProprio', (int)$data->IdProprietaire, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->sendJsonResponse(['message' => 'Appartement mis à jour avec succès.']);
        } else {
            $this->sendJsonResponse(['erreur' => "Erreur lors de la mise à jour de l'appartement."], 500);
        }
    }

    /**
     * Supprime un appartement.
     * DELETE /appartement/{id}
     */
    private function delete($id) {
        $checkStmt = $this->db->prepare("SELECT IdAppartement FROM appartements WHERE IdAppartement = :id");
        $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();
        if ($checkStmt->fetch(PDO::FETCH_ASSOC) === false) {
            $this->sendJsonResponse(['erreur' => 'Appartement non trouvé.'], 404);
            return;
        }

        $query = "DELETE FROM appartements WHERE IdAppartement = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->sendJsonResponse(['message' => 'Appartement supprimé avec succès.']);
        } else {
            $this->sendJsonResponse(['erreur' => "Erreur lors de la suppression de l'appartement."], 500);
        }
    }


    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit; 
    }
}
?> 