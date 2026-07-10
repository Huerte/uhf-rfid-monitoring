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
            background-color: #316d9bff;
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
            width: 40px;
            text-align: center;
            background-color: #316d9bff;
            border-right: 1px solid #D4D4D4;
            position: relative;
        }

        /* Selected row styling (classic windows blue) */
        .selected-row td {
            background-color: #ffffffff !important;
            color: Black !important;
        }
        .selected-row td:first-child {
            background-color: #0078D7 !important;
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

        /* Proportional column widths */
        .col-type { width: 50%; }
        .col-epc { width: 50%; }
        .col-tid { width: 20%; }
        .col-userdata { width: 12%; }
        .col-reserved { width: 10%; }
        .col-epcbank { width: 50%; }
        .col-total { width: 60%; }
        .col-ant { width: 40%; }
        .col-rssi { width: 50%; }
        
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
    <div class="flex items-center gap-3 p-3 bg-[#F0F0F0] border-b border-[#A0A0A0] shadow-sm">
        <button id="btn-pause" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span id="pause-text">Pause Stream</span>
        </button>
        <button id="btn-export" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 border border-blue-700 rounded shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
            <svg class="w-4 h-4 text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span>Export to CSV</span>
        </button>
        <div class="h-6 w-px bg-gray-300 mx-2"></div>
        <button id="btn-clear" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded shadow-sm hover:bg-red-50 hover:text-red-600 hover:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            <span>Clear Table</span>
        </button>
        <div class="flex-1"></div>
        <div class="relative flex items-center">
            <svg class="w-4 h-4 text-gray-400 absolute left-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" id="search-input" placeholder="Search EPC/TID..." class="pl-9 pr-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 w-64 shadow-sm text-gray-700">
        </div>
    </div>
    <div class="table-container">
        <table class="grid-table" id="tags-table">
            <thead>
                <tr>
                    <th class="col-Number">No.</th>
                    <th class="col-type">Type</th>
                    <th class="col-epc">EPC</th>
                    <th class="col-epcbank">EPCBank</th>
                    <th class="col-total">Totalcount</th>
                    <th class="col-ant">Ant1</th>
                    <th class="col-ant">Ant2</th>
                    <th class="col-ant">Ant3</th>
                    <th class="col-ant">Ant4</th>
                    <th class="col-rssi">Rssi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <script type="module">
        let rowCount = 0;
        const tableBody = document.querySelector('#tags-table tbody');

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

        let isPaused = false;
        const pauseBtn = document.getElementById('btn-pause');
        const pauseText = document.getElementById('pause-text');
        const exportBtn = document.getElementById('btn-export');

        pauseBtn.addEventListener('click', () => {
            isPaused = !isPaused;
            pauseText.innerText = isPaused ? 'Resume Stream' : 'Pause Stream';
            
            if(isPaused) {
                pauseBtn.querySelector('svg').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                pauseBtn.classList.remove('bg-white', 'text-gray-700', 'border-gray-300');
                pauseBtn.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-300');
            } else {
                pauseBtn.querySelector('svg').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                pauseBtn.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-300');
                pauseBtn.classList.add('bg-white', 'text-gray-700', 'border-gray-300');
            }
        });

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

        // Clear button logic
        const clearBtn = document.getElementById('btn-clear');
        clearBtn.addEventListener('click', () => {
            tableBody.innerHTML = '';
            rowCount = 0;
        });

        // Search logic
        const searchInput = document.getElementById('search-input');
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

        function subscribeToRfid() {
            if (!window.Echo) {
                console.error('[RFID] window.Echo is not available.');
                return;
            }

            window.Echo.channel('rfid.live')
                .listen('.tag.scanned', (e) => {
                    if (isPaused) return;

                    rowCount++;

                    const row = document.createElement('tr');

                    row.innerHTML = `
                        <td>${rowCount}</td>
                        <td>${(e.protocol || '').toLowerCase()}</td>
                        <td>${e.epc || ''}</td>
                        <td>${e.tid || ''}</td>
                        <td>${e.user_data || ''}</td>
                        <td></td>
                        <td>2</td>
                        <td>1</td>
                        <td>${e.antenna == 1 ? '1' : '0'}</td> 
                        <td>${e.antenna == 2 ? '1' : '0'}</td>
                        <td>${e.antenna == 3 ? '1' : '0'}</td>
                        <td>${e.antenna == 4 ? '1' : '0'}</td>
                        <td>${e.rssi || ''}</td>
                    `;

                    const term = searchInput.value.toLowerCase();
                    if (term && !row.innerText.toLowerCase().includes(term)) {
                        row.style.display = 'none';
                    }

                    tableBody.insertBefore(row, tableBody.firstChild);

                    if (tableBody.children.length > 100) {
                        tableBody.removeChild(tableBody.lastChild);
                    }

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
