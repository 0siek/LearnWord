<?php
// addWord.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';

$message = "";
$php_errormsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tableName = trim($_POST['table_name']);
    $wordsInput = trim($_POST['words_input']);
    $userId = $_SESSION['user_id'];

    // Pobranie nazwy użytkownika na podstawie ID
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $username = $stmt->fetchColumn();

    if (!$username) {
        $php_errormsg = "Nie udało się znaleźć nazwy użytkownika.";
    } else {
        if (!empty($tableName) && !empty($wordsInput)) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
                $php_errormsg = "Nazwa tabeli zawiera niedozwolone znaki!";
            } else {
                // Sprawdzenie czy nazwa tabeli jest unikalna
                $stmt = $pdo->prepare("SELECT id FROM user_tables WHERE table_name = ?");
                $stmt->execute([$tableName]);
                if ($stmt->fetch()) {
                    $php_errormsg = "Nazwa tabeli jest już zajęta!";
                } else {
                    // Procesowanie słów
                    $lines = explode("\n", $wordsInput);
                    $data = [];

                    foreach ($lines as $line) {
                        $pair = explode(' - ', $line);
                        if (count($pair) === 2) {
                            $data[] = [trim($pair[0]), trim($pair[1])];
                        }
                    }

                    if (!empty($data)) {
                        try {
                            // Tworzenie tabeli słówek
                            $createTableSQL = "
                            CREATE TABLE `" . $username . "_" . $tableName . "` (
                                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `polskie_slowko` VARCHAR(255) NOT NULL,
                                `angielskie_slowko` VARCHAR(255) NOT NULL
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
                            $pdo->exec($createTableSQL);

                            // Wstawianie danych do tabeli
                            $insertSQL = "INSERT INTO `" . $username . "_" . $tableName . "` (polskie_slowko, angielskie_slowko) VALUES (?, ?)";
                            $stmt = $pdo->prepare($insertSQL);

                            foreach ($data as $pair) {
                                $stmt->execute([$pair[0], $pair[1]]);
                            }

                            // Dodanie wpisu do user_tables
                            $stmt = $pdo->prepare("INSERT INTO user_tables (user_id, table_name) VALUES (?, ?)");
                            $stmt->execute([$userId, $username . "_" . $tableName]);

                            $message = "Tabela '" . $tableName . "' została utworzona i wypełniona " . count($data) . " słówkami!";
                        } catch (PDOException $e) {
                            $php_errormsg = "Błąd: " . htmlspecialchars($e->getMessage());
                        }
                    } else {
                        $php_errormsg = "Nie udało się przetworzyć wprowadzonego tekstu. Sprawdź format danych.";
                    }
                }
            }
        } else {
            $php_errormsg = "Wszystkie pola muszą być wypełnione!";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Tworzenie tabeli</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="./favico.jpg">
    <link rel="stylesheet" href="style/style.css?v=<?php echo time(); ?>">
</head>

<body>
    <a href="index.php"><button>Menu główne</button></a>

    <h1>Tworzenie nowej tabeli</h1>
    <div class="dodawanieslowek">
        <form class="formularz_dodawania" method="POST" action="">
            <label for="table_name">Nazwa tabeli:</label>
            <input type="text" id="table_name" name="table_name" required><br><br>

            <label for="words_input">Słówka (format: polskie - angielskie):</label>
            <textarea id="words_input" name="words_input" required
                placeholder="Wklej słówka w formacie: polskie - angielskie"></textarea><br><br>

            <button type="submit">Utwórz tabelę</button>
        </form>
    </div>
    <?php if (!empty($message)): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif;?>
    <?php if (!empty($php_errormsg)): ?>
    <div class="error_message"><?php echo htmlspecialchars($php_errormsg); ?></div>
    <?php endif;?>

</body>

</html>