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

<!-- Success Message -->
@if(session('success'))
    <div class="max-w-7xl mx-auto px-6 mb-4">
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    </div>
@endif

<!-- Add User Button -->
<div class="flex max-w-7xl mx-auto px-6 mb-4">
    <button onclick="document.getElementById('addUserModal').classList.remove('hidden')"
        class="bg-green-500 hover:bg-green-400 text-white font-semibold py-2 px-4 border border-green-700 hover:border-green-500 rounded-lg shadow w-[150px] h-[40px] flex items-center justify-center cursor-pointer">
        + Add User
    </button>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-opacity-50 backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-700">Add User</h2>
            <button onclick="document.getElementById('addUserModal').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
        </div>
        <form action="{{ url('/users') }}" method="POST">
            @csrf
            <div class="flex flex-col gap-4 p-6">
                <div>
                    <label class="block mb-2 text-sm text-slate-600">Name</label>
                    <input type="text" name="name"
                        class="w-full text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm"
                        placeholder="Your Name" />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block mb-2 text-sm text-slate-600">Email</label>
                    <input type="email" name="email"
                        class="w-full text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm"
                        placeholder="Your Email" />
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="px-6 pb-6">
                <button type="submit"
                    class="w-full rounded-md bg-slate-800 py-2 px-4 text-sm text-white hover:bg-slate-700 shadow-md cursor-pointer">
                    Add User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== TABLE ===================== -->
<div class="max-w-7xl mx-auto px-6">
    <div class="bg-white rounded-lg shadow p-6">
        <table id="search-table" class="w-full text-sm text-left">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-4 py-3"><span class="flex items-center">ID</span></th>
                    <th class="px-4 py-3"><span class="flex items-center">Name</span></th>
                    <th class="px-4 py-3"><span class="flex items-center">Email</span></th>
                    <th class="px-4 py-3"><span class="flex items-center">Tasklist</span></th>
                    <th class="px-4 py-3"><span class="flex items-center">Action</span></th>
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
                            <div onclick="document.getElementById('editTaskModal-{{ $task->id }}').classList.remove('hidden')"
                                class="text-sm text-gray-700 py-0.5 cursor-pointer hover:text-blue-500">
                                {{ $task->title }}
                            </div>
                        @empty
                            <div class="text-sm text-gray-400 italic">No tasks found.</div>
                        @endforelse
                    </td>
                    <td class="px-4 py-3 flex gap-2">
                        <button onclick="document.getElementById('addTaskModal-{{ $user->id }}').classList.remove('hidden')"
                            class="cursor-pointer bg-green-500 hover:bg-green-400 text-white font-bold py-1 px-3 border-b-4 border-green-700 hover:border-green-500 rounded text-xs">
                            Add Task
                        </button>
                        <button onclick="document.getElementById('editUserModal-{{ $user->id }}').classList.remove('hidden')"
                            class="cursor-pointer bg-blue-500 hover:bg-blue-400 text-white font-bold py-1 px-3 border-b-4 border-blue-700 hover:border-blue-500 rounded text-xs">
                            Edit User
                        </button>
                        <button onclick="document.getElementById('deleteModal-{{ $user->id }}').classList.remove('hidden')"
                            class="cursor-pointer bg-red-500 hover:bg-red-400 text-white font-bold py-1 px-3 border-b-4 border-red-700 hover:border-red-500 rounded text-xs">
                            Delete
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-3 text-center text-gray-400">No data found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== MODALS (outside table) ===================== -->
@foreach ($users as $user)

    <!-- Add Task Modal -->
    <div id="addTaskModal-{{ $user->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-opacity-50 backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-700">Add Task for {{ $user->name }}</h2>
                <button onclick="document.getElementById('addTaskModal-{{ $user->id }}').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>
            <form action="{{ url('/tasks') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}" />
                <div class="flex flex-col gap-4 p-6">
                    <div>
                        <label class="block mb-2 text-sm text-slate-600">Title</label>
                        <input type="text" name="title"
                            class="w-full text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm"
                            placeholder="Task Title" />
                        @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm text-slate-600">Description</label>
                        <input type="text" name="description"
                            class="w-full text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm"
                            placeholder="Task Description" />
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm text-slate-600">Due Date</label>
                        <input type="date" name="due_date"
                            class="w-full text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm" />
                        @error('due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm text-slate-600">Status</label>
                        <select name="status" class="w-full text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                        @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="px-6 pb-6">
                    <button type="submit"
                        class="w-full rounded-md bg-slate-800 py-2 px-4 text-sm text-white hover:bg-slate-700 shadow-md cursor-pointer">
                        Add Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal-{{ $user->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-opacity-50 backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-700">Edit {{ $user->name }}</h2>
                <button onclick="document.getElementById('editUserModal-{{ $user->id }}').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>
            <form action="{{ url('/users/' . $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="flex flex-col gap-4 p-6">
                    <div>
                        <label class="block mb-2 text-sm text-slate-600">Name</label>
                        <input type="text" name="name" value="{{ $user->name }}"
                            class="w-full text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm" />
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm text-slate-600">Email</label>
                        <input type="email" name="email" value="{{ $user->email }}"
                            class="w-full text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm" />
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="px-6 pb-6">
                    <button type="submit"
                        class="w-full rounded-md bg-blue-700 py-2 px-4 text-sm text-white hover:bg-blue-600 shadow-md cursor-pointer">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit + Delete Task Modals (one per task) -->
    @foreach ($user->tasks as $task)

    <!-- Edit Task Modal -->
    <div id="editTaskModal-{{ $task->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-opacity-50 backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-700">Edit {{ $task->title }}</h2>
                <button onclick="document.getElementById('editTaskModal-{{ $task->id }}').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <!-- Update Form -->
            <form action="{{ url('/tasks/' . $task->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="flex flex-col gap-4 p-6">
                    <div>
                        <label class="block mb-2 text-sm text-slate-600">Title</label>
                        <input type="text" name="title" value="{{ $task->title }}"
                            class="w-full text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm" />
                        @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm text-slate-600">Description</label>
                        <input type="text" name="description" value="{{ $task->description }}"
                            class="w-full text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm" />
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm text-slate-600">Due Date</label>
                        <input type="date" name="due_date" value="{{ $task->due_date }}"
                            class="w-full text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm" />
                        @error('due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm text-slate-600">Status</label>
                        <select name="status" class="w-full text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm">
                            <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                        @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="px-6 pb-4">
                    <!-- ✅ Update button inside form -->
                    <button type="submit"
                        class="w-full rounded-md bg-blue-700 py-2 px-4 text-sm text-white hover:bg-blue-600 shadow-md cursor-pointer">
                        Update Task
                    </button>
                </div>
            </form>

            <!-- ✅ Delete button OUTSIDE the form -->
            <div class="px-6 pb-6">
                <button type="button"
                    onclick="
                        document.getElementById('editTaskModal-{{ $task->id }}').classList.add('hidden');
                        document.getElementById('deleteTaskModal-{{ $task->id }}').classList.remove('hidden');
                    "
                    class="w-full rounded-md bg-red-700 py-2 px-4 text-sm text-white hover:bg-red-600 shadow-md cursor-pointer">
                    Delete Task
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Task Confirmation Modal -->
    <div id="deleteTaskModal-{{ $task->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-opacity-50 backdrop-blur-sm">
        <div class="relative p-6 w-full max-w-md bg-white rounded-lg shadow text-center">
            <button type="button"
                onclick="document.getElementById('deleteTaskModal-{{ $task->id }}').classList.add('hidden')"
                class="text-gray-400 absolute top-3 right-3 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
            <svg class="text-gray-400 w-11 h-11 mb-4 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <p class="mb-4 text-gray-500">Are you sure you want to delete <strong>{{ $task->title }}</strong>?</p>
            <div class="flex justify-center gap-4">
                <button type="button"
                    onclick="
                        document.getElementById('deleteTaskModal-{{ $task->id }}').classList.add('hidden');
                        document.getElementById('editTaskModal-{{ $task->id }}').classList.remove('hidden');
                    "
                    class="py-2 px-4 text-sm text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 cursor-pointer">
                    No, cancel
                </button>
                <form action="{{ url('/tasks/' . $task->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="py-2 px-4 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 cursor-pointer">
                        Yes, I'm sure
                    </button>
                </form>
            </div>
        </div>
    </div>

    @endforeach

    <!-- Delete User Modal -->
    <div id="deleteModal-{{ $user->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-opacity-50 backdrop-blur-sm">
        <div class="relative p-6 w-full max-w-md bg-white rounded-lg shadow text-center">
            <button type="button"
                onclick="document.getElementById('deleteModal-{{ $user->id }}').classList.add('hidden')"
                class="text-gray-400 absolute top-3 right-3 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
            <svg class="text-gray-400 w-11 h-11 mb-4 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <p class="mb-4 text-gray-500">Are you sure you want to delete <strong>{{ $user->name }}</strong>?</p>
            <div class="flex justify-center gap-4">
                <button type="button"
                    onclick="document.getElementById('deleteModal-{{ $user->id }}').classList.add('hidden')"
                    class="py-2 px-4 text-sm text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 cursor-pointer">
                    No, cancel
                </button>
                <form action="{{ url('/users/' . $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="py-2 px-4 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 cursor-pointer">
                        Yes, I'm sure
                    </button>
                </form>
            </div>
        </div>
    </div>

@endforeach

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