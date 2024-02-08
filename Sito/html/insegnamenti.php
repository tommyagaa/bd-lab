<?php
function inserisciInsegnamento($db, $codice_insegnamento, $cdl_codice, $nome, $anno, $descrizione,$email_docente) {
    $verificaCorsoQuery = "SELECT 1 FROM progetto.corso_di_laurea WHERE codice = $1";
    $verificaCorsoResult = pg_query_params($db, $verificaCorsoQuery, array($cdl_codice));

    if (!$verificaCorsoResult || pg_num_rows($verificaCorsoResult) == 0) {
        return false;
    }

    $livelloCorsoQuery = "SELECT livello FROM progetto.corso_di_laurea WHERE codice = $1";
    $livelloCorsoResult = pg_query_params($db, $livelloCorsoQuery, array($cdl_codice));

    if (!$livelloCorsoResult) {
        return false;
    }
    $livello = pg_fetch_assoc($livelloCorsoResult)['livello'];
    $livello = intval($livello);

    // Verifica se l'anno è valido in base al livello
    if (($livello <= 3 && $anno >= 1 && $anno <= 3) || ($livello >= 4 && $anno >= 4 && $anno <= 5)) {
        // Inserisce l'insegnamento con l'email del docente
    $query = "INSERT INTO progetto.insegnamenti(codice, cdl_codice, nome, anno, descrizione, email_docente) VALUES ($1, $2, $3, $4, $5, $6)";
    return pg_query_params($db, $query, array($codice_insegnamento, $cdl_codice, $nome, $anno, $descrizione, $email_docente));
    } else {
        return false;
    }
}


