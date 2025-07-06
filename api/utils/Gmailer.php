<?php
require_once __DIR__ . '/Helper.php';

class GMailer
{
    /**
     * NOTE IMPORTANTE:
     * L'envoi d'e-mails via un serveur SMTP externe comme Gmail de manière fiable en PHP
     * nécessite une bibliothèque spécialisée (ex: PHPMailer, Symfony Mailer).
     * La fonction mail() native de PHP utilisée ici est simple mais souvent insuffisante
     * car elle ne gère pas l'authentification SMTP requise par Gmail et dépend de la
     * configuration du serveur d'hébergement.
     *
     * Pour une utilisation en production, il est fortement conseillé de remplacer
     * cette implémentation par une bibliothèque plus robuste.
     */

    public string $toEmail;
    public string $subject;
    public string $body;
    public bool $isHtml = true;

    /**
     * Tente d'envoyer un e-mail en utilisant la fonction mail() de PHP.
     */
    public function send(): void
    {
        $gmailUsername = $_ENV['GMAIL_USERNAME'] ?? null;
        if (!$gmailUsername) {
             throw new Exception("L'identifiant GMAIL_USERNAME n'est pas configuré dans le fichier .env");
        }

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: "Votre Application" <' . $gmailUsername . '>' . "\r\n";
        $headers .= 'Reply-To: ' . $gmailUsername . "\r\n";

        try {
            if (!mail($this->toEmail, $this->subject, $this->body, $headers)) {
                throw new Exception("La fonction mail() a échoué. Ceci est probablement dû à la configuration du serveur et à l'absence d'authentification SMTP.");
            }
        } catch (Exception $ex) {
            throw new Exception("Erreur lors de la tentative d'envoi du mail: " . $ex->getMessage());
        }
    }

    /**
     * Méthode d'assistance statique pour un envoi rapide.
     * @param string $destinataire
     * @param string $subject
     * @param string $body
     */
    public static function sendMail(string $destinataire, string $subject, string $body): void
    {
        // NOTE: La logique d'authentification SMTP complexe serait ici.
        // Puisque nous utilisons la simple fonction mail(), la configuration
        // (comme les identifiants) est gérée au niveau du serveur ou de la fonction mail() elle-même,
        // et non passée en paramètre comme pour une bibliothèque SMTP.
        // GMAIL_PASSWORD n'est donc pas utilisé dans cette implémentation simpliste.
        try {
            $mailer = new GMailer();
            $mailer->toEmail = $destinataire;
            $mailer->subject = $subject;
            $mailer->body = $body;
            $mailer->isHtml = true; // Par défaut, les mails sont en HTML
            $mailer->send();
        } catch (Exception $ex) {
            Helper::WriteLogSystem($ex->getMessage(), "GMailer-sendMail");
        }
    }
}
?> 