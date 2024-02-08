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

// Controlla se è stata inviata la matricola da eliminare
if(isset($_POST['matricola'])) {
    $matricolaDaEliminare = $_POST['matricola'];
    // Elimina lo studente dalla tabella studente
    $query = "DELETE FROM progetto.studente WHERE matricola = $1::integer";
    $result = pg_query_params($db, $query, array((int) $matricolaDaEliminare));
    
    
    
    if ($result) {
        echo "Studente eliminato con successo.";
    } else {
        echo "Errore durante l'eliminazione dello studente.";
    }
} else {
    echo "Matricola non fornita.";
}

pg_close($db);

echo '<br><a href="Segreteria.php">Torna alla Segreteria</a>';
?>



