document.addEventListener("DOMContentLoaded", function () {
  var form = document.querySelector("form.layout");
  if (!form) return;

  document.documentElement.lang = "fr";
  var translations = {"CV builder":"Generateur de CV","Build your professional CV":"Creez votre CV professionnel","Edit your CV":"Modifiez votre CV","My CVs":"Mes CV","Profile":"Profil","Without photo":"Sans photo","With photo":"Avec photo","Upload a professional photo":"Ajouter une photo professionnelle","Full name *":"Nom complet *","Professional title":"Titre professionnel","Phone":"Telephone","City / Address":"Ville / Adresse","Age":"Age","Professional summary":"Resume professionnel","Work experience":"Experiences professionnelles","Show your impact with roles, companies, dates, and responsibilities.":"Ajoutez vos postes, entreprises, dates et responsabilites.","Add role":"Ajouter une experience","Job title":"Poste occupe","Company":"Entreprise","Start date":"Date de debut","End date":"Date de fin","Key responsibilities and achievements":"Responsabilites et realisations","Education":"Formation","Add degrees, schools, and study dates.":"Ajoutez vos diplomes, etablissements et dates.","Add education":"Ajouter une formation","Degree or qualification":"Diplome ou qualification","School / university":"Ecole / Universite","Start year":"Annee de debut","End year":"Annee de fin","Skills":"Competences","Add skill":"Ajouter une competence","Skill":"Competence","Level":"Niveau","Languages":"Langues","Add language":"Ajouter une langue","Language":"Langue","Create my CV":"Creer mon CV","Save changes":"Enregistrer les modifications","Design":"Modele","Choose your template":"Choisissez votre modele","Each visual preview is a different CV layout. Your choice is saved with the CV.":"Chaque apercu represente une mise en page differente. Votre choix est enregistre avec le CV.","Basic":"Debutant","Intermediate":"Intermediaire","Advanced":"Avance","Expert":"Expert"};
  var walker = document.createTreeWalker(form, NodeFilter.SHOW_TEXT);
  var textNodes = [];
  while (walker.nextNode()) textNodes.push(walker.currentNode);
  textNodes.forEach(function (node) {
    var key = node.nodeValue.trim();
    if (translations[key]) node.nodeValue = node.nodeValue.replace(key, translations[key]);
  });
  document.querySelectorAll("input[placeholder], textarea[placeholder]").forEach(function (field) {
    field.placeholder = field.placeholder.replace("e.g. Front-end developer", "ex. Developpeur front-end").replace("A concise introduction highlighting your experience and strengths.", "Une courte presentation de votre experience et de vos points forts.").replace("Use one line per achievement.", "Utilisez une ligne par realisation.");
  });

  var photoBox = document.getElementById("photoBox");
  var photoInput = document.getElementById("photoInput");
  var photoLabel = document.getElementById("photoLabel");
  var withPhoto = document.getElementById("withPhoto");
  function togglePhotoBox() { photoBox.classList.toggle("is-hidden", !(withPhoto && withPhoto.checked)); }
  document.querySelectorAll('input[name="has_photo"]').forEach(function (radio) { radio.addEventListener("change", togglePhotoBox); });
  togglePhotoBox();

  photoInput.addEventListener("change", function () {
    var file = photoInput.files[0];
    if (!file) return;
    var oldImage = photoBox.querySelector("img");
    if (oldImage) oldImage.remove();
    var image = document.createElement("img");
    image.src = URL.createObjectURL(file);
    image.alt = "Profile photo preview";
    photoBox.appendChild(image);
    photoLabel.textContent = file.name;
  });

  function clearFields(row) {
    row.querySelectorAll("input, textarea, select").forEach(function (field) {
      if (field.tagName === "SELECT") field.selectedIndex = 1;
      else field.value = "";
    });
  }
  function updateNames(row, index) {
    row.querySelectorAll("[name]").forEach(function (field) { field.name = field.name.replace(/\[\d+\]/, "[" + index + "]"); });
  }
  document.querySelectorAll("[data-add-row]").forEach(function (button) {
    button.addEventListener("click", function () {
      var list = document.getElementById(button.dataset.addRow);
      if (!list || !list.firstElementChild) return;
      var row = list.firstElementChild.cloneNode(true);
      clearFields(row);
      updateNames(row, list.children.length);
      list.appendChild(row);
      var input = row.querySelector("input, textarea, select");
      if (input) input.focus();
    });
  });
  document.addEventListener("click", function (event) {
    var remove = event.target.closest(".js-remove-row");
    if (!remove) return;
    var row = remove.closest("[data-row]");
    var list = row && row.parentElement;
    if (!row || !list) return;
    if (list.children.length > 1) row.remove();
    else clearFields(row);
  });
  form.addEventListener("submit", function (event) {
    var name = document.getElementById("full_name");
    var email = document.getElementById("email");
    if (!name.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
      event.preventDefault();
      alert("Veuillez saisir votre nom complet et une adresse e-mail valide.");
    }
  });
});
