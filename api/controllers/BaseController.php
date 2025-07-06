<?php

/**
 * Classe de base abstraite pour tous les contrôleurs de l'application.
 * Elle fournit une connexion à la base de données et la méthode HTTP utilisée.
 */
abstract class BaseController {
    protected $db;
    protected $requestMethod;

    /**
     * Constructeur.
     * @param PDO $db Handle de connexion à la base de données.
     */
    public function __construct($db) {
        $this->db = $db;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Méthode abstraite que chaque contrôleur enfant devra implémenter.
     * Elle est responsable du traitement de la requête HTTP.
     * @param int|null $id L'identifiant de la ressource, s'il est présent dans l'URL.
     */
    abstract public function handleRequest(?string $param): void;
} 