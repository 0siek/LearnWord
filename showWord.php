<?php
// showWord.php
session_start();
include 'API.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';

$userId = $_SESSION['user_id'];
$message = "";
$php_errormsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_table'])) {
    $displayNameToDelete = $_POST['delete_table']; // "test1"

    // Tworzenie pełnej nazwy tabeli na podstawie nazwy użytkownika
    $userPrefix = $_SESSION['username'] . "_"; // np. "Patryk_"
    $fullTableName = $userPrefix . $displayNameToDelete; // np. "Patryk_test1"

    try {
        // Bezpieczne usuwanie tabeli bez przedrostka schematu
        $stmt = $pdo->prepare("DROP TABLE IF EXISTS `$fullTableName`");
        $stmt->execute();

        // Usuwanie wpisu z tabeli user_tables
        $stmt = $pdo->prepare("DELETE FROM user_tables WHERE table_name = ? AND user_id = ?");
        $stmt->execute([$fullTableName, $userId]);

        $message = "Tabela '$displayNameToDelete' została usunięta.";
    } catch (PDOException $e) {
        $message = "Błąd podczas usuwania tabeli '$displayNameToDelete': " . htmlspecialchars($e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Lista Tabel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="./favico.jpg">
    <link rel="stylesheet" href="style/style.css?v=<?php echo time(); ?>">
    <script src="./script.js" defer></script>
    <script src="./api.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body>
    <div class="header">
        <a href="index.php"><button>Menu główne</button></a>
        <button onclick="playAudioShowWord('cos')">fsdsdf</button>
        <button id="toggle-button" onclick="toggleTableVisibility()">Ukryj tabelkę</button>
    </div>

    <h1 class="tabele">Twoje tabele w bazie danych</h1>

    <div class="list">
        <div class="left">
            <h2>Usuń tabelę</h2>
            <div id="table-section">
                <?php
try {
    $stmt = $pdo->prepare("SELECT table_name, SUBSTRING_INDEX(table_name, '_', -1) AS display_name FROM user_tables WHERE user_id = ?");
    $stmt->execute([$userId]);
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($tables) {
        echo "<table border='1'>";
        echo "<tr><th>Nazwa tabeli</th><th>Akcje</th></tr>";
        foreach ($tables as $table) {
            $safeTableName = htmlspecialchars($table['display_name']);
            echo "<tr>
                                <td>{$safeTableName}</td>
                                <td>
                                    <form class='usun-button' action='' method='POST' onsubmit=\"return confirm('Czy na pewno chcesz usunąć tę tabelę?');\">
                                        <input type='hidden' name='delete_table' value='{$safeTableName}'>
                                        <button type='submit'>Usuń</button>
                                    </form>
                                </td>
                              </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Brak tabel do usunięcia.</p>";
    }
} catch (PDOException $e) {
    echo "<p>Błąd podczas pobierania tabel: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
            </div>



        </div>


        <div class="right">
            <form method="GET" action="">
                <label for="name">Wybierz tabelę:</label>
                <select name="name" id="name" required>
                    <?php
try {
    $stmt = $pdo->prepare("SELECT table_name, SUBSTRING_INDEX(table_name, '_', -1) AS display_name FROM user_tables WHERE user_id = ?");
    $stmt->execute([$userId]);
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($tables) {
        foreach ($tables as $table) {
            $safeTableName = htmlspecialchars($table['display_name']);
            echo "<option value='$safeTableName'>$safeTableName</option>";
        }
    } else {
        echo "<option value=''>Brak tabel</option>";
    }
} catch (PDOException $e) {
    echo "<option value=''>Błąd podczas pobierania tabel</option>";
}
?>
                </select>
                <button type="submit">Wyświetl</button>
            </form>
            <?php
if (isset($_GET['name'])) {
    $displayName = $_GET['name'];
    $stmt = $pdo->prepare("SELECT table_name FROM user_tables WHERE SUBSTRING_INDEX(table_name, '_', -1) = ? AND user_id = ?");
    $stmt->execute([$displayName, $userId]);
    $tableRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tableRow) {
        $tableName = $tableRow['table_name'];
        try {
            $sql = "SELECT polskie_slowko, angielskie_slowko FROM $tableName";
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<h2>Tabela '$displayName'</h2>";

            if ($results) {
                echo "<table border='1'>";
                echo "<tr><th>ID</th><th>Polskie Słówko</th><th>Angielskie Słówko</th></tr>";

                $id = 1;
                foreach ($results as $row) {
                    $angielskieSlowko = htmlspecialchars($row['angielskie_slowko']);
                    echo "<tr>";
                    echo "<td>$id</td>";
                    echo "<td>" . htmlspecialchars($row['polskie_slowko']) . "</td>";
                    echo "<td>" . $angielskieSlowko . "<i class='bi bi-volume-up-fill' onclick='playAudioShowWord(\"$angielskieSlowko\")'></i>" . "</td>";

                    echo "</tr>";
                    $id++;
                }
                echo "</table>";
            } else {
                echo "<p>Brak danych w tabeli '$displayName'.</p>";
            }
        } catch (PDOException $e) {
            $php_errormsg = "Błąd: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $php_errormsg = "Nie masz dostępu do tej tabeli.";
    }
}
?>
        </div>



    </div>




    <?php if (!empty($message)): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif;?>
    <?php if (!empty($php_errormsg)): ?>
    <div class="error_message"><?php echo htmlspecialchars($php_errormsg); ?></div>
    <?php endif;?>
</body>

</html>