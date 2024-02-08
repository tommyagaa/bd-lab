<?php
session_start();

// Verifica se l'utente è autenticato come docente
if (!isset($_SESSION["ruolo"]) || ($_SESSION["ruolo"] !== "docente")) {
    echo "Accesso non consentito. Effettua il login come docente.";
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
$queryAppelli = "SELECT * FROM progetto.appelli WHERE id = $1";
$resultAppelli = pg_query_params($db, $queryAppelli, array($codice_insegnamento));

// Verifica se la query è stata eseguita correttamente
if (!$resultAppelli) {
    die("Errore nella query degli appelli: " . pg_last_error($db));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appelli Docente</title>

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
    </style>
</head>
<body>
    <h1>Appelli per l'insegnamento</h1>

    <?php
    // Verifica se ci sono appelli
    if (pg_num_rows($resultAppelli) > 0) {
        echo "<ul>";
        while ($rowAppello = pg_fetch_assoc($resultAppelli)) {
            echo "<li>Data Svolgimento: {$rowAppello['data_svolgimento']}</li>";
        }
        echo "</ul>";
    } else {
        // Nessun appello trovato
        echo "<p>Nessun appello presente per questo insegnamento.</p>";
    }
    ?>
    <a href="Docente.php">Torna alla dashboard</a>

</body>
</html>
