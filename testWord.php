<?php
// testWord.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';

$userId = $_SESSION['user_id'];
$username = $_SESSION['username']; // Nazwa użytkownika, np. "Patryk"
$message = null;
$finished = false;

// Generowanie tokenu CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Rozpoczęcie nowego quizu lub obsługa formularzy
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Weryfikacja tokenu CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Nieprawidłowy token CSRF");
    }

    // Obsługa przycisku 'restart'
    if (isset($_POST['restart'])) {
        unset($_SESSION['quiz_words'], $_SESSION['quiz_index'], $_SESSION['correct_answers'], $_SESSION['incorrect_answers']);
        header("Location: testWord.php");
        exit;
    }

    // Obsługa przycisku 'cancel' z modalnego dialogu
    if (isset($_POST['cancel'])) {
        unset($_SESSION['quiz_words'], $_SESSION['quiz_index'], $_SESSION['correct_answers'], $_SESSION['incorrect_answers']);
        header("Location: index.php");
        exit;
    }

    // Obsługa przycisku 'save_session' z modalnego dialogu
    if (isset($_POST['save_session'])) {
        header("Location: index.php");
        exit;
    }

    // Obsługa wyboru tabeli do quizu
    if (isset($_POST['table_name'])) {
        $selectedTable = $_POST['table_name'];
        $fullTableName = $username . "_" . $selectedTable;

        // Sprawdzenie, czy tabela należy do użytkownika
        $stmt = $pdo->prepare("SELECT id FROM user_tables WHERE table_name = ? AND user_id = ?");
        $stmt->execute([$fullTableName, $userId]);

        if ($stmt->fetch()) {
            $query = $pdo->prepare("SELECT * FROM `$fullTableName`");
            $query->execute();
            $words = $query->fetchAll(PDO::FETCH_ASSOC);

            if ($words) {
                shuffle($words); // Losowe ustawienie słówek
                $_SESSION['quiz_words'] = $words;
                $_SESSION['quiz_index'] = 0;
                $_SESSION['correct_answers'] = [];
                $_SESSION['incorrect_answers'] = [];
            } else {
                $message = "Wybrana tabela jest pusta!";
            }
        } else {
            $message = "Nie masz dostępu do tej tabeli.";
        }
    }

    // Obsługa przycisku 'submit_answer'
    if (isset($_POST['submit_answer'])) {
        if (!isset($_SESSION['quiz_words'])) {
            $message = "Nie rozpoczęto quizu.";
        } else {
            $index = $_SESSION['quiz_index'];
            $currentWord = $_SESSION['quiz_words'][$index];

            $userAnswer = trim(strtolower($_POST['answer']));
            $correctAnswer = trim(strtolower($currentWord['angielskie_slowko']));

            if ($userAnswer === $correctAnswer) {
                $_SESSION['correct_answers'][] = $currentWord;
            } else {
                $_SESSION['incorrect_answers'][] = $currentWord;
            }

            $_SESSION['quiz_index']++;

            if ($_SESSION['quiz_index'] >= count($_SESSION['quiz_words'])) {
                $finished = true;
            }
        }
    }
}

// Pobieranie dostępnych tabel użytkownika
$stmt = $pdo->prepare("SELECT SUBSTRING_INDEX(table_name, '_', -1) AS table_name FROM user_tables WHERE user_id = ?");
$stmt->execute([$userId]);
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$quizWord = $_SESSION['quiz_words'][$_SESSION['quiz_index']] ?? null;
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprawdzian wiedzy</title>
    <link rel="icon" type="image/x-icon" href="./favico.jpg">
    <link rel="stylesheet" href="style/style.css?v=<?php echo time(); ?>">
    <script src="./modal.js"></script>
</head>

<body>
    <a href="index.php" onclick="openModal(event)"><button>Menu główne</button></a>
    <h1>Sprawdzian wiedzy</h1>

    <div id="sessionModal" class="modal">
        <div class="modal-content">
            <h2>Czy chcesz zapisać swoją sesję?</h2>
            <div class="modal-button-container">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token"
                        value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button class="modal-button ok-button" type="submit" name="save_session">Tak</button>
                </form>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token"
                        value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button class="modal-button cancel-button" type="submit" name="cancel">Nie</button>
                </form>
            </div>
        </div>
    </div>

    <div class="test">
        <?php if (!isset($_SESSION['quiz_words'])): ?>
        <!-- Wybór tabeli -->
        <form method="POST" action="" class="test-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <label for="table_name">Wybierz tabelę:</label>
            <select id="table_name" name="table_name" required>
                <?php
foreach ($tables as $table) {
    $safeTable = htmlspecialchars($table);
    echo "<option value=\"$safeTable\">$safeTable</option>";
}
?>
            </select>
            <button type="submit">Rozpocznij test</button>
        </form>
        <?php if ($message): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif;?>
        <?php elseif (isset($finished) && $finished): ?>
        <div class="wynik">
            <h2>Wyniki testu</h2>
            <p>Poprawne odpowiedzi: <?php echo count($_SESSION['correct_answers']); ?> /
                <?php echo count($_SESSION['quiz_words']); ?>
                (<?php echo count($_SESSION['quiz_words']) > 0 ? round((count($_SESSION['correct_answers']) / count($_SESSION['quiz_words'])) * 100, 2) : 0; ?>%)
            </p>

            <h3>Poprawne odpowiedzi:</h3>
            <ul>
                <?php foreach ($_SESSION['correct_answers'] as $word): ?>
                <li><?php echo htmlspecialchars($word['polskie_slowko']); ?> -
                    <?php echo htmlspecialchars($word['angielskie_slowko']); ?></li>
                <?php endforeach;?>
            </ul>

            <h3>Błędne odpowiedzi:</h3>
            <ul>
                <?php foreach ($_SESSION['incorrect_answers'] as $word): ?>
                <li><?php echo htmlspecialchars($word['polskie_slowko']); ?> -
                    <?php echo htmlspecialchars($word['angielskie_slowko']); ?></li>
                <?php endforeach;?>
            </ul>

            <form method="POST" action="" class="restart">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit" name="restart">Rozpocznij od nowa</button>
            </form>
        </div>
        <?php elseif ($quizWord): ?>
        <form method="POST" action="" class="test-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <p>Przetłumacz na angielski:
                <strong><?php echo htmlspecialchars($quizWord['polskie_slowko']); ?></strong>
            </p>
            <input type="text" name="answer" placeholder="Twoja odpowiedź" autocomplete="off">
            <button type="submit" name="submit_answer">Zatwierdź</button>
        </form>
        <?php endif;?>
    </div>
</body>

</html>