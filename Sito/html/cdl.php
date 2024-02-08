<?php
function inserisciCorsoDiLaurea($db, $codice, $livello, $nome){
    // Verifica se il codice è già presente nel database
    $query_check = "SELECT 1 FROM progetto.corso_di_laurea WHERE codice = $1";
    $result_check = pg_query_params($db, $query_check, array($codice));

    if (pg_num_rows($result_check) > 0) {
        return false;
    }
    // Inserisci il nuovo corso di laurea
    $query_insert = "INSERT INTO progetto.corso_di_laurea(codice, livello, nome) VALUES ($1, $2, $3)";
    return pg_query_params($db, $query_insert, array($codice, $livello, $nome));
}





