<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Management Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #9E9E9E; /* Classic Windows gray background */
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Header: title row ── */
        .header-title-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px 4px 10px;
            background: #F0F0F0;
            border-bottom: 1px solid #CECECE;
        }
        .header-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a1a;
        }
        .tag-badge {
            display: inline-flex;
            align-items: center;
            padding: 1px 8px;
            background: #E1EFFE;
            color: #1a56db;
            border: 1px solid #C3D9FB;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 500;
        }

        /* ── Toolbar: action row ── */
        .toolbar {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            background: #F7F7F7;
            border-bottom: 1px solid #DCDCDC;
        }

        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            font-size: 12px;
            font-family: "Segoe UI", sans-serif;
            background: #fff;
            border: 1px solid #C8C8C8;
            border-radius: 3px;
            cursor: pointer;
            color: #333;
            transition: background 0.1s, border-color 0.1s;
            white-space: nowrap;
        }
        .toolbar-btn:hover {
            background: #EBF3FF;
            border-color: #7EB4EA;
        }
        .toolbar-btn:active {
            background: #D6E9FF;
        }
        .toolbar-btn svg {
            width: 12px;
            height: 12px;
            color: #555;
            flex-shrink: 0;
        }

        .toolbar-sep {
            width: 1px;
            height: 18px;
            background: #D4D4D4;
            margin: 0 4px;
        }

        /* Search */
        .search-wrap {
            display: flex;
            align-items: center;
            position: relative;
        }
        .search-wrap svg {
            position: absolute;
            left: 6px;
            width: 12px;
            height: 12px;
            color: #999;
        }
        .search-input {
            padding: 3px 6px 3px 22px;
            font-size: 12px;
            font-family: "Segoe UI", sans-serif;
            border: 1px solid #C8C8C8;
            border-radius: 3px;
            width: 210px;
            background: #fff;
            color: #111;
            outline: none;
        }
        .search-input:focus {
            border-color: #7EB4EA;
            box-shadow: 0 0 0 2px #BDD8F522;
        }

        .showing-label {
            font-size: 11px;
            color: #555;
            white-space: nowrap;
        }
        .showing-label b { color: #111; font-weight: 600; }
        
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            font-size: 12px;
            table-layout: fixed; /* Keep columns from jumping */
        }
        
        .grid-table th {
            font-weight: normal;
            text-align: left;
            padding: 4px 6px;
            border-right: 1px solid #D4D4D4;
            border-bottom: 1px solid #D4D4D4;
            background-color: #417ac0ff;
            color: #ffffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 1px 0 #D4D4D4;
        }

        .grid-table td {
            padding: 2px 6px;
            border-right: 1px solid #D4D4D4;
            border-bottom: 1px solid #D4D4D4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #000;
            height: 22px;
        }

        /* The row number column */
        .grid-table th:first-child, .grid-table td:first-child {
            width: 2.5%;
            text-align: center;
            background-color: #417ac0ff; 
            border-right: 1px solid #D4D4D4;
            position: relative;
        }

        /* Selected row styling (classic windows blue) */
        .selected-row td {
            background-color: #ffffffff !important; 
            color: black !important;
        }
        .selected-row td:first-child {
            background-color: #497bd1ff !important;
            color: black !important;
        }

        /* Selected arrow indicator */
        .selected-row td:first-child::before {
            content: "▶";
            position: absolute;
            left: 4px;
            font-size: 9px;
            color: black;
            line-height: 18px;
        }

        /* Row number column — fixed narrow width */
        .col-Number   { width: 2.5%; }

        /* Proportional column widths — EPC is wider, all others equal */
        .col-epc      { width: 22%; }
        .col-type,
        .col-tid,
        .col-userdata,
        .col-ant,
        .col-rssi     { width: 9%; }
        
        .table-container {
            background-color: white;
            border: 1px solid #7A7A7A;
            overflow-y: auto;
            flex: 1; /* Fills the whole vertical space */
            width: 100%; /* Fills the whole horizontal space */
        }
    </style>
</head>
<body>
    <!-- Title row -->
    <div class="header-title-row">
        <span class="header-title">RFID Tag Reader</span>
        <span class="tag-badge" id="tag-badge">0 tags</span>
    </div>

    <!-- Action row -->
    <div class="toolbar">
        <div class="search-wrap">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="search-input" class="search-input" placeholder="Search EPC, antenna, RSSI...">
        </div>
        <div class="toolbar-sep"></div>
        <button id="btn-export" class="toolbar-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </button>
        <button id="btn-clear" class="toolbar-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18v3"/></svg>
            Clear
        </button>
        <div style="flex:1"></div>
        <span class="showing-label">Showing <b id="sb-showing">0</b> of <b id="sb-total">0</b></span>
    </div>

    <div class="table-container">
        <table class="grid-table" id="tags-table">
            <thead>
                <tr>
                    <th class="col-Number">No.</th>
                    <th class="col-type">Type</th>
                    <th class="col-epc">EPC</th>
                    <th class="col-tid">TID</th>
                    <th class="col-userdata">User Data</th>
                    <th class="col-ant">Ant1</th>
                    <th class="col-ant">Ant2</th>
                    <th class="col-ant">Ant3</th>
                    <th class="col-ant">Ant4</th>
                    <th class="col-rssi">RSSI</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <script type="module">
        let rowCount = 0;
        const tableBody = document.querySelector('#tags-table tbody');
        const rowMap = new Map();

        // Function to handle row selection highlighting
        function updateSelection() {
            const rows = tableBody.querySelectorAll('tr');
            rows.forEach((row, index) => {
                if (index === 0) {
                    row.classList.add('selected-row');
                } else {
                    row.classList.remove('selected-row');
                }
            });
        }

        const exportBtn = document.getElementById('btn-export');
        const clearBtn = document.getElementById('btn-clear');
        const searchInput = document.getElementById('search-input');

        function updateCounters() {
            const visible = tableBody.querySelectorAll('tr:not([style*="display: none"])').length;
            document.getElementById('sb-showing').textContent = visible;
            document.getElementById('sb-total').textContent   = rowCount;
            document.getElementById('tag-badge').textContent  = rowCount + (rowCount === 1 ? ' tag' : ' tags');
        }

        exportBtn.addEventListener('click', () => {
            let csv = [];
            let headers = [];
            document.querySelectorAll("#tags-table thead th").forEach((th, index) => {
                if(index > 0) headers.push(th.innerText);
            });
            csv.push(headers.join(","));

            let rows = document.querySelectorAll("#tags-table tbody tr");
            rows.forEach(row => {
                let rowData = [];
                row.querySelectorAll("td").forEach((td, index) => {
                    if(index > 0) rowData.push(td.innerText);
                });
                csv.push(rowData.join(","));
            });

            let csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
            let downloadLink = document.createElement("a");
            downloadLink.download = "rfid_data.csv";
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        });

        // Clear button logic (Refresh)
        clearBtn.addEventListener('click', () => {
            tableBody.innerHTML = '';
            rowCount = 0;
            rowMap.clear();
            updateCounters();
        });

        // Search logic
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if(text.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            updateCounters();
        });

        // Click to copy logic
        tableBody.addEventListener('click', async (e) => {
            const tr = e.target.closest('tr');
            if(!tr) return;
            // Get EPC which is the 3rd column (index 2)
            const epc = tr.children[2].innerText;
            if(epc) {
                try {
                    await navigator.clipboard.writeText(epc);
                    const originalBg = tr.style.backgroundColor;
                    tr.style.backgroundColor = '#dcfce3'; // light green feedback
                    setTimeout(() => {
                        tr.style.backgroundColor = originalBg;
                        updateSelection(); 
                    }, 300);
                } catch(err) {
                    console.error('Failed to copy', err);
                }
            }
        });

        async function loadExistingTags() {
            try {
                const response = await fetch('/api/tags');
                const data = await response.json();
                
                if (data.data) {
                    data.data.forEach(tag => {
                        rowCount++;
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${rowCount}</td>
                            <td>${(tag.protocol || '').toLowerCase()}</td>
                            <td>${tag.epc || ''}</td>
                            <td>${tag.tid || ''}</td>
                            <td>${tag.user_data || ''}</td>
                            <td>${tag.ant1 ?? 0}</td>
                            <td>${tag.ant2 ?? 0}</td>
                            <td>${tag.ant3 ?? 0}</td>
                            <td>${tag.ant4 ?? 0}</td>
                            <td>${tag.rssi || ''}</td>
                        `;
                        tableBody.appendChild(row);
                    });
                    updateCounters();
                    updateSelection();
                }
            } catch (error) {
                console.error('[RFID] Failed to load existing tags:', error);
            }
        }

        loadExistingTags();

        function subscribeToRfid() {
            if (!window.Echo) {
                console.error('[RFID] window.Echo is not available.');
                return;
            }

            window.Echo.channel('rfid.live')
                .listen('.tag.scanned', (e) => {
                    const epc = e.epc || '';

                    if (rowMap.has(epc)) {
                        const row = rowMap.get(epc);
                        const cells = row.querySelectorAll('td');
                        
                        cells[5].textContent  = e.ant1 ?? 0;
                        cells[6].textContent  = e.ant2 ?? 0;
                        cells[7].textContent  = e.ant3 ?? 0;
                        cells[8].textContent  = e.ant4 ?? 0;
                        cells[9].textContent  = e.rssi ?? '';

                        row.style.backgroundColor = '#fef9c3';
                        setTimeout(() => { row.style.backgroundColor = ''; }, 400);

                        tableBody.insertBefore(row, tableBody.firstChild);
                    } else {
                        rowCount++;

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${rowCount}</td>
                            <td>${(e.protocol || '').toLowerCase()}</td>
                            <td>${epc}</td>
                            <td>${e.tid || ''}</td>
                            <td>${e.user_data || ''}</td>
                            <td>${e.ant1 ?? 0}</td>
                            <td>${e.ant2 ?? 0}</td>
                            <td>${e.ant3 ?? 0}</td>
                            <td>${e.ant4 ?? 0}</td>
                            <td>${e.rssi || ''}</td>
                        `;

                        const term = searchInput.value.toLowerCase();
                        if (term && !row.innerText.toLowerCase().includes(term)) {
                            row.style.display = 'none';
                        }

                        tableBody.insertBefore(row, tableBody.firstChild);
                        rowMap.set(epc, row);

                        if (tableBody.children.length > 100) {
                            const removed = tableBody.lastChild;
                            rowMap.forEach((v, k) => { if (v === removed) rowMap.delete(k); });
                            tableBody.removeChild(removed);
                        }
                    }

                    updateCounters();
                    updateSelection();
                });

            console.log('[RFID] Subscribed to rfid.live channel.');
        }

        if (window.Echo) {
            subscribeToRfid();
        } else {
            window.addEventListener('echo:ready', subscribeToRfid, { once: true });
        }
    </script>
</body>
</html>