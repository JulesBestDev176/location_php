<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ModePaiement.php';
require_once __DIR__ . '/../utils/Helper.php';

class ModePaiementController extends BaseController {

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
        $query = "SELECT * FROM modepaiements ORDER BY LibelleModePaiement";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $this->sendJsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getById($id) {
        $query = "SELECT * FROM modepaiements WHERE IdModePaiement = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $mode = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($mode) {
            $this->sendJsonResponse($mode);
        } else {
            $this->sendJsonResponse(['erreur' => 'Mode de paiement non trouvé'], 404);
        }
    }

    private function create() {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->LibelleModePaiement)) {
            $this->sendJsonResponse(['erreur' => 'Le libellé est requis.'], 400);
            return;
        }

        $query = "INSERT INTO modepaiements (LibelleModePaiement) VALUES (:libelle)";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':libelle', htmlspecialchars(strip_tags($data->LibelleModePaiement)));

        if ($stmt->execute()) {
            $this->sendJsonResponse(['message' => 'Mode de paiement créé.', 'id' => $this->db->lastInsertId()], 201);
        } else {
            $this->sendJsonResponse(['erreur' => 'Erreur lors de la création.'], 500);
        }
    }

    private function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM modepaiements WHERE IdModePaiement = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $this->sendJsonResponse(['message' => 'Mode de paiement supprimé.']);
        } else {
            $this->sendJsonResponse(['erreur' => 'Mode de paiement non trouvé.'], 404);
        }
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?> 