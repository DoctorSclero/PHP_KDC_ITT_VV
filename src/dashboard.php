<?php 

session_start();

require_once __DIR__ . '/utils/database.php';

$ERROR = null; // Variabile per gestire la visualizzazione degli errori

// Controllo se l'utente non è loggato
if (!isset($_SESSION['user_email'])) {
    // L'utente non è loggato, lo rimando alla home page
    header('Location: /index.php');
    exit();
}

// Controllo se l'utente vuole conversare con qualcuno
// verificando i campi del form
if (isset($_POST["contact_email"])) {
    // L'utente vuole conversare con qualcuno
    // recupero l'email del contatto
    $contact_email = $_POST["contact_email"];

    // Controllo se l'email del contatto è presente nel database
    $results = $connection->query("SELECT * FROM users WHERE email = '$contact_email'");

    if ($results->num_rows !== 0) {
        // L'email del contatto è presente nel database 
        
        $results = $connection->query("INSERT INTO conversations (user")
    } else {
        // L'email del contatto non è presente nel database
        // Avviso l'utente che l'email inserita non è valida
        $ERROR = "user_not_found";
    }
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="shortcut icon" href="./assets/stemma_IISVV.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div>
        <h1>Dashboard</h1>
        <a name="logout" id="logout" class="btn-danger" href="/logout.php">Logout</a>
        <a name="renew_keys" id="renew_keys" class="btn-danger" href="/renew-keys.php">Elimina Conversazioni</a>
        <h2>Avvia conversazione</h2>
        <form method="POST">
            <label for="contact_email">Inserisci email della persona con cui vuoi conversare</label>
            <input type="email" name="contact_email" id="contact_email" placeholder="Email contatto">
            <input type="submit" value="Conversa">
        </form>
        <h2>Conversazioni attive</h2>
        <ul>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
        </ul>
    </div>
</body>
</html>