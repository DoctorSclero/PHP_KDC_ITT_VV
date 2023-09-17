<?php

session_start(); // Inizializzo la sessione. (Necessario per accedere alla variabile $_SESSION)

require_once __DIR__ . '/utils/nonce.php';
require_once __DIR__ . '/utils/database.php';

$ERROR = null; // Variabile per registrare eventuali errori di registrazione

// Controllo se l'utente è già loggato
// se lo è lo reindirizzo alla dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit();
}

// Verifico se è stata inoltrata una richiesta di registrazione
if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    try {
        // Estrapolo i dati della richiesta
        $submitted_user["nonce"] = $_POST["nonce"] ?? null;
        $submitted_user["email"] = $_POST["email"] ?? null;
        $submitted_user["password"] = $_POST["password"] ?? null;
        $submitted_user["confirm_password"] = $_POST["confirm_password"] ?? null;

        // Verifico il nonce contro gli attacchi di replica
        if (empty($submitted_user["nonce"]) || !Nonce::verify($submitted_user["nonce"])) {
            // Il nonce non è valido potrebbe essere un attacco
            // di replica, lancio un eccezione
            throw new Exception("invalid_nonce");
        }

        // Ottengo i dati corrispondenti all'utente dal database
        $database_user = $connection->query(
            "SELECT id, salt, hash FROM users
            WHERE email = '{$submitted_user["email"]}'"
        )->fetch_assoc();

        // Verifico che l'utente non sia già registrato nel KDC
        if (!empty($database_user)) {
            // L'utente è già registrato, informo l'utente
            // attraverso un'eccezione
            throw new Exception("email_already_used");
        }

        // Verifico che le password siano uguali
        if ($submitted_user["password"] !== $submitted_user["confirm_password"]) {
            // Le password non sono uguali, notifico l'utente
            // attraverso un'eccezione
            throw new Exception("passwords_not_match");
        }

        // Le password combaciano e l'utente è registrato
        // con successo, salvo l'id utente nella sessione

        // Genero un salt casuale per fare l'hash della password e
        // rendere più sicuro il sistema prevendendo che due utenti
        // con la stessa password abbiano lo stesso hash.
        $submitted_user["salt"] = bin2hex(random_bytes(16));

        // Genero l'hash della password aggiungendo in coda il salt
        // utilizzando l'algoritmo sha256.
        $submitted_user["hash"] = hash('sha256', $submitted_user["password"] . $submitted_user["salt"]);

        // Eseguo la query per salvare i dati nel database.
        $result = $connection->query(
            "INSERT INTO users (email, salt, hash) VALUES 
            ('{$submitted_user["email"]}', '{$submitted_user["salt"]}', '{$submitted_user["hash"]}')"
        );

        // Controllo se ci sono stati errori durante l'esecuzione della query.
        if (!$result) {
            throw new Exception("database_failure");
        }

        // Ora l'utente è registrato quindi posso reindirizzarlo alla pagina di login.
        header('Location: /login.php?success=account_created');
    } catch (Exception $e) {
        $ERROR = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITT KDC</title>
    <link rel="shortcut icon" href="./assets/stemma_IISVV.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <div>
        <a href="/index.php">Indietro</a>

        <h1>Register</h1>
        <!-- Stampo eventuali errori di registrazione -->
        <?php
        if (isset($ERROR)) {
            switch ($ERROR) {
                case 'passwords_not_match':
                    echo '<div class="error">Le password non coincidono!</div>';
                    break;
                case 'email_already_used':
                    echo '<div class="error">L\'email inserita è già stata usata!</div>';
                    break;
                case 'database_failure':
                    echo '<div class="error">Errore durante la registrazione!</div>';
                    break;
                case 'invalid_nonce':
                    echo '<div class="error">Nonce non valido! Ricompila i campi a mano</div>';
                    break;
            }
        }
        ?>


        <form method="POST">
            <!--
                Per rendere sicuro il form dalla da attacchi di replica facciamo uso di un nonce.
                Lo facciamo inserire dal server all'interno del form in modo che ci venga restituito
                al momento della submit. Se il nonce non corrisponde a quello generato dal server
                allora il form non viene accettato in quanto potrebbe essere una replica di una richiesta
                effettuata in precedenza. (Attacco di replica)
            -->
            <input type="hidden" name="nonce" value="<?php echo Nonce::generate_and_store(); ?>">
            <input type="email" name="email" placeholder="Email">
            <input type="password" name="password" placeholder="Password">
            <input type="password" name="confirm_password" placeholder="Conferma Password">
            <input type="submit" value="Register">
        </form>
    </div>
</body>

</html>