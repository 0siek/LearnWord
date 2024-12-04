<?php
// index.php
session_start();
require 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Nauka Słówek</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="./favico.jpg">
    <link rel="stylesheet" href="style/style.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="container">


        <div class="menu">
            <?php if (isset($_SESSION['user_id'])): ?>


            <a class="menu-button" href="learnWord.php"><button>Nauka</button></a>
            <a class="menu-button" href="testWord.php"><button>Test</button></a>
            <a class="menu-button" href="addWord.php"><button>Dodaj słówka</button></a>
            <a class="menu-button" href="showWord.php"><button>Pokaż słówka</button></a>
            <a class="menu-button" href="logout.php"><button>Wyloguj się</button></a>
            <?php else: ?>
            <a class="menu-button" href="login.php"><button>Logowanie</button></a>
            <a class="menu-button" href="register.php"><button>Rejestracja</button></a>
            <?php endif;?>
        </div>
    </div>
</body>

</html>