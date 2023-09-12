<?php

session_start(); // Inizializzo la sessione. (Necessario per accedere alla variabile $_SESSION)

require_once __DIR__ . '/utils/nonce.php';
require_once __DIR__ . '/utils/database.php';

$ERROR = null; // Variabile per registrare eventuali errori di registrazione

// Controllo se l'utente è già loggato
// se lo è lo reindirizzo alla dashboard.
if (isset($_SESSION['user_email'])) {
    header('Location: /dashboard.php');
}

// Verifico che la richiesta non sia riprodotta
if (isset($_POST['nonce'])) {
    if (Nonce::verify($_POST['nonce'])) {
        // Il nonce è valido, la richiesta non è riprodotta quindi procedo.

        // Recupero i dati dal form
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Verifico che le password siano uguali
        if ($password === $confirm_password) {
            // Controllo che l'email non sia già stata usata
            $result = $connection->query("SELECT email FROM users WHERE email = '$email'");

            // Se la ricerca ha prodotto risultati allora esiste un account
            // associato all'email inserita.
            if ($result->num_rows == 0) {
                // Se non ci sono imprevisti procedo con la registrazione
                // salvando i dati nel database.

                // Genero un salt casuale per fare l'hash della password e
                // rendere più sicuro il sistema prevendendo che due utenti
                // con la stessa password abbiano lo stesso hash.
                $salt = bin2hex(random_bytes(16));

                // Genero l'hash della password aggiungendo in coda il salt
                // utilizzando l'algoritmo sha256.
                $password_hash = hash('sha256', $password . $salt);

                // In genere è bene per verificare la corretta appartenenza
                // di un utente all'email inserita inviare una mail di verifica

                // Eseguiamo la query per salvare i dati nel database.
                $result = $connection->query("INSERT INTO users (email, salt, hash) VALUES ('$email', '$salt', '$password_hash')");

                // Controllo se ci sono stati errori durante l'esecuzione della query.
                if ($result) {
                    // Ora l'utente è registrato quindi posso reindirizzarlo alla pagina di login.
                    header('Location: /login.php?success=account_created');
                } else {
                    // Se ci sono stati errori durante l'esecuzione della query
                    // allora notifico l'utente che la registrazione non è andata a buon fine.
                    // Reindirizzo l'utente alla pagina di login.
                    $ERROR = 'database_failure';
                }
            } else {
                // L'email è già stata usata
                $ERROR = 'email_already_used';
            }
        } else {
            // Le password non sono uguali
            $ERROR = 'passwords_not_match';
        }
    } else {
        // Il nonce non è valido quindi la richiesta è riprodotta.
        // Reindirizzo l'utente alla pagina di login.
        $ERROR = 'replay_attack';
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
                case 'replay_attack':
                    echo '<div class="error">Richiesta riprodotta!</div>';
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