<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../utils/Helper.php';

class UtilisateurController extends BaseController {

    public function handleRequest(?string $param): void {
        if ($param === 'count' && $this->requestMethod === 'GET') {
            $this->getNumberUtilisateur();
            return;
        }

        $id = is_numeric($param) ? (int)$param : null;

        switch ($this->requestMethod) {
            case 'GET':
                if ($id) {
                    $this->getById($id);
                } elseif (isset($_GET['email'])) {
                    $this->getByEmail($_GET['email']);
                } elseif (isset($_GET['identifiant'])) {
                    $this->getByIdentifiant($_GET['identifiant']);
                } elseif (isset($_GET['exact_identifiant'])) {
                    $this->getUserByIdentifiant($_GET['exact_identifiant']);
                } elseif (isset($_GET['profil']) && $_GET['profil'] === 'admin') {
                    $this->getAdmin();
                } else {
                    $this->getAll();
                }
                break;
            case 'POST':
                $this->create();
                break;
            default:
                $this->sendJsonResponse(['erreur' => 'Méthode non autorisée'], 405);
                break;
        }
    }

    private function getAll() {
        $query = "
            SELECT p.*, u.Identifiant, u.profil, u.Statut 
            FROM personnes p 
            JOIN utilisateurs u ON p.IdPersonne = u.IdPersonne
            ORDER BY p.Nom, p.Prenom
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $this->sendJsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getById($id) {
        $query = "SELECT p.*, u.Identifiant, u.profil, u.Statut FROM personnes p JOIN utilisateurs u ON p.IdPersonne = u.IdPersonne WHERE p.IdPersonne = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $this->sendJsonResponse($user);
        } else {
            $this->sendJsonResponse(['erreur' => 'Utilisateur non trouvé'], 404);
        }
    }

    private function getByEmail($email) {
        $query = "SELECT p.*, u.Identifiant, u.profil, u.Statut FROM personnes p JOIN utilisateurs u ON p.IdPersonne = u.IdPersonne WHERE p.Email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $this->sendJsonResponse($user);
        } else {
            $this->sendJsonResponse(['erreur' => 'Utilisateur non trouvé pour cet email'], 404);
        }
    }

    private function getByIdentifiant($identifiant) {
        $query = "SELECT p.*, u.Identifiant, u.profil, u.Statut FROM personnes p JOIN utilisateurs u ON p.IdPersonne = u.IdPersonne WHERE u.Identifiant LIKE :identifiant";
        $stmt = $this->db->prepare($query);
        $searchTerm = '%' . $identifiant . '%';
        $stmt->bindParam(':identifiant', $searchTerm);
        $stmt->execute();
        $this->sendJsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getUserByIdentifiant($identifiant) {
        $query = "SELECT p.*, u.Identifiant, u.profil, u.Statut FROM personnes p JOIN utilisateurs u ON p.IdPersonne = u.IdPersonne WHERE u.Identifiant = :identifiant";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':identifiant', $identifiant);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $this->sendJsonResponse($user);
        } else {
            $this->sendJsonResponse(['erreur' => 'Utilisateur non trouvé'], 404);
        }
    }

    private function getNumberUtilisateur() {
        $query = "SELECT COUNT(IdPersonne) as count FROM utilisateurs";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->sendJsonResponse($result);
    }

    private function getAdmin() {
        $query = "SELECT p.*, u.Identifiant, u.profil, u.Statut FROM personnes p JOIN utilisateurs u ON p.IdPersonne = u.IdPersonne WHERE u.profil = 'Administrateur' LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            $this->sendJsonResponse($admin);
        } else {
            $this->sendJsonResponse(['erreur' => 'Administrateur non trouvé'], 404);
        }
    }

    private function create() {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->Nom) || empty($data->Prenom) || empty($data->Telephone) || empty($data->Email) || empty($data->Identifiant) || empty($data->MotDePasse)) {
            $this->sendJsonResponse(['erreur' => 'Données incomplètes.'], 400);
            return;
        }

        if (!Helper::ValidatePassword($data->MotDePasse)) {
             $this->sendJsonResponse(['erreur' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.'], 400);
            return;
        }
        
        $this->db->beginTransaction();

        try {
            $queryPersonne = "INSERT INTO personnes (Nom, Prenom, Telephone, Email) VALUES (:Nom, :Prenom, :Telephone, :Email)";
            $stmtPersonne = $this->db->prepare($queryPersonne);
            $stmtPersonne->bindValue(':Nom', htmlspecialchars(strip_tags($data->Nom)));
            $stmtPersonne->bindValue(':Prenom', htmlspecialchars(strip_tags($data->Prenom)));
            $stmtPersonne->bindValue(':Telephone', htmlspecialchars(strip_tags($data->Telephone)));
            $stmtPersonne->bindValue(':Email', htmlspecialchars(strip_tags($data->Email)));
            $stmtPersonne->execute();
            $idPersonne = $this->db->lastInsertId();

            $hashedPassword = Helper::GetMd5Hash($data->MotDePasse);
            
            $queryUtilisateur = "INSERT INTO utilisateurs (IdPersonne, Identifiant, MotDePasse, profil, Statut) VALUES (:IdPersonne, :Identifiant, :MotDePasse, :profil, :Statut)";
            $stmtUtilisateur = $this->db->prepare($queryUtilisateur);
            $stmtUtilisateur->bindValue(':IdPersonne', $idPersonne, PDO::PARAM_INT);
            $stmtUtilisateur->bindValue(':Identifiant', htmlspecialchars(strip_tags($data->Identifiant)));
            $stmtUtilisateur->bindValue(':MotDePasse', $hashedPassword);
            $stmtUtilisateur->bindValue(':profil', !empty($data->profil) ? htmlspecialchars(strip_tags($data->profil)) : 'Utilisateur');
            $stmtUtilisateur->bindValue(':Statut', !empty($data->Statut) ? htmlspecialchars(strip_tags($data->Statut)) : 'Actif');
            $stmtUtilisateur->execute();

            $this->db->commit();
            $this->sendJsonResponse(['message' => 'Utilisateur créé avec succès.', 'id' => $idPersonne], 201);

        } catch (Exception $e) {
            $this->db->rollBack();
            Helper::WriteDataError('CreateUtilisateur', $e->getMessage());
            $this->sendJsonResponse(['erreur' => "Erreur lors de la création de l'utilisateur: " . $e->getMessage()], 500);
        }
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
        exit;
    }
}
?> 