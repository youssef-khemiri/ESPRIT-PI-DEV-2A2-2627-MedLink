var EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
var MESSAGE_OBLIGATOIRE = 'Ce champ est obligatoire.';
var MESSAGE_EMAIL = 'Veuillez saisir une adresse e-mail valide.';
var MESSAGE_AGE = "L'âge doit être un nombre entre 1 et 120.";

function lireRegles(champ) {
  var texteRegles = champ.getAttribute('data-rule');
  if (texteRegles === null) {
    texteRegles = '';
  }
  return texteRegles.split(' ');
}

function trouverGroupe(champ) {
  return champ.closest('.form-group, label');
}

function verifierChamp(champ) {
  var regles = lireRegles(champ);
  var valeur = champ.value.trim();
  if (regles.includes('password')) {
    valeur = champ.value;
  }
  if (regles.includes('optional') && valeur === '') {
    return '';
  }
  if (valeur === '') {
    return MESSAGE_OBLIGATOIRE;
  }
  if (regles.includes('email')) {
    var emailEstValide = EMAIL_REGEX.test(valeur);
    if (!emailEstValide) {
      return MESSAGE_EMAIL;
    }
  }
  if (regles.includes('age')) {
    var age = Number(valeur);
    var estUnEntier = /^\d+$/.test(valeur);

    if (!estUnEntier || age < 1 || age > 120) {
      return MESSAGE_AGE;
    }
  }
  return '';
}

function afficherErreur(champ, message) {
  var groupe = trouverGroupe(champ);
  if (groupe === null) {
    return;
  }
  var zoneErreur = groupe.querySelector('.field-error');
  if (message === '') {
    groupe.classList.remove('has-error');
  } else {
    groupe.classList.add('has-error');
  }
  if (zoneErreur !== null) {
    zoneErreur.textContent = message;
  }
}

function surveillerChamp(champ) {
  champ.addEventListener('blur', function () {
    var message = verifierChamp(champ);
    afficherErreur(champ, message);
  });
  champ.addEventListener('input', function () {
    var groupe = trouverGroupe(champ);
    var dejaEnErreur = false;
    if (groupe !== null && groupe.classList.contains('has-error')) {
      dejaEnErreur = true;
    }
    if (dejaEnErreur) {
      var message = verifierChamp(champ);
      afficherErreur(champ, message);
    }
  });
}

function surveillerFormulaire(formulaire) {
  var champs = formulaire.querySelectorAll('[data-rule]');
  for (var i = 0; i < champs.length; i++) {
    surveillerChamp(champs[i]);
  }
  formulaire.addEventListener('submit', function (evenement) {
    var premierChampInvalide = null;
    for (var j = 0; j < champs.length; j++) {
      var message = verifierChamp(champs[j]);
      afficherErreur(champs[j], message);
      if (message !== '' && premierChampInvalide === null) {
        premierChampInvalide = champs[j];
      }
    }
    if (premierChampInvalide !== null) {
      evenement.preventDefault();
      premierChampInvalide.focus();
    }
  });
}
var formulaires = document.querySelectorAll('form[data-validate]');
for (var k = 0; k < formulaires.length; k++) {
  surveillerFormulaire(formulaires[k]);
}
