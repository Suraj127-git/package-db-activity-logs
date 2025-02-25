<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Activity Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-4">Database Activity Logs</h1>

        <div class="mb-6">
            <label for="dateFilter" class="block text-sm font-medium text-gray-700">Filter by Date:</label>
            <input type="date" id="dateFilter" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" onchange="filterByDate(this.value)">
        </div>

        <div id="logContainer" class="grid grid-cols-1 gap-4">
            @foreach($logs as $log)
                <div class="bg-white shadow-md rounded-lg p-4">
                    <h2 class="font-semibold">Log ID: {{ $log->id }}</h2>
                    <p><strong>SQL:</strong> {{ $log->sql }}</p>
                    <p><strong>Bindings:</strong> {{ json_encode($log->bindings) }}</p>
                    <p><strong>Execution Time:</strong> {{ $log->time }} ms</p>
                    <p><strong>Table Name:</strong> {{ $log->table_name }}</p>
                    <p><strong>Hit Count:</strong> {{ $log->hit_count }}</p>
                    <p><strong>Created At:</strong> {{ $log->created_at }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        function filterByDate(selectedDate) {
            const logContainer = document.getElementById('logContainer');
            const logs = @json($logs);

            logContainer.innerHTML = ''; // Clear current logs

            logs.forEach(log => {
                const logDate = new Date(log.created_at).toISOString().split('T')[0];
                if (logDate === selectedDate) {
                    logContainer.innerHTML += `
                        <div class="bg-white shadow-md rounded-lg p-4">
                            <h2 class="font-semibold">Log ID: ${log.id}</h2>
                            <p><strong>SQL:</strong> ${log.sql}</p>
                            <p><strong>Bindings:</strong> ${JSON.stringify(log.bindings)}</p>
                            <p><strong>Execution Time:</strong> ${log.time} ms</p>
                            <p><strong>Table Name:</strong> ${log.table_name}</p>
                            <p><strong>Hit Count:</strong> ${log.hit_count}</p>
                            <p><strong>Created At:</strong> ${log.created_at}</p>
                        </div>
                    `;
                }
            });
        }
    </script>
</body>
</html>