<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3/dist/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3/dist/umd/simple-datatables.js"></script>
    <title>CRUD api - Data only</title>
</head>
<body class="bg-gray-100 min-h-screen">

<nav class="bg-white shadow px-6 py-3 mb-6 flex items-center">
  <a class="text-xl font-bold text-blue-600" href="#">CRUD API</a>
  <span class="ml-4 text-gray-400 text-sm">Data monitoring</span>
</nav>

<div class="max-w-7xl mx-auto px-6">
  <div class="bg-white rounded-lg shadow p-6">
    <table id="search-table" class="w-full text-sm text-left">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th class="px-4 py-3">
                    <span class="flex items-center">#</span>
                </th>
                <th class="px-4 py-3">
                    <span class="flex items-center">Name</span>
                </th>
                <th class="px-4 py-3">
                    <span class="flex items-center">Email</span>
                </th>
                <th class="px-4 py-3">
                    <span class="flex items-center">Tasklist</span>
                </th>
            </tr>
        </thead>
        <tbody>
          @forelse ($users as $user)
          <tr class="bg-white border-b hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $user->id }}</td>
            <td class="px-4 py-3">{{ $user->name }}</td>
            <td class="px-4 py-3">{{ $user->email }}</td>
            <td class="px-4 py-3">
              @forelse ($user->tasks as $task)
                <div class="text-sm text-gray-700 py-0.5">{{ $task->title }}</div>
              @empty
                <div class="text-sm text-gray-400 italic">No tasks found.</div>
              @endforelse
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="px-4 py-3 text-center text-gray-400">No data found.</td>
          </tr>
          @endforelse
        </tbody>
    </table>
  </div>
</div>

<script>
  if (document.getElementById("search-table") && typeof simpleDatatables.DataTable !== 'undefined') {
    const dataTable = new simpleDatatables.DataTable("#search-table", {
        searchable: true,
        sortable: true
    });
  }
</script>

</body>
</html>