<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clubs | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-48 bg-gradient-to-b from-purple-600 to-purple-800 text-white overflow-y-auto flex flex-col">
            <div class="p-6 border-b border-purple-500">
                <h1 class="text-xl font-bold">Campus Event Hub</h1>
                <p class="text-sm text-purple-200">Super Admin Panel</p>
            </div>

            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
                <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📊</span>
                    <span>Dashboard</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Management</p>
                </div>
                <a href="{{ route('super-admin.manage-events') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📋</span>
                    <span>Manage Events</span>
                </a>
                <a href="{{ route('super-admin.manage-clubs') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
                    <span>👥</span>
                    <span>Manage Clubs</span>
                </a>
                <a href="{{ route('super-admin.manage-users') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👤</span>
                    <span>Manage Users</span>
                </a>
                <a href="{{ route('super-admin.manage-reviews') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>⭐</span>
                    <span>Manage Reviews</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Configuration</p>
                </div>
                <a href="{{ route('super-admin.system-settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>⚙️</span>
                    <span>System Settings</span>
                </a>
            </nav>

            <div class="px-3 py-2 space-y-1">
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>⚙️</span>
                    <span>Settings</span>
                </a>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="p-3 border-t border-purple-500">
                @csrf
                <button type="submit" class="w-full px-4 py-3 bg-red-500 hover:bg-red-600 rounded-lg transition text-sm font-semibold">
                    🚪 Logout
                </button>
            </form>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <!-- Top Bar -->
            <div class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center sticky top-0 z-10">
                <h2 class="text-2xl font-bold text-gray-800">Manage Clubs</h2>
                <div class="flex items-center gap-6">
                    <button class="text-gray-600 text-xl">🔔</button>
                    <div class="flex items-center gap-3 pl-6 border-l border-gray-300">
                        <div class="text-right">
                            <p class="font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">Super Admin</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
                @endif

                @php
                    $clubCategories = collect($clubs->items())->pluck('category')->filter()->unique()->sort()->values();
                @endphp

                <div class="admin-filters-card">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h3 class="admin-shell-title">Club List</h3>
                            <p class="admin-shell-subtitle">Manage all registered campus clubs</p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                            <input
                                type="text"
                                id="clubSearchInput"
                                placeholder="Search clubs..."
                                class="admin-input sm:w-64"
                            >

                            <select id="clubCategoryFilter" class="admin-select sm:w-56">
                                <option value="">All Categories</option>
                                @foreach($clubCategories as $category)
                                    <option value="{{ strtolower($category) }}">{{ $category }}</option>
                                @endforeach
                            </select>

                            <a href="{{ route('super-admin.clubs.create') }}" class="admin-primary-btn whitespace-nowrap">
                                + Create Club
                            </a>
                        </div>
                    </div>
                </div>

                <div class="admin-table-wrap">
                    <div class="admin-table-header">
                        <h3 class="admin-table-title">All Clubs</h3>
                        <p class="admin-table-subtitle">Total: {{ $clubs->total() }} clubs</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Club Name</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th class="is-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="clubsTableBody">
                                @forelse($clubs as $club)
                                <tr
                                    data-club-name="{{ strtolower($club->name) }}"
                                    data-club-category="{{ strtolower($club->category) }}"
                                >
                                    <td class="whitespace-nowrap font-semibold text-gray-800">{{ $club->name }}</td>
                                    <td class="whitespace-nowrap text-gray-600">{{ $club->category }}</td>
                                    <td class="whitespace-nowrap">
                                        @if($club->status === 'active')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="is-center">
                                        <div class="admin-actions">
                                            <a href="{{ route('super-admin.clubs.show', $club) }}" class="admin-action-btn admin-action-btn--view">View</a>
                                            <a href="{{ route('super-admin.clubs.edit', $club) }}" class="admin-action-btn admin-action-btn--edit">Edit</a>
                                            @if($club->status === 'active')
                                                <form method="POST" action="{{ route('super-admin.clubs.status', $club) }}" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="inactive">
                                                    <button type="submit" class="admin-action-btn" style="background-color: #6b7280; color: #ffffff;">
                                                        Deactivate
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('super-admin.clubs.status', $club) }}" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="active">
                                                    <button type="submit" class="admin-action-btn" style="background-color: #16a34a; color: #ffffff;">
                                                        Activate
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('super-admin.clubs.delete', $club) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this club? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-action-btn admin-action-btn--delete">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No clubs found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="admin-pagination">
                        {{ $clubs->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('clubSearchInput');
            const categoryFilter = document.getElementById('clubCategoryFilter');
            const tableBody = document.getElementById('clubsTableBody');

            if (!searchInput || !categoryFilter || !tableBody) {
                return;
            }

            const rows = Array.from(tableBody.querySelectorAll('tr[data-club-name]'));

            const applyFilters = () => {
                const searchTerm = searchInput.value.trim().toLowerCase();
                const categoryTerm = categoryFilter.value;

                rows.forEach((row) => {
                    const name = row.dataset.clubName || '';
                    const category = row.dataset.clubCategory || '';
                    const matchesSearch = !searchTerm || name.includes(searchTerm) || category.includes(searchTerm);
                    const matchesCategory = !categoryTerm || category === categoryTerm;
                    row.style.display = matchesSearch && matchesCategory ? '' : 'none';
                });
            };

            searchInput.addEventListener('input', applyFilters);
            categoryFilter.addEventListener('change', applyFilters);
        });
    </script>
</body>
</html>
