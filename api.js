function playAudioShowWord(angielskieSlowko) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "API.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4) {
      console.log("Status: " + xhr.status);

      if (xhr.status == 200) {
        // Sprawdź, czy odpowiedź to Blob (plik audio)
        if (xhr.response) {
          const audioUrl = URL.createObjectURL(xhr.response); // Tworzymy URL dla pliku audio
          const audio = new Audio(audioUrl);
          audio.play();
          console.log("Audio zostało odtworzone.");
        } else {
          console.error("Błąd: Odpowiedź nie zawiera pliku audio.");
        }
      } else {
        console.error("Błąd: Serwer zwrócił status " + xhr.status);
      }
    }
  };

  xhr.responseType = "blob"; // Oczekujemy odpowiedzi jako Blob (plik audio)
  xhr.send("data=" + encodeURIComponent(angielskieSlowko)); // Wysyłanie danych
}
