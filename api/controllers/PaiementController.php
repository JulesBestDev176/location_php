<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Paiement.php';
require_once __DIR__ . '/../utils/Helper.php';

class PaiementController extends BaseController {

    public function handleRequest(?string $param): void {
        $id = is_numeric($param) ? (int)$param : null;

        switch ($this->requestMethod) {
            case 'GET':
                if ($id) {
                    $this->getById($id);
                } else {
                    if (isset($_GET['location_id'])) {
                         $this->getByLocationId((int)$_GET['location_id']);
                    } else {
                        $this->getAll();
                    }
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
        $query = "SELECT * FROM paiements ORDER BY DatePaiement DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $this->sendJsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getById($id) {
        $query = "SELECT * FROM paiements WHERE IdPaiement = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($paiement) {
            $this->sendJsonResponse($paiement);
        } else {
            $this->sendJsonResponse(['erreur' => 'Paiement non trouvé'], 404);
        }
    }

    private function getByLocationId($locationId) {
        $query = "SELECT * FROM paiements WHERE IdLocation = :locationId ORDER BY DatePaiement DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':locationId', $locationId);
        $stmt->execute();
        $this->sendJsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    private function create() {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->MontantPaiement) || empty($data->NumeroFacture) || !isset($data->Statut) || empty($data->IdLocation) || empty($data->IdModePaiement)) {
            $this->sendJsonResponse(['erreur' => 'Données incomplètes pour créer le paiement.'], 400);
            return;
        }

        $query = "INSERT INTO paiements (DatePaiement, MontantPaiement, NumeroFacture, Statut, IdLocation, IdModePaiement) VALUES (NOW(), :montant, :facture, :statut, :idloc, :idmode)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindValue(':montant', (int)$data->MontantPaiement);
        $stmt->bindValue(':facture', htmlspecialchars(strip_tags($data->NumeroFacture)));
        $stmt->bindValue(':statut', (bool)$data->Statut, PDO::PARAM_BOOL);
        $stmt->bindValue(':idloc', (int)$data->IdLocation);
        $stmt->bindValue(':idmode', (int)$data->IdModePaiement);

        if ($stmt->execute()) {
            $this->sendJsonResponse(['message' => 'Paiement créé avec succès.', 'id' => $this->db->lastInsertId()], 201);
        } else {
            Helper::WriteDataError('CreatePaiement', 'Erreur SQL lors de l\'insertion');
            $this->sendJsonResponse(['erreur' => "Erreur lors de la création du paiement."], 500);
        }
    }

    private function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM paiements WHERE IdPaiement = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $this->sendJsonResponse(['message' => 'Paiement supprimé avec succès.']);
        } else {
            $this->sendJsonResponse(['erreur' => 'Paiement non trouvé.'], 404);
        }
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?> 