<?php
session_start();

// Verifica se l'utente è autenticato come segreteria
if (!isset($_SESSION["ruolo"]) || ($_SESSION["ruolo"] !== "segreteria")) {
    // Messaggio di errore e reindirizzamento alla pagina di login
    echo "Accesso non consentito. Effettua il login come segreteria.";
    exit();
}

include_once 'database.php';
$db = pg_connect("host=pgsql user=tomas password=unimipgsql dbname=progetto");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $email_docente = $_POST["email_docente"] ?? "";

    // Query per eliminare il docente
    $queryEliminaDocente = "DELETE FROM progetto.docente WHERE email = $1";
    $resultEliminaDocente = pg_query_params($db, $queryEliminaDocente, array($email_docente));

    if ($resultEliminaDocente) {
        echo "Docente eliminato con successo";
    } else {
        echo "Errore durante l'eliminazione del docente";
        echo pg_last_error($db);
    }
}
echo '<br><a href="Segreteria.php">Torna alla Segreteria</a>';
?>

