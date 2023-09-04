<?php

session_start();

if (isset($_SESSION['user_email'])) {
    header('Location: /dashboard.php');
}

require_once './vendor/nonce.php';

if (isset($_POST['nonce'])) {
    if (Nonce::verify($_POST['nonce'])) {
        // Il nonce è valido la richiesta non è riprodotta quindi procedo.
        require_once './vendor/database.php';

        // Recupero i dati dal form
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Verifico che le password siano uguali
        if ($password !== $confirm_password) {
            // Le password non sono uguali
            header('Location: /register.php?error=passwords_not_match');
            exit;
        }

        // Controllo che l'email non sia già stata usata ma prima la sanifico
        // per farlo uso un prepared statement di mysqli.
        $query = $connection->prepare("SELECT email FROM users WHERE email = ?");
        // Bindiamo i parametri al prepared statement.
        $query->bind_param("s", $submitted_email);
        // Eseguiamo la query per ottenere i risultati
        $result = $connection->query($query);

        // Se la ricerca ha prodotto risultati allora esiste un account
        // associato all'email inserita.
        if ($result->num_rows > 0) {
            // L'email è già stata usata
            header('Location: /register.php?error=email_already_used');
            exit;
        }

        // Genero un salt casuale
        $salt = bin2hex(random_bytes(32));
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <div class="vh-100 bg-light">
        <div class="h-100 d-flex justify-content-center align-items-center">
            <div class="content text-center">
                <div class="mb-3">
                    <h1>Register</h1>
                </div>    
                <form method="POST">
                    <!--
                        Per rendere sicuro il form dalla da attacchi di replica facciamo uso di un nonce.
                        Lo facciamo inserire dal server all'interno del form in modo che ci venga restituito
                        al momento della submit. Se il nonce non corrisponde a quello generato dal server
                        allora il form non viene accettato in quanto potrebbe essere una replica di una richiesta
                        effettuata in precedenza. (Attacco di replica)
                    -->
                    <input type="hidden" name="nonce" value="<?php echo Nonce::generate_and_store(); ?>">
                    <div class="mb-3">
                        <input type="email" class="form-control" name="" id="" placeholder="Email" title="Email">
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" name="" id="" placeholder="Password" title="Password">
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" name="" id="" placeholder="Confirm Password" title="Confirm Password">
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                    <a class="btn btn-secondary" href="index.php" role="button">Back</a>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
</body>

</html>