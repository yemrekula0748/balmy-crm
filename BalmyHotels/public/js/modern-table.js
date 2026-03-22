/**
 * modernTable — Lightweight client-side search + pagination.
 * Replaces DataTables for simple use cases.
 *
 * Usage: modernTable('myTableId', { pageLength: 25 });
 *
 * Required DOM elements (by id convention):
 *   #myTableId-search  → search <input>
 *   #myTableId-len     → page-length <select>
 *   #myTableId-info    → info text element (e.g. <small>)
 *   #myTableId-pagin   → <ul class="pagination"> container
 */
function modernTable(tableId, opts) {
    opts = Object.assign({ pageLength: 25 }, opts || {});

    var table = document.getElementById(tableId);
    if (!table) return;

    var allRows  = Array.from(table.querySelectorAll('tbody tr'));
    // The empty/placeholder row is the last one with a colspan td
    var emptyRow = null;
    for (var i = allRows.length - 1; i >= 0; i--) {
        if (allRows[i].querySelector('td[colspan]')) { emptyRow = allRows[i]; break; }
    }
    var dataRows = allRows.filter(function (r) { return r !== emptyRow; });

    var searchEl = document.getElementById(tableId + '-search');
    var lenEl    = document.getElementById(tableId + '-len');
    var infoEl   = document.getElementById(tableId + '-info');
    var paginEl  = document.getElementById(tableId + '-pagin');

    var pageLen  = opts.pageLength;
    var page     = 1;

    function getFiltered() {
        var q = (searchEl ? searchEl.value : '').toLowerCase().trim();
        if (!q) return dataRows.slice();
        return dataRows.filter(function (r) {
            return r.textContent.toLowerCase().indexOf(q) !== -1;
        });
    }

    function render() {
        var filtered = getFiltered();
        var total    = filtered.length;
        var pages    = Math.max(1, Math.ceil(total / pageLen));
        if (page > pages) page = pages;

        var start = (page - 1) * pageLen;
        var end   = Math.min(start + pageLen, total);

        dataRows.forEach(function (r) { r.style.display = 'none'; });
        if (emptyRow) emptyRow.style.display = total === 0 ? '' : 'none';
        filtered.slice(start, end).forEach(function (r) { r.style.display = ''; });

        if (infoEl) {
            infoEl.textContent = total
                ? (start + 1) + '–' + end + ' / ' + total + ' kayıt'
                : '0 kayıt';
        }

        if (!paginEl) return;
        paginEl.innerHTML = '';
        if (pages <= 1) return;

        function mkLi(html, pg, disabled, active) {
            var li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            var a = document.createElement('a');
            a.className = 'page-link';
            a.innerHTML = html;
            a.href = '#';
            if (!disabled && !active) {
                (function (p) {
                    a.addEventListener('click', function (e) {
                        e.preventDefault();
                        page = p;
                        render();
                    });
                })(pg);
            }
            li.appendChild(a);
            return li;
        }

        paginEl.appendChild(mkLi('&#8249;', page - 1, page === 1, false));

        var s = Math.max(1, page - 2);
        var e = Math.min(pages, page + 2);
        if (e - s < 4) {
            if (s === 1) e = Math.min(pages, 5);
            else         s = Math.max(1, e - 4);
        }
        if (s > 1) {
            paginEl.appendChild(mkLi('1', 1, false, false));
            if (s > 2) paginEl.appendChild(mkLi('&hellip;', 0, true, false));
        }
        for (var p2 = s; p2 <= e; p2++) {
            paginEl.appendChild(mkLi(p2, p2, false, p2 === page));
        }
        if (e < pages) {
            if (e < pages - 1) paginEl.appendChild(mkLi('&hellip;', 0, true, false));
            paginEl.appendChild(mkLi(pages, pages, false, false));
        }
        paginEl.appendChild(mkLi('&#8250;', page + 1, page === pages, false));
    }

    if (searchEl) searchEl.addEventListener('input',  function () { page = 1; render(); });
    if (lenEl)    lenEl.addEventListener('change',    function () { pageLen = +lenEl.value; page = 1; render(); });

    render();
}
