<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica che il form sia stato inviato
    if (isset($_POST['voto'])) {
        $db = pg_connect("host=pgsql user=tomas password=unimipgsql dbname=progetto");

        $codice_appello = $_POST['codice_appello'];
        $studente = $_POST['studente'];
        $voto = $_POST['voto'];

        // Esegue la query per inserire la valutazione nel database
        $queryInserisciValutazione = "INSERT INTO progetto.esame (voto, codice_cdl, codice_ins, data_svolgimento, studente) 
        VALUES ($1, (SELECT cdl_codice FROM progetto.studente WHERE email = $2 LIMIT 1), 
                (SELECT id FROM progetto.appelli WHERE id = $3 LIMIT 1), 
                (SELECT data_svolgimento FROM progetto.appelli WHERE id = $3 LIMIT 1), $4)";

$resultInserisciValutazione = pg_query_params($db, $queryInserisciValutazione, array($voto, $studente, $codice_appello, $studente));


        if (!$resultInserisciValutazione) {
            die("Errore nell'inserimento della valutazione: " . pg_last_error($db));
        }

        echo "Valutazione inserita con successo.";

if (!$resultInserisciValutazione) {
    die("Errore nell'inserimento della valutazione: " . pg_last_error($db));
}


// Rimuove lo studente dalle iscrizioni
$queryRimuoviIscrizione = "UPDATE progetto.appelli SET iscritto = REPLACE(iscritto, $1, '') WHERE id = $2";
$resultRimuoviIscrizione = pg_query_params($db, $queryRimuoviIscrizione, array($studente, $codice_appello));

if (!$resultRimuoviIscrizione) {
    die("Errore nella rimozione dell'iscrizione: " . pg_last_error($db));
}        
}
    
}
$queryInserisciMemoriaEsame = "INSERT INTO progetto.memoria_esame (voto, nome, codice_ins) 
                                VALUES ($1, (SELECT studente FROM progetto.esame WHERE studente = $2 LIMIT 1), $3)";
$resultInserisciMemoriaEsame = pg_query_params($db, $queryInserisciMemoriaEsame, array($voto, $studente, $codice_appello));



echo '<br><a href="Docente.php">Torna alla Dashboard</a>';
?>
