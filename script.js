function toggleTableVisibility() {
  const tableSection = document.querySelector(".left");
  const rightSection = document.querySelector(".right");
  const toggleButton = document.getElementById("toggle-button");

  if (window.innerWidth > 768) {
    if (tableSection.style.display === "none") {
      tableSection.style.display = "block";
      rightSection.style.width = "48%";
      rightSection.style.margin = "0";
      toggleButton.textContent = "Ukryj tabelkę";
    } else {
      tableSection.style.display = "none";
      rightSection.style.width = "80%";
      rightSection.style.margin = "auto";
      toggleButton.textContent = "Pokaż tabelkę";
    }
  } else {
    tableSection.style.display =
      tableSection.style.display === "none" ? "block" : "none";
    toggleButton.textContent =
      tableSection.style.display === "none" ? "Pokaż tabelkę" : "Ukryj tabelkę";
  }
}
