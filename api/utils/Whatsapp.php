<?php
require_once __DIR__ . '/Helper.php';

class Whatsapp
{
    /**
     * Envoie un message WhatsApp en utilisant l'API Wassenger via cURL.
     *
     * @param string $phoneNumber Le numéro de téléphone du destinataire (format international).
     * @param string $message Le contenu du message à envoyer.
     * @return bool Retourne true si la requête a été envoyée avec succès (réponse HTTP 2xx), sinon false.
     */
    public static function sendWhatsappMessage(string $phoneNumber, string $message): bool
    {
        try {
            $apiUrl = $_ENV['WHATSAPP_API_URL'] ?? null;
            $token = $_ENV['WHATSAPP_TOKEN'] ?? null;
            $apiKey = $_ENV['WHATSAPP_API_KEY'] ?? null;

            if (!$apiUrl || !$token || !$apiKey) {
                throw new Exception("Les clés d'API WhatsApp ne sont pas configurées dans le fichier .env");
            }

            $fullApiUrl = $apiUrl . '?key1=' . $apiKey;

            $requestBody = json_encode([
                'phone' => $phoneNumber,
                'message' => $message
            ]);

            $ch = curl_init($fullApiUrl);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_POST, true);           
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, [          
                'Content-Type: application/json',
                'Token: ' . $token,
                'Content-Length: ' . strlen($requestBody)
            ]);

            $response = curl_exec($ch);
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                throw new Exception('Erreur cURL lors de la requête: ' . curl_error($ch));
            }

            curl_close($ch);

            if ($httpStatusCode < 200 || $httpStatusCode >= 300) {
                 throw new Exception("L'API Wassenger a répondu avec un code d'erreur HTTP $httpStatusCode. Réponse: $response");
            }
            
            return true;

        } catch (Exception $ex) {
            Helper::WriteLogSystem($ex->getMessage(), "Whatsapp-SendWhatsappMessage");
            return false;
        }
    }
}
?> 