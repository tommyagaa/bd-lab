<?php
session_start();

include_once 'database.php';
$db = pg_connect("host=pgsql user=tomas password=unimipgsql dbname=progetto");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION["email"] ?? "";
    $oldPassword = $_POST["old_password"] ?? "";
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    $ruolo = $_SESSION["ruolo"] ?? "";

    // Verifica che la vecchia password sia corretta
    $query = "SELECT * FROM progetto.$ruolo WHERE email = $1 AND password = $2";
    $result = pg_query_params($db, $query, array($email, md5($oldPassword)));

    $row = pg_fetch_assoc($result);
    
    // Verifica che le nuove password coincidano
    if ($newPassword !== $confirmPassword) {
        echo "Le nuove password non coincidono";
        exit();
    }


    // Aggiorna la password nel database
    $updateQuery = "UPDATE progetto.$ruolo SET password = $1 WHERE email = $2";
    $updateResult = pg_query_params($db, $updateQuery, array($newPassword, $email));

    if ($updateResult) {
        header('Password cambiata');
        header('Location: index.html');
       
    } else {
        echo "Errore nell'aggiornamento della password";
    }
   
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambia Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
            background-color: cadetblue;
        }

        h1 {
            color: black;
            font-family: cursive;
        }

        form {
            width: 300px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 10px;
            color: black;
        }

        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4caf50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: black;
        }

        .success-message {
            color: red;
            margin-top: 10px;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Nuova Password</h1>
    <form action="newPassword.php" method="post">
        <label for="old_password">Vecchia Password:</label>
        <input type="password" id="old_password" name="old_password" required>
        <br>
        <label for="new_password">Nuova Password:</label>
        <input type="password" id="new_password" name="new_password" required>
        <br>
        <label for="confirm_password">Ripeti Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <br>
        <div class="error-message" id="error-message"></div>
        <div class="success-message" id="success-message"></div>
        <br>
        <input type="submit" value="Cambia Password">
    </form>
</body>
</html>
