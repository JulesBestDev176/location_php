<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../utils/Helper.php';

class LocationController extends BaseController {

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
        $query = "SELECT * FROM locations ORDER BY DateCreation DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $this->sendJsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getById($id) {
        $query = "SELECT * FROM locations WHERE IdLocation = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $location = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($location) {
            $this->sendJsonResponse($location);
        } else {
            $this->sendJsonResponse(['erreur' => 'Location non trouvée'], 404);
        }
    }

    private function create() {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->NumeroLocation) || empty($data->MontantLocation) || empty($data->DateDebut) || empty($data->IdAppartement) || empty($data->IdLocataire)) {
            $this->sendJsonResponse(['erreur' => 'Données incomplètes pour créer la location.'], 400);
            return;
        }

        $this->db->beginTransaction();
        try {
            $stmtAppart = $this->db->prepare("SELECT Disponiblite, nbrLocataire FROM appartements WHERE IdAppartement = :id");
            $stmtAppart->bindParam(':id', $data->IdAppartement);
            $stmtAppart->execute();
            $appartement = $stmtAppart->fetch(PDO::FETCH_ASSOC);

            if (!$appartement || !$appartement['Disponiblite']) {
                $this->sendJsonResponse(['erreur' => "L'appartement n'est pas disponible."], 409); // 409 Conflict
                $this->db->rollBack();
                return;
            }

            $query = "INSERT INTO locations (NumeroLocation, MontantLocation, DateDebut, DateFin, DateCreation, Statut, IdAppartement, IdLocataire) VALUES (:Num, :Montant, :Debut, :Fin, NOW(), :Statut, :IdApp, :IdLoc)";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindValue(':Num', htmlspecialchars(strip_tags($data->NumeroLocation)));
            $stmt->bindValue(':Montant', (int)$data->MontantLocation);
            $stmt->bindValue(':Debut', $data->DateDebut);
            $stmt->bindValue(':Fin', !empty($data->DateFin) ? $data->DateFin : null);
            $stmt->bindValue(':Statut', isset($data->Statut) ? (bool)$data->Statut : true, PDO::PARAM_BOOL);
            $stmt->bindValue(':IdApp', (int)$data->IdAppartement);
            $stmt->bindValue(':IdLoc', (int)$data->IdLocataire);
            $stmt->execute();
            $idLocation = $this->db->lastInsertId();

            $newNbrLocataire = $appartement['nbrLocataire'] + 1;
            $stmtUpdate = $this->db->prepare("UPDATE appartements SET Disponiblite = 0, nbrLocataire = :nbr WHERE IdAppartement = :id");
            $stmtUpdate->bindParam(':nbr', $newNbrLocataire, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':id', $data->IdAppartement, PDO::PARAM_INT);
            $stmtUpdate->execute();

            $this->db->commit();
            $this->sendJsonResponse(['message' => 'Location créée avec succès.', 'id' => $idLocation], 201);

        } catch (Exception $e) {
            $this->db->rollBack();
            Helper::WriteDataError('CreateLocation', $e->getMessage());
            $this->sendJsonResponse(['erreur' => "Erreur lors de la création de la location: " . $e->getMessage()], 500);
        }
    }

    private function delete($id) {
       
        $this->db->beginTransaction();
        try {
            $location = $this->getLocationForUpdate($id);

            $stmt = $this->db->prepare("DELETE FROM locations WHERE IdLocation = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0 && $location) {
                if ($location['Statut'] == 1) {
                    $this->db->exec("UPDATE appartements SET Disponiblite = 1, nbrLocataire = GREATEST(0, nbrLocataire - 1) WHERE IdAppartement = {$location['IdAppartement']}");
                }
            } else {
                 $this->sendJsonResponse(['erreur' => 'Location non trouvée.'], 404);
                 $this->db->rollBack();
                 return;
            }

            $this->db->commit();
            $this->sendJsonResponse(['message' => 'Location supprimée avec succès.']);

        } catch (Exception $e) {
            $this->db->rollBack();
            Helper::WriteDataError('DeleteLocation', $e->getMessage());
            $this->sendJsonResponse(['erreur' => "Erreur lors de la suppression de la location."], 500);
        }
    }

    private function getLocationForUpdate($id) {
        $stmt = $this->db->prepare("SELECT IdAppartement, Statut FROM locations WHERE IdLocation = :id FOR UPDATE");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?> 