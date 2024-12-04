<?php
// register.php
session_start();
require 'db_connect.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);
    $confirm_pass = trim($_POST['confirm_password']);

    if (empty($user) || empty($pass) || empty($confirm_pass)) {
        $message = "Wszystkie pola są wymagane!";
    } elseif ($pass !== $confirm_pass) {
        $message = "Hasła się nie zgadzają!";
    } else {
        // Sprawdzenie czy użytkownik już istnieje
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$user]);
        if ($stmt->fetch()) {
            $message = "Nazwa użytkownika jest już zajęta!";
        } else {
            // Hashowanie hasła
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            // Dodanie użytkownika do bazy
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$user, $hashed_pass])) {
                $message = "Rejestracja zakończona sukcesem! Możesz się zalogować.";
            } else {
                $message = "Błąd podczas rejestracji.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja</title>
    <link rel="stylesheet" href="style/style.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="container login-container">
        <h1>Rejestracja</h1>
        <form method="POST" action="register.php" class="form-login">
            <label for="username">Nazwa użytkownika:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Hasło:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Potwierdź hasło:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Zarejestruj się</button>
        </form>
        <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif;?>
        <p>Masz już konto? <a href="login.php">Zaloguj się</a></p>
    </div>
</body>

</html>