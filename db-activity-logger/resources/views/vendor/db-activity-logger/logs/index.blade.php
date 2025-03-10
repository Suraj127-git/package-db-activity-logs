<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Database Activity Logs</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class'
    }
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react/18.2.0/umd/react.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.2.0/umd/react-dom.production.min.js"></script>
  <style>
    .dark {
      color-scheme: dark;
    }
  </style>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
  <div class="container mx-auto px-4 py-8">
    <div class="mb-8 space-y-4 flex justify-between items-start">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-50">Database Activity Logs</h1>
        <p class="text-gray-500 dark:text-gray-400">View and filter SQL query logs and performance metrics</p>
      </div>
      <button id="darkModeToggle" class="flex items-center justify-center h-10 w-10 rounded-md border border-input bg-background hover:bg-accent">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden dark:block text-gray-50">
          <circle cx="12" cy="12" r="4"></circle>
          <path d="M12 2v2"></path>
          <path d="M12 20v2"></path>
          <path d="M4.93 4.93l1.41 1.41"></path>
          <path d="M17.66 17.66l1.41 1.41"></path>
          <path d="M2 12h2"></path>
          <path d="M20 12h2"></path>
          <path d="M6.34 17.66l-1.41 1.41"></path>
          <path d="M19.07 4.93l-1.41 1.41"></path>
        </svg>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="block dark:hidden text-gray-900">
          <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
        </svg>
      </button>
    </div>

    <div class="mb-6 flex flex-wrap items-end gap-4">
      <div class="grid w-full max-w-sm items-center gap-1.5">
        <label for="dateFilter" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-900 dark:text-gray-50">Filter by Date</label>
        <input type="date" id="dateFilter" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50" onchange="applyFilters()">
      </div>
      
      <div class="grid w-full max-w-sm items-center gap-1.5">
        <label for="tableFilter" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-900 dark:text-gray-50">Filter by Table</label>
        <select id="tableFilter" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50" onchange="applyFilters()">
          <option value="">All Tables</option>
        </select>
      </div>
      
      <button id="resetFilter" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50" onclick="resetFilters()">
        Reset Filters
      </button>
    </div>

    <div id="logStatsCard" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3"></div>
    
    <div id="logContainer" class="grid grid-cols-1 gap-4"></div>
    
    <div id="emptyState" class="hidden py-12 text-center">
      <div class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-10 w-10 text-gray-500 dark:text-gray-400">
          <path d="M4 22h14a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"></path>
          <path d="M14 2v6h6"></path>
          <path d="M3 15h6"></path>
          <path d="M9 18H3"></path>
        </svg>
      </div>
      <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-50">No logs found</h3>
      <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No logs match your current filter criteria.</p>
    </div>
  </div>

  <script>
    const logs = @json($logs); // Replace with actual logs data
    let tableNames = [];

    document.addEventListener('DOMContentLoaded', function() {
      // Dark mode initialization
      const savedTheme = localStorage.getItem('theme');
      const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      
      if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
        document.documentElement.classList.add('dark');
      }

      // UI initialization
      populateTableDropdown();
      renderLogs(logs);
      updateStats(logs);

      // Dark mode toggle handler
      document.getElementById('darkModeToggle').addEventListener('click', () => {
        const html = document.documentElement;
        html.classList.toggle('dark');
        localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
      });
    });

    function populateTableDropdown() {
      const tableFilter = document.getElementById('tableFilter');
      tableNames = [...new Set(logs.map(log => log.table_name))].sort();
      tableNames.forEach(tableName => {
        const option = document.createElement('option');
        option.value = tableName;
        option.textContent = tableName;
        tableFilter.appendChild(option);
      });
    }

    function renderLogs(logsToRender) {
      const logContainer = document.getElementById('logContainer');
      const emptyState = document.getElementById('emptyState');
      logContainer.innerHTML = '';

      if (logsToRender.length === 0) {
        logContainer.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
      }

      logContainer.classList.remove('hidden');
      emptyState.classList.add('hidden');

      logsToRender.forEach(log => {
        let executionTimeClass = 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300';
        if (log.time > 100) executionTimeClass = 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300';
        else if (log.time > 50) executionTimeClass = 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300';

        logContainer.innerHTML += `
          <div class="overflow-hidden rounded-lg border bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50 shadow-sm">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Log ID: ${log.id}</h3>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${executionTimeClass}">
                  ${log.time} ms
                </span>
              </div>
              <div class="mt-4 space-y-3">
                <div>
                  <div class="text-sm font-medium text-gray-500 dark:text-gray-400">SQL Query</div>
                  <div class="mt-1 rounded-md bg-gray-50 dark:bg-gray-900 p-3 text-sm font-mono">${log.sql}</div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Table Name</div>
                    <div class="mt-1">${log.table_name}</div>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Hit Count</div>
                    <div class="mt-1">${log.hit_count}</div>
                  </div>
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Bindings</div>
                  <div class="mt-1 rounded-md bg-gray-50 dark:bg-gray-900 p-3 text-sm font-mono">${JSON.stringify(log.bindings)}</div>
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</div>
                  <div class="mt-1">${new Date(log.created_at).toLocaleString()}</div>
                </div>
              </div>
            </div>
          </div>
        `;
      });
    }

    function updateStats(logsToAnalyze) {
      const statsContainer = document.getElementById('logStatsCard');
      const totalLogs = logsToAnalyze.length;
      let totalExecutionTime = 0, slowestQuery = { time: 0 }, mostFrequentTable = {};

      logsToAnalyze.forEach(log => {
        totalExecutionTime += log.time;
        if (log.time > slowestQuery.time) slowestQuery = log;
        mostFrequentTable[log.table_name] = (mostFrequentTable[log.table_name] || 0) + 1;
      });

      const avgExecutionTime = totalLogs > 0 ? (totalExecutionTime / totalLogs).toFixed(2) : 0;
      const topTable = Object.entries(mostFrequentTable).reduce((a, b) => b[1] > a[1] ? b : a, ['', 0]);

      statsContainer.innerHTML = `
        <div class="rounded-lg border bg-white dark:bg-gray-800 p-4 shadow-sm">
          <div class="flex flex-row items-center justify-between space-y-0 pb-2">
            <h3 class="tracking-tight text-sm font-medium text-gray-500 dark:text-gray-400">Total Logs</h3>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-gray-50">${totalLogs}</div>
        </div>
        <div class="rounded-lg border bg-white dark:bg-gray-800 p-4 shadow-sm">
          <div class="flex flex-row items-center justify-between space-y-0 pb-2">
            <h3 class="tracking-tight text-sm font-medium text-gray-500 dark:text-gray-400">Avg. Execution Time</h3>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-gray-50">${avgExecutionTime} ms</div>
        </div>
        <div class="rounded-lg border bg-white dark:bg-gray-800 p-4 shadow-sm">
          <div class="flex flex-row items-center justify-between space-y-0 pb-2">
            <h3 class="tracking-tight text-sm font-medium text-gray-500 dark:text-gray-400">Most Queried Table</h3>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-gray-50">${topTable[0] || 'N/A'}</div>
          <p class="text-xs text-gray-500 dark:text-gray-400">${topTable[1]} queries</p>
        </div>
      `;
    }

    function applyFilters() {
      const selectedDate = document.getElementById('dateFilter').value;
      const selectedTable = document.getElementById('tableFilter').value;
      let filteredLogs = logs.filter(log => {
        const dateMatch = !selectedDate || new Date(log.created_at).toISOString().split('T')[0] === selectedDate;
        const tableMatch = !selectedTable || log.table_name === selectedTable;
        return dateMatch && tableMatch;
      });
      renderLogs(filteredLogs);
      updateStats(filteredLogs);
    }

    function resetFilters() {
      document.getElementById('dateFilter').value = '';
      document.getElementById('tableFilter').value = '';
      renderLogs(logs);
      updateStats(logs);
    }
  </script>
</body>
</html>