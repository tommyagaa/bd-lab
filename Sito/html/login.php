<?php
session_start();

include_once 'database.php';
$db = pg_connect("host=pgsql user=tomas password=unimipgsql dbname=progetto");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    $ruolo = $_POST["ruolo"] ?? "";

    $query = "SELECT * FROM progetto.$ruolo WHERE email = $1 AND password = $2";
    $result = pg_query_params($db, $query, array($email, $password));

    if ($result) {
        $row = pg_fetch_assoc($result);

        if ($row) {
            // Utente autenticato
            $_SESSION["email"] = $email;
            $_SESSION["ruolo"] = $ruolo;

            // Reindirizza in base al ruolo
            if ($ruolo == "segreteria") {
                header("Location: Segreteria.php");
                exit();
            } elseif ($ruolo == "docente") {
                header("Location: Docente.php");
                exit();
            } elseif ($ruolo == "studente") {
                header("Location: Studente.php");
                exit();
            }
        }
    }
    exit();
}
?>
