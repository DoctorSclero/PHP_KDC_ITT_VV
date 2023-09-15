<?php
session_start(); // Inizializzo la sessione. (Necessario per accedere alla variabile $_SESSION)

require_once __DIR__ . '/utils/database.php';
require_once __DIR__ . '/utils/crypto.php';
require_once __DIR__ . '/utils/nonce.php';

$ERROR = null; // Variabile per gestire la visualizzazione degli errori

// Recupero i dati dalla richiesta
$sender["id"] = $_SESSION["user_id"] ?? null;
$recipient["id"] = $_GET["with"] ?? null;

// Verifico che l'utente sia loggato
if (empty($sender["id"])) {
    // L'utente non è loggato
    // Reindirizzo l'utente alla pagina di login
    header("Location: /login.php");
    exit;
}

// Verifico che l'utente abbia specificato un destinatario
if (empty($recipient["id"])) {
    // L'utente non ha specificato il mittente
    // Reindirizzo l'utente alla dashboard
    header("Location: /dashboard.php");
    exit;
}

// Ottengo l'indirizzo email del destinatario
$recipient["email"] = $connection->query(
    "SELECT email FROM users WHERE id = {$recipient["id"]}"
)->fetch_assoc()["email"];

// Verifico che l'utente abbia una conversazione attiva col destinatario
$conversations = $connection->query(
    "SELECT id, shared_key FROM conversations WHERE
            (from_user = {$sender["id"]} AND to_user = {$recipient["id"]}) OR
            (from_user = {$recipient["id"]} AND to_user = {$sender["id"]})"
);

if ($conversations && $conversations->num_rows == 0) {
    // Non esiste una conversazione tra i due utenti
    // Reindirizzo l'utente alla pagina delle conversazioni
    header("Location: /dashboard.php");
    exit;
}

// Esiste una conversazione tra i due utenti
// Esraggo i dati della conversazione
$conversation = $conversations->fetch_assoc();

// Verifico se l'utente ha inviato un messaggio
if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    // Estrapolo il messagio dalla richiesta
    $sent_nonce = $_POST["nonce"] ?? null;
    $sent_message = $_POST["message"] ?? null;

    // Verifico che il nonce sia valido
    if (!empty($sent_nonce) && Nonce::verify($sent_nonce)) {
        if (!empty($sent_message)) {
            // L'utente ha inviato un messaggio

            // Cripto il messaggio con la chiave condivisa
            $encrypted_message = encryptAES_CTR($conversation["shared_key"], $sent_message);

            // Inserisco il messaggio nel database
            $insert_result = $connection->query(
                "INSERT INTO messages (conversation, datetime, content, sender) VALUES
                    (
                        '{$conversation["id"]}',
                        NOW(),
                        '$encrypted_message',
                        '{$sender["id"]}'
                    )"
            );
        }
    }
}


// Procedo estraendo tutti i messaggi scambiati
$messages = $connection->query(
    "SELECT datetime, content, sender, users.email as sender_email FROM messages
    INNER JOIN users ON users.id = messages.sender
    WHERE conversation = '{$conversation["id"]}'
    ORDER BY datetime DESC"
);

// Per ogni messaggio decifro il contenuto
// e registro il messaggio decifrato nell'indice associativo
// 'messages' dell'array associativo $conversation
while ($message = $messages->fetch_assoc()) {
    $message["content"] = decryptAES_CTR($conversation["shared_key"], $message["content"]);
    $conversation["messages"][] = $message;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITT KDC</title>
</head>

<body>
    <div>
        <?php if ($ERROR == 'conversation_not_found') { ?>
            <h1>Conversazione non trovata</h1>
            <p>Avviala dalla <a href="/dashboard.php">dashboard</a></p>
        <?php } else { ?>
            <h1>Conversazione con <?php echo $recipient["email"]; ?></h1>
            <form method="POST">
                <input type="hidden" name="nonce" value="<?php echo Nonce::generate_and_store(); ?>">
                <input type="hidden" name="conversation" value="<?php echo $conversation["id"]; ?>">
                <input type="text" name="message" placeholder="Scrivi un messaggio">
                <button type="submit">Invia</button>
            </form>
            <!-- Stampo tutti i messaggi decifrati della conversazione -->
            <div>
                <?php if (!empty($conversation["messages"])) { ?>
                    <?php foreach ($conversation["messages"] as $message) { ?>
                        <div>
                            <h3><?php echo $message["sender_email"]; ?></h3>
                            <sub><?php echo $message["datetime"]; ?></sub>
                            <p><?php echo $message["content"]; ?></p>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p>Non ci sono messaggi</p>
                <?php } ?>
            </div>

        <?php } ?>
    </div>
</body>

</html>