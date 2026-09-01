/**
 * PSSP IMPACT+ — Recherche dans les listes deroulantes
 *
 * Applique automatiquement select2 a tous les <select> de l'application qui
 * comportent assez d'options pour qu'un defilement devienne penible. Plus
 * besoin d'initialiser select2 vue par vue : tout nouvel ecran en herite.
 *
 * Reglage par champ :
 *   <select data-search>      force la recherche meme sur une liste courte
 *   <select data-no-search>   desactive la recherche sur ce champ
 *
 * Les listes courtes (statut, mode de paiement, niveau de log...) restent des
 * <select> natifs : sur mobile le selecteur natif est plus rapide.
 */
(function () {
    'use strict';

    // En dessous de ce nombre d'options, un select natif reste preferable.
    var SEUIL_RECHERCHE = 8;

    if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
        return; // select2 indisponible : on garde les selects natifs, rien ne casse
    }

    var $ = window.jQuery;

    $(function () {
        $('select').each(function () {
            var $select = $(this);

            if (!estEligible($select)) {
                return;
            }

            $select.select2({
                theme: 'bootstrap-5',
                language: 'fr',
                width: '100%',
                placeholder: placeholderDe($select),
                allowClear: false,
                // Sous 8 options select2 masque le champ de recherche : on force
                // son affichage des lors qu'on a decide d'activer le composant.
                minimumResultsForSearch: 0,
                dropdownParent: parentDropdown($select)
            });
        });
    });

    function estEligible($select) {
        var el = $select[0];

        if ($select.is('[data-no-search]') || $select.hasClass('select2-hidden-accessible')) {
            return false;
        }

        // Champs caches ou pilotes par un autre composant
        if (el.type === 'hidden' || $select.closest('.select2-container').length) {
            return false;
        }

        if ($select.is('[data-search]')) {
            return true;
        }

        return el.options.length >= SEUIL_RECHERCHE;
    }

    /**
     * Reprend le libelle de l'option vide ("-- Choisir un site --") comme
     * placeholder, sinon le <label> associe au champ.
     */
    function placeholderDe($select) {
        var premiere = $select[0].options[0];

        if (premiere && premiere.value === '' && premiere.text.trim() !== '') {
            return premiere.text.trim();
        }

        var id = $select.attr('id');
        if (id) {
            var label = document.querySelector('label[for="' + CSS.escape(id) + '"]');
            if (label) {
                return 'Rechercher : ' + label.textContent.trim().replace(/\s*\*$/, '');
            }
        }

        return 'Rechercher...';
    }

    /**
     * Dans une modale Bootstrap, le menu deroulant doit etre rattache a la
     * modale : sinon il s'affiche derriere elle et devient inutilisable.
     */
    function parentDropdown($select) {
        var modale = $select.closest('.modal');
        return modale.length ? modale : $(document.body);
    }
})();
