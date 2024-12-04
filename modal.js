function openModal(event) {
  event.preventDefault();
  document.getElementById("sessionModal").style.display = "block";
}

window.onclick = function (event) {
  if (event.target == document.getElementById("sessionModal")) {
    document.getElementById("sessionModal").style.display = "none";
  }
};
