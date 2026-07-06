/**
 * PSSP IMPACT+ — Tables dynamiques
 * Améliore automatiquement les tableaux de listing :
 *  - recherche instantanée (filtre les lignes affichées)
 *  - tri par clic sur les en-têtes (numérique, dates jj/mm/aaaa, texte)
 *  - compteur de lignes
 *
 * Mode serveur : <table data-server-search> — la recherche interroge le
 * serveur (paramètre ?search=) et porte donc sur TOUTES les pages, pas
 * seulement la page affichée. Ajouter data-total="{{ $items->total() }}"
 * pour afficher le nombre total de résultats.
 *
 * Opt-out par table : <table data-no-dt>. Les tables gérées par
 * simple-datatables (classe .datatable) sont ignorées.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var tables = document.querySelectorAll('#main table.table:not([data-no-dt]):not(.datatable)');
        tables.forEach(enhance);
    });

    function enhance(table) {
        if (!table.tHead || !table.tBodies.length) return;

        var tbody = table.tBodies[0];
        var dataRows = getDataRows(tbody);
        var serverMode = table.hasAttribute('data-server-search');

        // Tri sur les en-têtes (dès qu'il y a au moins 2 lignes de données)
        if (dataRows.length > 1) {
            initSort(table, tbody);
        }

        if (serverMode) {
            // Recherche serveur : toujours affichée (même page vide, pour
            // pouvoir modifier ou effacer un filtre en cours)
            initServerToolbar(table, tbody);
        } else if (dataRows.length > 3) {
            initToolbar(table, tbody);
        }
    }

    /* Lignes de données : on écarte les lignes "aucun résultat" (td unique avec colspan) */
    function getDataRows(tbody) {
        return Array.prototype.filter.call(tbody.rows, function (tr) {
            return !(tr.cells.length === 1 && tr.cells[0].colSpan > 1);
        });
    }

    /* ---------------------------------- Recherche côté serveur ----- */

    function initServerToolbar(table, tbody) {
        var parts = buildToolbar(table, 'Rechercher (toutes les pages)...');
        var input = parts.input;
        var count = parts.count;

        var params = new URLSearchParams(window.location.search);
        var current = params.get('search') || '';
        input.value = current;

        var total = table.getAttribute('data-total');
        count.textContent = (total !== null ? total + ' résultat(s)' : getDataRows(tbody).length + ' ligne(s)')
            + (current ? ' pour « ' + current + ' »' : '');

        // Focus restitué après rechargement si une recherche est active
        if (current && document.activeElement === document.body) {
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);
        }

        var timer = null;
        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                var term = input.value.trim();
                if (term === current) return;

                var url = new URL(window.location.href);
                if (term) {
                    url.searchParams.set('search', term);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.delete('page'); // repartir de la page 1
                window.location.assign(url.toString());
            }, 550);
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(timer);
                var url = new URL(window.location.href);
                var term = input.value.trim();
                if (term) { url.searchParams.set('search', term); } else { url.searchParams.delete('search'); }
                url.searchParams.delete('page');
                window.location.assign(url.toString());
            }
        });
    }

    /* ------------------------------------------------ Recherche ---- */

    function buildToolbar(table, placeholder) {
        var toolbar = document.createElement('div');
        toolbar.className = 'dt-toolbar';

        var search = document.createElement('div');
        search.className = 'dt-search';
        search.innerHTML = '<i class="bi bi-search"></i>';

        var input = document.createElement('input');
        input.type = 'search';
        input.placeholder = placeholder;
        input.setAttribute('aria-label', 'Rechercher dans le tableau');
        search.appendChild(input);

        var count = document.createElement('span');
        count.className = 'dt-count';

        toolbar.appendChild(search);
        toolbar.appendChild(count);
        var host = table.closest('.table-responsive') || table;
        host.parentNode.insertBefore(toolbar, host);

        return { input: input, count: count };
    }

    function initToolbar(table, tbody) {
        var parts = buildToolbar(table, 'Rechercher dans ce tableau...');
        var input = parts.input;
        var count = parts.count;

        function refresh() {
            var term = input.value.trim().toLowerCase();
            var rows = getDataRows(tbody);
            var visible = 0;

            rows.forEach(function (tr) {
                var match = !term || tr.textContent.toLowerCase().indexOf(term) !== -1;
                tr.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            count.textContent = term
                ? visible + ' / ' + rows.length + ' ligne(s)'
                : rows.length + ' ligne(s) sur cette page';

            toggleNoResult(table, tbody, visible === 0 && term !== '');
        }

        input.addEventListener('input', refresh);
        refresh();
    }

    function toggleNoResult(table, tbody, show) {
        var existing = tbody.querySelector('.dt-empty-message');
        if (show && !existing) {
            var tr = document.createElement('tr');
            tr.className = 'dt-empty-message';
            var td = document.createElement('td');
            td.colSpan = table.tHead.rows[0].cells.length;
            td.textContent = 'Aucun résultat pour cette recherche sur la page courante.';
            tr.appendChild(td);
            tbody.appendChild(tr);
        } else if (!show && existing) {
            existing.remove();
        }
    }

    /* ------------------------------------------------ Tri ---------- */

    function initSort(table, tbody) {
        var headers = table.tHead.rows[0].cells;

        Array.prototype.forEach.call(headers, function (th, index) {
            var label = th.textContent.trim().toLowerCase();
            if (th.hasAttribute('data-no-sort') || label === 'actions' || label === 'action' || label === '') {
                return;
            }

            th.classList.add('dt-sortable');
            th.addEventListener('click', function () {
                var dir = th.classList.contains('asc') ? 'desc' : 'asc';

                Array.prototype.forEach.call(headers, function (h) {
                    h.classList.remove('asc', 'desc');
                });
                th.classList.add(dir);

                sortRows(tbody, index, dir);
            });
        });
    }

    function sortRows(tbody, index, dir) {
        var rows = getDataRows(tbody);
        var factor = dir === 'asc' ? 1 : -1;

        rows.sort(function (a, b) {
            var va = cellValue(a, index);
            var vb = cellValue(b, index);

            var na = parseNumeric(va);
            var nb = parseNumeric(vb);
            if (na !== null && nb !== null) return (na - nb) * factor;

            var da = parseDate(va);
            var db = parseDate(vb);
            if (da !== null && db !== null) return (da - db) * factor;

            return va.localeCompare(vb, 'fr', { sensitivity: 'base', numeric: true }) * factor;
        });

        rows.forEach(function (tr) { tbody.appendChild(tr); });

        // Garder la ligne "aucun résultat" en fin de tableau
        var empty = tbody.querySelector('.dt-empty-message');
        if (empty) tbody.appendChild(empty);
    }

    function cellValue(tr, index) {
        var cell = tr.cells[index];
        return cell ? cell.textContent.trim() : '';
    }

    /* "12 345,67 kg" -> 12345.67 */
    function parseNumeric(text) {
        var cleaned = text.replace(/[\s ]/g, '').replace(/[^0-9,.-]/g, '').replace(',', '.');
        if (cleaned === '' || cleaned === '-' || cleaned === '.') return null;
        var n = parseFloat(cleaned);
        if (isNaN(n)) return null;
        // Refuser si le texte contient trop de caractères non numériques (ex. codes)
        var digits = text.replace(/[^0-9]/g, '').length;
        return digits > 0 && digits >= (text.replace(/[\s ]/g, '').length / 2) ? n : null;
    }

    /* jj/mm/aaaa ou jj/mm/aaaa hh:mm -> timestamp */
    function parseDate(text) {
        var m = text.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?/);
        if (!m) return null;
        return new Date(m[3], m[2] - 1, m[1], m[4] || 0, m[5] || 0).getTime();
    }
})();
