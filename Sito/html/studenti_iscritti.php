<?php
session_start();

// Verifica se l'utente è autenticato come docente
if (!isset($_SESSION["ruolo"]) || ($_SESSION["ruolo"] !== "docente")) {
    // Messaggio di errore e reindirizzamento alla pagina di login
    echo "Accesso non consentito. Effettua il login come docente.";
    exit();
}

$db = pg_connect("host=pgsql user=tomas password=unimipgsql dbname=progetto");

$email_docente_loggato = $_SESSION["email"];

// Verifica se il parametro codice_appello è presente nell'URL
if (!isset($_GET['codice_appello'])) {
    echo "Parametro mancante: codice_appello.";
    exit();
}

$codice_appello = $_GET['codice_appello'];

// Esegui la query per ottenere la lista degli studenti iscritti all'appello
$queryIscrittiAppello = "SELECT iscritto FROM progetto.appelli WHERE id = $1";
$resultIscrittiAppello = pg_query_params($db, $queryIscrittiAppello, array($codice_appello));

// Verifica se la query è stata eseguita correttamente
if (!$resultIscrittiAppello) {
    die("Errore nella query degli studenti iscritti: " . pg_last_error($db));
}

// Ottiene la lista degli studenti iscritti
$rowIscrittiAppello = pg_fetch_assoc($resultIscrittiAppello);
$studenti_iscritti = $rowIscrittiAppello['iscritto'];

// Se ci sono studenti iscritti, visualizzali
if (!empty($studenti_iscritti)) {
    $studenti = explode(',', $studenti_iscritti);
    echo "<h1>Studenti Iscritti all'Appello</h1>";
    echo "<ul>";
    foreach ($studenti as $studente) {
        echo "<li>$studente";

        echo "<form action='valutazione.php' method='post'>";
        echo "<input type='hidden' name='codice_appello' value='$codice_appello'>";
        echo "<input type='hidden' name='studente' value='$studente'>";
        echo "<label for='voto'>Voto (1-30): </label>";
        echo "<input type='number' name='voto' min='1' max='30' required>";
        echo "<input type='submit' value='Inserisci Valutazione'>";
        echo "</form>";

        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Nessuno studente iscritto a questo appello.</p>";
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Studenti Iscritti</title>
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
            font-size: 60px;
            font-family: cursive;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <a href="Docente.php">Torna alla dashboard</a>
</body>

</html>
