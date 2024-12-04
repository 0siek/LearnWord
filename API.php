<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['data'])) {
    $angielskieSlowko = $_POST['data'];

    // Ustaw nagłówki odpowiedzi na audio/mpeg
    header('Content-Type: audio/mpeg');
    header('Content-Disposition: inline; filename="output.mp3"');

    data($angielskieSlowko);

}

function data($data)
{
    require_once __DIR__ . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    if (!file_exists(__DIR__ . '/.env')) {
        die("Brak pliku .env.");
    }

    $apiKey = $_ENV['API_KEY'];
    $userId = $_ENV['API_USER'];

    $dataToSend = [
        'text' => $data,
        'voice_engine' => 'Play3.0',
        'voice' => 's3://voice-cloning-zero-shot/d9ff78ba-d016-47f6-b0ef-dd630f59414e/female-cs/manifest.json',
        'output_format' => 'mp3',
    ];

    $options = [
        'http' => [
            'header' => "X-USER-ID: $userId\r\n" .
            "AUTHORIZATION: $apiKey\r\n" .
            "Content-Type: application/json\r\n" .
            "Accept: audio/mpeg\r\n",
            'method' => 'POST',
            'content' => json_encode($dataToSend),
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents('https://api.play.ht/api/v2/tts/stream', false, $context);

    if ($response !== false) {
        // Zapisujemy odpowiedź jako plik MP3
        file_put_contents('output.mp3', $response);

        // Otwórz plik audio i przekaż go do odpowiedzi
        readfile('output.mp3');
    } else {
        echo "Błąd podczas generowania audio.";
        exit;
    }
}