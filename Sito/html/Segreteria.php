<?php
session_start();

include_once 'database.php';
include_once 'cdl.php';
include_once 'insegnamenti.php';

// Verifica se l'utente è autenticato come segreteria
if (!isset($_SESSION["ruolo"]) || ($_SESSION["ruolo"] !== "segreteria")) {
    // Messaggio di errore e reindirizzamento alla pagina di login
    echo "Accesso non consentito. Effettua il login come segreteria.";
    exit();
}


$db = pg_connect("host=pgsql user=tomas password=unimipgsql dbname=progetto");


// Gestisce l'inserimento di un docente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["inserisci_docente"])) {
    $nome_docente = $_POST["nome_docente"] ?? "";
    $cognome_docente = $_POST["cognome_docente"] ?? "";
    $email_docente = $_POST["email_docente"] ?? "";
    $password_docente = $_POST["password_docente"] ?? "";

    // Verifica se l'email del docente esiste già
    $verificaDocenteQuery = "SELECT 1 FROM progetto.docente WHERE email = $1";
    $verificaDocenteResult = pg_query_params($db, $verificaDocenteQuery, array($email_docente));

    if (!$verificaDocenteResult || pg_num_rows($verificaDocenteResult) > 0) {
        echo "Errore: L'email del docente già esiste.";
        exit();
    }

    // Esegue l'inserimento del docente nel database
    $inserimentoQuery = "INSERT INTO progetto.docente(nome, cognome, email, password) VALUES ($1, $2, $3, $4)";
    $result = pg_query_params($db, $inserimentoQuery, array($nome_docente, $cognome_docente, $email_docente, $password_docente));

    if ($result) {
        echo "Docente inserito con successo";
    } else {
        echo "Errore durante l'inserimento del docente";
    }
}

// Gestisce l'inserimento di uno studente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["inserisci_studente"])) {
    $matricola = $_POST["matricola"] ?? "";
    $nome_studente = $_POST["nome_studente"] ?? "";
    $cognome_studente = $_POST["cognome_studente"] ?? "";
    $email_studente = $_POST["email_studente"] ?? "";
    $cdl_codice = $_POST["cdl_codice"] ?? "";
    $password_studente = $_POST["password_studente"] ?? "";

    // Verifica se il corso di laurea esiste
    $verificaCorsoQuery = "SELECT * FROM progetto.corso_di_laurea WHERE codice = $1";
    $verificaCorsoResult = pg_query_params($db, $verificaCorsoQuery, array($cdl_codice));

    if (!$verificaCorsoResult || pg_num_rows($verificaCorsoResult) == 0) {
        echo "Errore: Il corso di laurea specificato non esiste.";
        exit();
    }

    // Esegue l'inserimento dello studente nel database
    $inserimentoStudenteQuery = "INSERT INTO progetto.studente (matricola, nome, cognome, email, cdl_codice, password) VALUES ($1, $2, $3, $4, $5, $6)";
    $resultStudente = pg_query_params($db, $inserimentoStudenteQuery, array($matricola, $nome_studente, $cognome_studente, $email_studente, $cdl_codice, $password_studente));

    if ($resultStudente) {
        echo "Studente inserito con successo";
    } else {
        echo "Errore durante l'inserimento dello studente";
    }
}
//funzione per tasto logout
function logout() {

    session_destroy();
    

    header("Location: index.html");
    exit();
}

if (isset($_GET["logout"])) {
    logout();
}
// inserimento corso di laurea

if (isset($_POST['submit_cdl'])) {
    // Recupera i dati del form
    $codice = $_POST['codice']; 
    $livello = $_POST['livello'];
    $nome = $_POST['nome'];

    // Verifica se il codice è già presente
    if (inserisciCorsoDiLaurea($db, $codice, $livello, $nome)) {
        echo "Corso di laurea inserito con successo.";
    } else {
        echo "Errore: Il corso di laurea con il codice $codice è già presente.";
    }
}


// inserimento insegnamenti per corso di laurea
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["inserisci_insegnamento"])) {
    $codice_insegnamento = $_POST["codice_insegnamento"] ?? "";
    $codice_cdl = $_POST["codice_cdl"] ?? "";
    $nome_insegnamento = $_POST["nome_insegnamento"] ?? "";
    $anno_insegnamento = $_POST["anno_insegnamento"] ?? "";
    $descrizione_insegnamento = $_POST["descrizione_insegnamento"] ?? "";
    $email_docente = $_POST["email_docente"] ?? "";

    // Esegue la funzione per inserire l'insegnamento
    $resultInsegnamento = inserisciInsegnamento($db, $codice_insegnamento, $codice_cdl, $nome_insegnamento, $anno_insegnamento, $descrizione_insegnamento, $email_docente);

    if ($resultInsegnamento) {
        echo "Insegnamento inserito con successo";
    } else {
        echo "Errore durante l'inserimento dell'insegnamento";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["visualizza_carriera"])) {
    $matricola_studente = $_POST["matricola_studente"] ?? "";

    // Ottiene la carriera dello studente con voti >= 18
    $queryCarrieraStudente = "SELECT m.voto, m.codice_ins, i.nome
        FROM progetto.memoria_esame m
        JOIN progetto.insegnamenti i ON m.codice_ins = i.codice
        WHERE m.nome = $1 AND m.voto >= 18";

    $resultCarrieraStudente = pg_query_params($db, $queryCarrieraStudente, array($matricola_studente));

    if ($resultCarrieraStudente) {
        echo "<p6>Carriera dello studente con matricola {$matricola_studente}</p6>";
        echo "<table>";
        echo "<tr>";
        echo "<th>Voto</th>";
        echo "<th>Codice</th>";
        echo "<th>Nome Insegnamento</th>";
        echo "</tr>";

        while ($rowCarrieraStudente = pg_fetch_assoc($resultCarrieraStudente)) {
            echo "<tr>";
            echo "<td>{$rowCarrieraStudente['voto']}</td>";
            echo "<td>{$rowCarrieraStudente['codice_ins']}</td>";
            echo "<td>{$rowCarrieraStudente['nome']}</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "Errore nella query: " . pg_last_error($db);
    }
}

//Ottiene la carriera dello studente con voti sia < che >18
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["visualizza_carrieraCompleta"])){
    $matricola_studente = $_POST["matricola_studente"] ?? "";
    $queryCarrieraStudenteCompleta = "SELECT m.voto, m.codice_ins, i.nome
    FROM progetto.memoria_esame m
    JOIN progetto.insegnamenti i ON m.codice_ins = i.codice
    WHERE m.nome = $1 AND (m.voto >= 18 OR m.voto <= 18)";

  $resultCarrieraStudenteCompleta = pg_query_params($db, $queryCarrieraStudenteCompleta, array($matricola_studente));
  if ($resultCarrieraStudenteCompleta) {
    echo "<p6>Carriera Completa dello studente con matricola {$matricola_studente}</p6>";
    echo "<table>";
    echo "<tr>";
    echo "<th>Voto</th>";
    echo "<th>Codice</th>";
    echo "<th>Nome Insegnamento</th>";
    echo "</tr>";

    while ($rowCarrieraStudenteCompleta = pg_fetch_assoc($resultCarrieraStudenteCompleta)) {
        echo "<tr>";
        echo "<td>{$rowCarrieraStudenteCompleta['voto']}</td>";
        echo "<td>{$rowCarrieraStudenteCompleta['codice_ins']}</td>";
        echo "<td>{$rowCarrieraStudenteCompleta['nome']}</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "Errore nella query: " . pg_last_error($db);
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Segretary</title>
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
        h4{
            color: black;
            font-size: 20px;
            font-family: Georgia, 'Times New Roman', Times, serif;
        }
       
        p1{
            color: black;
            font-size: 20px;
            font-family: Georgia, 'Times New Roman', Times, serif;
            text-align: center;
            margin-top: 90px;
        }
        a:link{
            font-size: 19px;
        }
        form {
            text-align: left;
            padding: 10px;
            border: 5px solid black;
            border-radius: 40px;
            background-color: white;
        }
        p2{
            color: black;
            font-size: 40px;
            font-family: cursive;
            text-align: center;
            margin-top: 90px;
        }
        p3{
            color: black;
            font-size: 40px;
            font-family: cursive;
            text-align: center;
            margin-top: 90px;
        }
        p4{
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
        p6{
            color: black;
            font-size: 40px;
            font-family: cursive;
            text-align: center;
            margin-top: 90px;
        } 
        p7{
            color: black;
            font-size: 40px;
            font-family: cursive;
            text-align: center;
            margin-top: 90px;
        } 
        p8{
            color: black;
            font-size: 40px;
            font-family: cursive;
            text-align: center;
            margin-top: 90px;
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
    </style>
</head>
<body>
    <!--tasto logut -->
    <div class="dashboard-container">
    <a href="Segreteria.php?logout=true" class="logout-link">Logout</a>
    </div>

<h1>My Segretary</h1>
    <h4>Benvenuto nella tua Area personale</h4>
    <p1>Hai la possibilità di cambiare la password qua: </p1>
    <a href="newPassword.php" style="font-size: 20px;">Cambia Password</a>
    <br>
    <a href="Segreteria.php?logout=true" class="logout-link">Logout</a>
    <br>
    <!-- Form per inserire una nuova Utenza di Docente -->
    <p2> Hai la possibilità di inserire un nuovo docente:</p2>
    <form action="" method="post">
    <label for="nome_docente">Nome Docente:</label>
        <input type="text" id="nome_docente" name="nome_docente" required>
        <br>
        <label for="cognome_docente">Cognome Docente:</label>
        <input type="text" id="cognome_docente" name="cognome_docente" required>
        <br>
        <label for="email_docente">Email Docente:</label>
        <input type="email" id="email_docente" name="email_docente" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password_docente" name="password_docente" required> 
        <br>
        <input type="submit" name="inserisci_docente" value="Inserisci Docente">
    </form>
     <!-- Form per inserire un nuovo studente -->
    <p2> Hai la possibilità di inserire uno studente:</p2>
    <form action="" method="post">
    <label for="matricola">Matricola:</label>
        <input type="text" id="matricola" name="matricola" required>
        <br>
        <label for="nome_studente">Nome Studente:</label>
        <input type="text" id="nome_studente" name="nome_studente" required>
        <br>
        <label for="cognome_studente">Cognome Studente:</label>
        <input type="cognome" id="cognome_studente" name="cognome_studente" required>
        <br>
        <label for="email_studente">Email Studente:</label>
        <input type="email" id="email_studente" name="email_studente" required> 
        <br>
        <label for="">Cdl Codice:</label>
        <input type="cdl_codice" id="cdl_codice" name="cdl_codice" required> 
        <br>
        <label for="password">Password Studente:</label>
        <input type="password" id="password_studente" name="password_studente" required> 
        <br>
        <input type="hidden" name="inserisci_studente" value="1">
        <input type="submit" value="Inserisci Studente">
    </form>
    <p3>Lista Docenti</p3>
    <table>
        <tr>
            <th>Nome</th>
            <th>Cognome</th>
            <th>Email</th>

        </tr>
        <?php
        $queryDocenti = "SELECT * FROM progetto.docente";
        $resultDocenti = pg_query($db, $queryDocenti);

        while ($rowDocente = pg_fetch_assoc($resultDocenti)) {
            echo "<tr>";
            echo "<td>{$rowDocente['nome']}</td>";
            echo "<td>{$rowDocente['cognome']}</td>";
            echo "<td>{$rowDocente['email']}</td>";
       
            echo "</tr>";
        }
        ?>
    </table>

    <form action="eliminaDocente.php" method="post">
        <label for="email_docente">Email Docente da eliminare:</label>
        <input type="email" id="email_docente" name="email_docente" required>
        <br>
        <input type="submit" value="Elimina Docente">
    </form>

    <p4>Lista Studenti</p4>
    <table>
    <tr>
        <th>Matricola</th>
        <th>Nome</th>
        <th>Cognome</th>
        <th>Email</th>
    </tr>
    <?php
    $queryElencoStudenti = "SELECT * FROM progetto.studente";
    $resultElencoStudenti = pg_query($db, $queryElencoStudenti);

    while ($row = pg_fetch_assoc($resultElencoStudenti)) {
        echo "<tr>";
        echo "<td>{$row['matricola']}</td>";
        echo "<td>{$row['nome']}</td>";
        echo "<td>{$row['cognome']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "</tr>";
    }
    ?>
</table>
<!-- Form per elimazione studente -->
<form action="eliminaStudente.php" method="post">
    <label for="matricolaDaEliminare">Inserisci la matricola dello studente da eliminare:</label>
    <input type="text" id="matricolaDaEliminare" name="matricola" required>
    <input type="submit" value="Elimina Studente">
</form>

<!-- Form per Inserimento Corso di Laurea -->
<p5>Inserisci Corso di Laurea</p5>
<form method="post" action="Segreteria.php">
    <label for = "codice"> Codice Corso di Laurea</label>
    <input type="number" name="codice" required>

    <label for = "livello"> Livello</label>
    <input type="text" name="livello" required>

    <label for = "nome"> Nome</label>
    <input type="text" name="nome" required>
  
    <input type="submit" name="submit_cdl" value="Inserisci Corso di Laurea">
</form>


<!-- Form per inserire un nuovo insegnamento -->
<p6> Hai la possibilità di inserire un nuovo insegnamento:</p6>
<form action="" method="post">
    <label for="codice_insegnamento">Codice Insegnamento:</label>
    <input type="text" id="codice_insegnamento" name="codice_insegnamento" required>
    <br>

    <label for="codice_cdl">Codice Corso di Laurea:</label>
    <input type="text" id="codice_cdl" name="codice_cdl" required>
    <br>

    <label for="nome_insegnamento">Nome Insegnamento:</label>
    <input type="text" id="nome_insegnamento" name="nome_insegnamento" required>
    <br>

    <label for="anno_insegnamento">Anno Insegnamento:</label>
    <input type="text" id="anno_insegnamento" name="anno_insegnamento" required>
    <br>

    <label for="descrizione_insegnamento">Descrizione Insegnamento:</label>
    <input type="text" id="descrizione_insegnamento" name="descrizione_insegnamento" required>
    <br>


    <label for="email_docente">Docente:</label>
    <select name="email_docente" required>
        <?php
        // Recupera la lista di docenti dal database
        $queryDocenti = "SELECT email FROM progetto.docente";
        $resultDocenti = pg_query($db, $queryDocenti);

        // Mostra le opzioni nel menu a discesa
        while ($rowDocente = pg_fetch_assoc($resultDocenti)) {
            echo "<option value='{$rowDocente['email']}'>{$rowDocente['email']}</option>";
        }
        ?>
    </select>
    <br>

    <input type="hidden" name="inserisci_insegnamento" value="1">
    <input type="submit" value="Inserisci Insegnamento">
</form>

<!-- Visualizza Lista di Docenti con relativi Insegnamenti -->
<p7>Lista Docenti con Insegnamenti</p7>
<table>
    <tr>
        <th>Email Docente</th>
        <th>Nome</th>
        <th>Cognome</th>
        <th>Insegnamento</th>
    </tr>
    <?php
    // Query per ottenere la lista di docenti con l'insegnamento di cui sono responsabili
    $queryDocentiInsegnamenti = "SELECT d.email, d.nome, d.cognome, i.nome as insegnamento
    FROM progetto.docente d
    LEFT JOIN progetto.insegnamenti i ON d.email = i.email_docente";

$resultDocentiInsegnamenti = pg_query($db, $queryDocentiInsegnamenti);

if ($resultDocentiInsegnamenti) {
while ($rowDocenteInsegnamento = pg_fetch_assoc($resultDocentiInsegnamenti)) {
echo "<tr>";
echo "<td>{$rowDocenteInsegnamento['email']}</td>";
echo "<td>{$rowDocenteInsegnamento['nome']}</td>";
echo "<td>{$rowDocenteInsegnamento['cognome']}</td>";
echo "<td>{$rowDocenteInsegnamento['insegnamento']}</td>";
echo "</tr>";
}
} else {
echo "Errore nella query: " . pg_last_error($db);
}
    ?>
</table>
<p9>Visualizza Carriera Studente</p9>
    <form action="" method="post">
        <label for="matricola_studente">Email Studente:</label>
        <input type="text" id="matricola_studente" name="matricola_studente" required>
        <br>
        <input type="submit" name="visualizza_carriera" value="Visualizza Carriera">
    </form>
</body>
<p10>Visualizza Carriera Completa</p9>
    <form action="" method="post">
        <label for="matricola_studente">Email Studente:</label>
        <input type="text" id="matricola_studente" name="matricola_studente" required>
        <br>
        <input type="submit" name="visualizza_carrieraCompleta" value="visualizza_carrieraCompleta">
    </form>
</body>
</html>


