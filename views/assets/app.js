/* Template Projet Web — contrôle et validation des saisies (côté navigateur)
   Utilisé par la page de connexion/inscription et par le formulaire du Back Office.
   Règles déclarées dans le HTML : data-rule="required | email | age | password | optional". */
(function () {
  'use strict';

  var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  // Règles déclarées dans le HTML : data-rule="required | email | age | name"
  function check(input) {
    var rules = (input.getAttribute('data-rule') || '').split(' ');
    var v = input.value.trim();
    if (rules.indexOf('password') !== -1) v = input.value; // le mot de passe n'est pas "trimmé"

    if (rules.indexOf('optional') !== -1 && v === '') return '';
    if (v === '') return 'Ce champ est obligatoire.';
    if (rules.indexOf('email') !== -1 && !EMAIL_RE.test(v)) return 'Veuillez saisir une adresse e-mail valide.';
    if (rules.indexOf('age') !== -1) {
      var n = Number(v);
      if (!/^\d+$/.test(v) || n < 1 || n > 120) return 'L\'âge doit être un nombre entre 1 et 120.';
    }
    return '';
  }

  function show(input, message) {
    var group = input.closest('.form-group, label');
    if (!group) return;
    var box = group.querySelector('.field-error');
    group.classList.toggle('has-error', message !== '');
    if (box) box.textContent = message;
  }

  document.querySelectorAll('form[data-validate]').forEach(function (form) {
    var fields = form.querySelectorAll('[data-rule]');

    fields.forEach(function (input) {
      input.addEventListener('blur', function () { show(input, check(input)); });
      input.addEventListener('input', function () {
        if (input.closest('.form-group, label').classList.contains('has-error')) show(input, check(input));
      });
    });

    form.addEventListener('submit', function (e) {
      var firstBad = null;
      fields.forEach(function (input) {
        var msg = check(input);
        show(input, msg);
        if (msg && !firstBad) firstBad = input;
      });
      if (firstBad) {
        e.preventDefault();
        firstBad.focus();
      }
    });
  });
})();
