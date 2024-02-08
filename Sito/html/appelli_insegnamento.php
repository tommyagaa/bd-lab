<?php
session_start();

// Verifica se l'utente è autenticato
if (!isset($_SESSION["ruolo"])) {
    echo "Accesso non consentito. Effettua il login.";
    exit();
}

// Connessione al database
$db = pg_connect("host=pgsql user=tomas password=unimipgsql dbname=progetto");

// Controlla se il parametro codice_insegnamento è presente nell'URL
if (!isset($_GET['codice_insegnamento'])) {
    echo "Parametro mancante: codice_insegnamento.";
    exit();
}

$codice_insegnamento = $_GET['codice_insegnamento'];

// Esegui la query per ottenere gli appelli dell'insegnamento
$queryAppelli = "SELECT * FROM progetto.appelli WHERE id= $1";
$resultAppelli = pg_query_params($db, $queryAppelli, array($codice_insegnamento));

// Verifica se la query è stata eseguita correttamente
if (!$resultAppelli) {
    die("Errore nella query degli appelli: " . pg_last_error($db));
}

// Gestisci l'iscrizione all'appello
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica che il form sia stato inviato
    if (isset($_POST['iscrivi_studente'])) {
        $codice_appello = $_POST['codice_appello'];
        $email_studente_loggato = $_SESSION["email"];

        // recupera la lista di studenti iscritti
        $queryGetIscritti = "SELECT iscritto FROM progetto.appelli WHERE id = $1";
        $resultGetIscritti = pg_query_params($db, $queryGetIscritti, array($codice_appello));

        if (!$resultGetIscritti) {
            die("Errore nell'ottenere la lista degli iscritti: " . pg_last_error($db));
        }

        $rowIscritti = pg_fetch_assoc($resultGetIscritti);
        $studenti_iscritti = $rowIscritti['iscritto'];

        // Aggiungi l'email dello studente corrente alla lista
        $studenti_iscritti .= empty($studenti_iscritti) ? $email_studente_loggato : ",$email_studente_loggato";

        // Aggiorna la lista degli studenti iscritti
        $queryUpdateIscritti = "UPDATE progetto.appelli SET iscritto = $1 WHERE id = $2";
        $resultUpdateIscritti = pg_query_params($db, $queryUpdateIscritti, array($studenti_iscritti, $codice_appello));

        if (!$resultUpdateIscritti) {
            die("Errore nell'aggiornamento degli iscritti: " . pg_last_error($db));
        }

        echo "Iscrizione avvenuta con successo!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appelli Insegnamento</title>
</head>
<style>
    body {
        background-color: cadetblue;
        color: black;
        font-size: 25px;
        font-family: Georgia, 'Times New Roman', Times, serif;
        text-align: left;
        margin-top: 50px;
    }
    h1 {
        color: black;
        font-size: 50px;
        font-family: cursive;
        text-align: left;
        margin-top: 90px;
    }
</style>
<body>

    <h1>Appelli per l'insegnamento</h1>

    <?php
    // Verifica se ci sono appelli
    if (pg_num_rows($resultAppelli) > 0) {
        echo "<ul>";
        while ($rowAppello = pg_fetch_assoc($resultAppelli)) {
            echo "<li>Data Svolgimento: {$rowAppello['data_svolgimento']}";
            echo "<form action='' method='post'>";
            echo "<input type='hidden' name='codice_appello' value='{$rowAppello['id']}'>";
            echo "<button type='submit' name='iscrivi_studente'>Iscriviti</button>";
            echo "</form>";

            echo "</li>";
        }
        echo "</ul>";
    }
    ?>
<a href="Studente.php">Torna alla dashboard</a>
</body>
</html>

