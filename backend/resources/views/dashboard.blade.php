<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Live Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Live RFID Tag Dashboard</h1>
        
        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-gray-500">Total Tags</h3>
                <p class="text-3xl font-bold" id="total-tags">{{ $stats['total_tags'] }}</p>
            </div>
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-gray-500">Sessions</h3>
                <p class="text-3xl font-bold">{{ $stats['total_sessions'] }}</p>
            </div>
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-gray-500">Running Scans</h3>
                <p class="text-3xl font-bold">{{ $stats['running'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <div class="p-4 border-b">
                <h2 class="text-xl font-bold">Live Data Stream</h2>
            </div>
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-4">Time</th>
                        <th class="p-4">Protocol</th>
                        <th class="p-4">EPC</th>
                        <th class="p-4">TID</th>
                        <th class="p-4">User Data</th>
                        <th class="p-4">RSSI</th>
                        <th class="p-4">Antenna</th>
                    </tr>
                </thead>
                <tbody id="tags-table">
                    @foreach($stats['recent_tags'] as $tag)
                    <tr class="border-b">
                        <td class="p-4">{{ $tag->scanned_at }}</td>
                        <td class="p-4">{{ $tag->protocol }}</td>
                        <td class="p-4">{{ $tag->epc }}</td>
                        <td class="p-4">{{ $tag->tid }}</td>
                        <td class="p-4">{{ $tag->user_data }}</td>
                        <td class="p-4">{{ $tag->rssi }}</td>
                        <td class="p-4">{{ $tag->antenna }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script type="module">
        let totalTags = {{ $stats['total_tags'] }};
        
        // Listen to the Reverb websocket channel
        setTimeout(() => {
            if (window.Echo) {
                window.Echo.channel('rfid.live')
                    .listen('.tag.scanned', (e) => {
                        totalTags++;
                        document.getElementById('total-tags').innerText = totalTags;
                        
                        const table = document.getElementById('tags-table');
                        const row = document.createElement('tr');
                        row.className = 'border-b bg-green-50 transition-colors duration-500';
                        
                        row.innerHTML = `
                            <td class="p-4">${e.scanned_at || '-'}</td>
                            <td class="p-4">${e.protocol || '-'}</td>
                            <td class="p-4">${e.epc || '-'}</td>
                            <td class="p-4">${e.tid || '-'}</td>
                            <td class="p-4">${e.user_data || '-'}</td>
                            <td class="p-4">${e.rssi || '-'}</td>
                            <td class="p-4">${e.antenna || '-'}</td>
                        `;
                        
                        table.insertBefore(row, table.firstChild);
                        
                        if (table.children.length > 20) {
                            table.removeChild(table.lastChild);
                        }
                        
                        setTimeout(() => {
                            row.classList.remove('bg-green-50');
                        }, 1000);
                    });
            }
        }, 1000);
    </script>
</body>
</html>
