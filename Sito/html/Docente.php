<?php
session_start();

// Verifica se l'utente è autenticato come docente
if (!isset($_SESSION["ruolo"]) || ($_SESSION["ruolo"] !== "docente")) {
    // Messaggio di errore e reindirizzamento alla pagina di login
    echo "Accesso non consentito. Effettua il login come docente.";
    exit();
}

$db = pg_connect("host=pgsql user=tomas password=unimipgsql dbname=progetto");

$queryVerificaData = "";

$email_docente_loggato = $_SESSION["email"];

$queryInsegnamentiDocente = "SELECT i.codice, i.nome, i.anno, i.descrizione
                             FROM progetto.insegnamenti i
                             WHERE i.email_docente = $1";

$resultInsegnamentiDocente = pg_query_params($db, $queryInsegnamentiDocente, array($email_docente_loggato));

// Gestione inserimento di un appello
if (isset($_POST['crea_appello'])) {
    $codice_insegnamento = $_POST['id'];
    $data_svolgimento = $_POST['data_svolgimento'];

    // Esegue la query per inserire l'appello nel database
    $queryInserimentoAppello = "INSERT INTO progetto.appelli (id, data_svolgimento) VALUES ($1, $2)";

    $resultInserimentoAppello = pg_query_params($db, $queryInserimentoAppello, array($codice_insegnamento, $data_svolgimento));

    if (!$resultInserimentoAppello) {
        die("Errore nell'inserimento dell'appello: " . pg_last_error($db));
    }

    echo "Appello inserito con successo.";
}

function logout()
{
    session_destroy();
    header("Location: index.html");
    exit();
}

if (isset($_GET["logout"])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Teacher</title>
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

        h4 {
            color: black;
            font-size: 20px;
            font-family: Georgia, 'Times New Roman', Times, serif;
        }

        p1 {
            color: black;
            font-size: 20px;
            font-family: Georgia, 'Times New Roman', Times, serif;
            text-align: center;
            margin-top: 90px;
        }

        a:link {
            font-size: 19px;
        }

        .dashboard-container {}

        .logout-link {
            position: absolute;
            top: 10px;
            right: 10px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }

        h3 {
            font-size: 30px;
            margin-top: 20px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin-bottom: 10px;
        }

        p2 {
            color: black;
            font-size: 40px;
            font-family: cursive;
            text-align: center;
            margin-top: 90px;
        }

        p3 {
            color: black;
            font-size: 40px;
            font-family: cursive;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <!-- logout -->
    <div class="dashboard-container">
        <a href="Docente.php?logout=true" class="logout-link">Logout</a>
    </div>

    <h1>My Teacher</h1>
    <h4>Benvenuto nella tua Area personale</h4>
    <p1>Hai la possibilità di cambiare la password qua: </p1>
    <a href="newPassword.php">Cambia Password</a>

    <!-- Visualizzazione degli insegnamenti -->
    <?php

    if ($resultInsegnamentiDocente && pg_num_rows($resultInsegnamentiDocente) > 0) {
        echo "<h3>Insegnamenti:</h3>";
        echo "<ul>";
        while ($rowInsegnamento = pg_fetch_assoc($resultInsegnamentiDocente)) {
            echo "<li>{$rowInsegnamento['nome']} (Anno {$rowInsegnamento['anno']}) - {$rowInsegnamento['descrizione']} ";
            echo "<a href='appelli_docente.php?codice_insegnamento={$rowInsegnamento['codice']}'>Visualizza Appelli</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nessun insegnamento associato al docente.</p>";
    }
    ?>
    <p2>Crea Appello</p2>
    <form action="Docente.php" method="post">
        <label for="id">Insegnamento:</label>
        <select id="id" name="id" required>
            <?php
            // Popola il menu a discesa con gli insegnamenti del docente
            $resultInsegnamentiDocente = pg_query_params($db, $queryInsegnamentiDocente, array($email_docente_loggato));

            while ($rowInsegnamento = pg_fetch_assoc($resultInsegnamentiDocente)) {
                echo "<option value=\"{$rowInsegnamento['codice']}\">{$rowInsegnamento['nome']} (Anno {$rowInsegnamento['anno']})</option>";
            }
            ?>
        </select>

        <label for="data_svolgimento">Data Svolgimento:</label>
        <input type="date" id="data_svolgimento" name="data_svolgimento" required>

        <input type="submit" value="Crea Appello" name="crea_appello">
    </form>
    <br></br>

    <!-- Visualizza Appelli Creati -->
    <p3>Visualizza Appelli Creati</p3>
    <?php
    // Esegui una query per ottenere gli appelli creati dal docente
    $queryAppelliCreati = "SELECT a.*, i.nome as nome_insegnamento
                           FROM progetto.appelli a
                           INNER JOIN progetto.insegnamenti i ON a.id = i.codice
                           WHERE i.email_docente = $1";
    $resultAppelliCreati = pg_query_params($db, $queryAppelliCreati, array($email_docente_loggato));

    // Verifica se ci sono appelli creati
    if ($resultAppelliCreati && pg_num_rows($resultAppelliCreati) > 0) {
        echo "<ul>";
        while ($rowAppelloCreato = pg_fetch_assoc($resultAppelliCreati)) {
            echo "<li>Data Svolgimento: {$rowAppelloCreato['data_svolgimento']} - Insegnamento: {$rowAppelloCreato['nome_insegnamento']} ";
            echo "<a href='studenti_iscritti.php?codice_appello={$rowAppelloCreato['id']}'>Visualizza Studenti Iscritti</a></li>";
          
        }
        echo "</ul>";
    } else {
        echo "<p>Nessun appello creato.</p>";
    }
    ?>
</body>

</html>


