<?php
// login.php
session_start();
require 'db_connect.php';
session_regenerate_id(true);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    if (empty($user) || empty($pass)) {
        $message = "Wszystkie pola są wymagane!";
    } else {
        // Pobranie użytkownika z bazy
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$user]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data && password_verify($pass, $user_data['password'])) {
            // Logowanie użytkownika
            session_regenerate_id(true); // Zabezpieczenie przed atakami typu session fixation
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user;
            header("Location: index.php");
            exit;
        } else {
            $message = "Nieprawidłowa nazwa użytkownika lub hasło!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
    <link rel="stylesheet" href="style/style.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="container login-container">
        <h1>Logowanie</h1>
        <form method="POST" action="login.php" class="form-login">
            <label for="username">Nazwa użytkownika:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Hasło:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Zaloguj się</button>
        </form>
        <?php if ($message): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif;?>
        <p>Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
    </div>
</body>

</html>