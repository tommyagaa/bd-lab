<?php
session_start();

// Verifica se l'utente è autenticato come studente
if (!isset($_SESSION["ruolo"]) || ($_SESSION["ruolo"] !== "studente")) {
    echo "Accesso non consentito. Effettua il login come studente.";
    exit();
}

function logout() {
    session_destroy();
    header("Location: index.html");
    exit();
}

if (isset($_GET["logout"])) {
    logout();
}


$db = pg_connect("host=pgsql user=tomas password=unimipgsql dbname=progetto");

$email_studente_loggato = $_SESSION["email"];

// Ottienie il corso di laurea dell'utente
$queryCorsoLaurea = "SELECT c.nome
                     FROM progetto.corso_di_laurea c
                     INNER JOIN progetto.studente s ON c.codice = s.cdl_codice
                     WHERE s.email = $1";

$resultCorsoLaurea = pg_query_params($db, $queryCorsoLaurea, array($email_studente_loggato));

$corso_laurea = "";

if ($resultCorsoLaurea && pg_num_rows($resultCorsoLaurea) > 0) {
    $rowCorsoLaurea = pg_fetch_assoc($resultCorsoLaurea);
    $corso_laurea = $rowCorsoLaurea['nome'];
}

// Ottiene gli insegnamenti del proprio corso di laurea
$queryInsegnamenti = "SELECT i.codice, i.nome, i.anno
                      FROM progetto.insegnamenti i
                      INNER JOIN progetto.corso_di_laurea c ON i.cdl_codice = c.codice
                      INNER JOIN progetto.studente s ON c.codice = s.cdl_codice
                      WHERE s.email = $1";

$resultInsegnamenti = pg_query_params($db, $queryInsegnamenti, array($email_studente_loggato));

$queryCarriera = "SELECT m.voto, m.codice_ins
                  FROM progetto.memoria_esame m
                  WHERE m.nome = $1";

$resultCarriera = pg_query_params($db, $queryCarriera, array($email_studente_loggato));

if ($resultCarriera === false) {
    die("Errore nella query carriera: " . pg_last_error($db));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Student</title>
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
            font-size: 30px;
            font-family: Georgia, 'Times New Roman', Times, serif;
            text-align: center;
            margin-top: 90px;
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
            text-align: center;
            margin-top: 90px;
        }

        p4 {
            color: black;
            font-size: 40px;
            font-family: cursive;
            text-align: center;
            margin-top: 90px;
        }
        p5{
            color: black;
            font-size: 40px;
            font-family: cursive;
            text-align: center;
            margin-top: 90px;
        }

        a:link {
            font-size: 19px;
        }

        .dashboard-container {

        }

        .logout-link {
            position: absolute;
            top: 10px;
            right: 10px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }

        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <a href="Studente.php?logout=true" class="logout-link">Logout</a>
    </div>

    <h1>My Student</h1>
    <h4>Benvenuto nella tua Area personale</h4>
    <p1>Hai la possibilità di cambiare la password qua: </p1>
    <a href="newPassword.php" style="font-size:20px;">Cambia Password</a>
    <br><br>
    <p2>Corso di Laurea: <?php echo $corso_laurea; ?></p2>
    <table>
        <thead>
            <tr>
                <th>Codice</th>
                <th>Nome</th>
                <th>Anno</th>
                <th>Azione</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($rowInsegnamento = pg_fetch_assoc($resultInsegnamenti)) {
                echo "<tr>";
                echo "<td>{$rowInsegnamento['codice']}</td>";
                echo "<td>{$rowInsegnamento['nome']}</td>";
                echo "<td>{$rowInsegnamento['anno']}</td>";

                // Aggiunto il link "Guarda Appelli"
                echo "<td>";
                echo "<a href='appelli_insegnamento.php?codice_insegnamento={$rowInsegnamento['codice']}'>Guarda Appelli</a>";
                echo "</td>";

                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    <p3>Carriera Valida</p3>
    <table>
    <thead>
        <tr>
            <th>Voto</th>
            <th>Codice Ins.</th>
            <th>Nome Insegnamento</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Riporta il puntatore del result set all'inizio
        pg_result_seek($resultCarriera, 0);
        while ($rowEsame = pg_fetch_assoc($resultCarriera)) {
            if ($rowEsame['voto'] >= 18) {
                echo "<tr>";
                echo "<td>{$rowEsame['voto']}</td>";
                echo "<td>{$rowEsame['codice_ins']}</td>";

                // Utilizza il nome dell'insegnamento
                $queryNomeInsegnamento = "SELECT nome FROM progetto.insegnamenti WHERE codice = $1";
                $resultNomeInsegnamento = pg_query_params($db, $queryNomeInsegnamento, array($rowEsame['codice_ins']));

                if ($resultNomeInsegnamento && pg_num_rows($resultNomeInsegnamento) > 0) {
                    $rowNomeInsegnamento = pg_fetch_assoc($resultNomeInsegnamento);
                    $nomeInsegnamento = $rowNomeInsegnamento['nome'];
                } else {
                    $nomeInsegnamento = 'Nome non disponibile';
                }

                echo "<td>{$nomeInsegnamento}</td>";

                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>

<!-- Tabella Carriera con voti < 18 -->
<p5>Carriera (Voti < 18)</p5>
<table>
    <thead>
        <tr>
            <th>Voto</th>
            <th>Codice Ins.</th>
            <th>Nome Insegnamento</th>
        </tr>
    </thead>
    <tbody>
        <?php
        pg_result_seek($resultCarriera, 0);
        while ($rowEsame = pg_fetch_assoc($resultCarriera)) {
            if ($rowEsame['voto'] < 18){
                echo "<tr>";
                echo "<td>{$rowEsame['voto']}</td>";
                echo "<td>{$rowEsame['codice_ins']}</td>";

                $queryNomeInsegnamento = "SELECT nome FROM progetto.insegnamenti WHERE codice = $1";
                $resultNomeInsegnamento = pg_query_params($db, $queryNomeInsegnamento, array($rowEsame['codice_ins']));

                if ($resultNomeInsegnamento && pg_num_rows($resultNomeInsegnamento) > 0) {
                    $rowNomeInsegnamento = pg_fetch_assoc($resultNomeInsegnamento);
                    $nomeInsegnamento = $rowNomeInsegnamento['nome'];
                } else {
                    $nomeInsegnamento = 'Nome non disponibile';
                }

                echo "<td>{$nomeInsegnamento}</td>";

                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>
<br>
<form method="post">
    <label for="corso_laurea_selezionato">Seleziona un corso di laurea:</label>
    <select name="corso_laurea_selezionato">
        <?php
        // Ottiene la lista di tutti i corsi di laurea
        $queryTuttiCorsiLaurea = "SELECT nome FROM progetto.corso_di_laurea";
        $resultTuttiCorsiLaurea = pg_query($db, $queryTuttiCorsiLaurea);

        if ($resultTuttiCorsiLaurea === false) {
            die("Errore nella query per ottenere la lista dei corsi di laurea: " . pg_last_error($db));
        }

        while ($rowCorsoLaurea = pg_fetch_assoc($resultTuttiCorsiLaurea)) {
            $nomeCorsoLaurea = $rowCorsoLaurea['nome'];
            echo "<option value=\"$nomeCorsoLaurea\">$nomeCorsoLaurea</option>";
        }
        ?>
    </select>
    <input type="submit" value="Visualizza Informazioni">
</form>

<!-- Visualizza gli insegnamenti del corso di laurea selezionato -->
<?php
if (isset($_POST['corso_laurea_selezionato'])) {
    $corso_laurea_selezionato = $_POST['corso_laurea_selezionato'];

    // Ottiene informazioni sul corso di laurea selezionato
    $queryInfoCorsoLaurea = "SELECT * FROM progetto.corso_di_laurea WHERE nome = $1";
    $resultInfoCorsoLaurea = pg_query_params($db, $queryInfoCorsoLaurea, array($corso_laurea_selezionato));

    if ($resultInfoCorsoLaurea === false) {
        die("Errore nella query per ottenere informazioni sul corso di laurea: " . pg_last_error($db));
    }

    $rowInfoCorsoLaurea = pg_fetch_assoc($resultInfoCorsoLaurea);

    echo "<h2>Informazioni sul Corso di Laurea: $corso_laurea_selezionato</h2>";
    echo "<p>Livello: {$rowInfoCorsoLaurea['livello']}</p>";

    // Visualizza gli insegnamenti del corso di laurea selezionato
    $queryInsegnamentiAltroCdl = "SELECT i.codice, i.nome, i.anno, i.descrizione, i.email_docente
                                  FROM progetto.insegnamenti i
                                  INNER JOIN progetto.corso_di_laurea c ON i.cdl_codice = c.codice
                                  WHERE c.nome = $1";

    $resultInsegnamentiAltroCdl = pg_query_params($db, $queryInsegnamentiAltroCdl, array($corso_laurea_selezionato));

    if ($resultInsegnamentiAltroCdl === false) {
        die("Errore nella query degli insegnamenti per il corso di laurea selezionato: " . pg_last_error($db));
    }

    // Visualizza la tabella degli insegnamenti del corso di laurea selezionato
    echo "<h2>Insegnamenti del Corso di Laurea: $corso_laurea_selezionato</h2>";
    echo "<table>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Codice</th>";
    echo "<th>Nome</th>";
    echo "<th>Anno</th>";
    echo "<th>Descrizione</th>";
    echo "<th>Email Docente</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    while ($rowInsegnamentoAltroCdl = pg_fetch_assoc($resultInsegnamentiAltroCdl)) {
        echo "<tr>";
        echo "<td>{$rowInsegnamentoAltroCdl['codice']}</td>";
        echo "<td>{$rowInsegnamentoAltroCdl['nome']}</td>";
        echo "<td>{$rowInsegnamentoAltroCdl['anno']}</td>";
        echo "<td>{$rowInsegnamentoAltroCdl['descrizione']}</td>";
        echo "<td>{$rowInsegnamentoAltroCdl['email_docente']}</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
}
?>
</html>
