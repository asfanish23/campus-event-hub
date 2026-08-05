<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/uitm_logo.png') }}?v={{ filemtime(public_path('images/uitm_logo.png')) }}">
    <title>Manage Users | Campus Event Hub</title>
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
                <a href="{{ route('super-admin.manage-clubs') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👥</span>
                    <span>Manage Clubs</span>
                </a>
                <a href="{{ route('super-admin.manage-users') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
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
                <h2 class="text-2xl font-bold text-gray-800">Manage Users & Applications</h2>
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

                <div class="admin-shell-card mb-6">
                    <div class="admin-shell-header">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h3 class="admin-shell-title">Pending Applications</h3>
                                <p class="admin-shell-subtitle">Review club admin applications</p>
                            </div>

                            @if($pendingApplications->count() > 0)
                                <span class="self-start px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">
                                    {{ $pendingApplications->count() }} awaiting review
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-6">
                        @if($pendingApplications->count() > 0)
                            <div class="grid gap-4">
                                @foreach($pendingApplications as $application)
                                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition hover:bg-purple-50/40">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                        <div>
                                            <p class="text-xs uppercase font-semibold text-gray-500">Name</p>
                                            <p class="text-lg font-semibold text-gray-800">{{ $application->name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase font-semibold text-gray-500">Email</p>
                                            <p class="text-sm text-gray-700">{{ $application->email }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase font-semibold text-gray-500">Club</p>
                                            <p class="text-sm font-semibold text-purple-600">{{ optional($application->club)->name ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase font-semibold text-gray-500">Applied</p>
                                            <p class="text-sm text-gray-600">{{ $application->admin_submitted_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 rounded-xl p-3 mb-4 border-l-4 border-orange-300">
                                        <p class="text-xs uppercase font-semibold text-gray-500 mb-1">Application Reason</p>
                                        <p class="text-sm text-gray-700">{{ $application->admin_application_reason }}</p>
                                    </div>

                                    <div class="flex items-center gap-2 flex-wrap">
                                        <form method="POST" action="{{ route('super-admin.approve-admin', $application) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="admin-action-btn admin-action-btn--view">
                                                Approve
                                            </button>
                                        </form>
                                        <button
                                            type="button"
                                            data-reject-user-id="{{ $application->id }}"
                                            class="admin-action-btn admin-action-btn--delete">
                                            Reject
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-8 text-center">
                                <p class="text-blue-700 font-semibold">✓ No pending applications</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="admin-filters-card">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h3 class="admin-shell-title">User Filters</h3>
                            <p class="admin-shell-subtitle">Search users, filter by role, and narrow by account status</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 w-full md:max-w-3xl">
                            <input
                                type="text"
                                id="userSearchInput"
                                placeholder="Search name or email..."
                                class="admin-input"
                            >

                            <select id="userRoleFilter" class="admin-select">
                                <option value="">All Roles</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="admin">Admin</option>
                                <option value="student">Student</option>
                            </select>

                            <select id="userStatusFilter" class="admin-select">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="not_applied">Not Applied</option>
                            </select>
                        </div>
                    </div>
                </div>

                @if($allUsers->count() > 0)
                    <div class="admin-table-wrap">
                        <div class="admin-table-header">
                            <h3 class="admin-table-title">All Users & Admins</h3>
                            <p class="admin-table-subtitle">Total: {{ $allUsers->total() }} users</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="admin-table min-w-[980px]">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Club</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th class="is-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    @foreach($allUsers as $user)
                                    <tr
                                        data-user-name="{{ strtolower($user->name) }}"
                                        data-user-email="{{ strtolower($user->email) }}"
                                        data-user-role="{{ strtolower($user->role) }}"
                                        data-user-status="{{ $user->role === 'super_admin' ? 'active' : strtolower($user->admin_status ?? 'not_applied') }}"
                                    >
                                        <td class="font-semibold text-gray-800 whitespace-nowrap">{{ $user->name }}</td>
                                        <td class="text-gray-600 whitespace-nowrap">{{ $user->email }}</td>
                                        <td>
                                            @if($user->role === 'super_admin')
                                                <span class="admin-badge admin-badge--red">Super Admin</span>
                                            @elseif($user->role === 'admin')
                                                <span class="admin-badge admin-badge--blue">Admin</span>
                                            @else
                                                <span class="admin-badge admin-badge--gray">Student</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(optional($user->club)->name)
                                                <span class="admin-badge admin-badge--purple">{{ $user->club->name }}</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->role === 'super_admin')
                                                <span class="admin-badge admin-badge--green">✓ Active</span>
                                            @elseif($user->admin_status === 'pending')
                                                <span class="admin-badge admin-badge--orange">⏳ Pending</span>
                                            @elseif($user->admin_status === 'approved')
                                                <span class="admin-badge admin-badge--green">✓ Approved</span>
                                            @elseif($user->admin_status === 'rejected')
                                                <span class="admin-badge admin-badge--red">✗ Rejected</span>
                                            @else
                                                <span class="admin-badge admin-badge--gray">Not Applied</span>
                                            @endif
                                        </td>
                                        <td class="text-gray-600 whitespace-nowrap">{{ $user->created_at->format('M d, Y') }}</td>
                                        <td class="is-center">
                                            <div class="admin-actions">
                                                <button
                                                    type="button"
                                                    data-view-user-id="{{ $user->id }}"
                                                    class="admin-action-btn admin-action-btn--view">
                                                    View
                                                </button>

                                                @if($user->role !== 'super_admin')
                                                    <button 
                                                        type="button"
                                                        data-edit-user-id="{{ $user->id }}"
                                                        data-edit-user-name="{{ $user->name }}"
                                                        data-edit-user-role="{{ $user->role }}"
                                                        class="admin-action-btn admin-action-btn--edit">
                                                        Edit
                                                    </button>
                                                @endif

                                                @if($user->role === 'super_admin')
                                                    <span class="admin-action-btn" style="background:#9ca3af; cursor:not-allowed;">
                                                        Delete
                                                    </span>
                                                @else
                                                    <form method="POST" action="{{ route('super-admin.delete-user', $user) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="admin-action-btn admin-action-btn--delete">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($allUsers->hasPages())
                        <div class="admin-pagination mt-6">
                            {{ $allUsers->links() }}
                        </div>
                        @endif
                    </div>
                @else
                    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-8 text-center">
                        <p class="text-blue-700 font-semibold text-lg">No users found</p>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Reject Application</h3>
            
            <form method="POST" id="rejectForm" action="">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Rejection Reason *</label>
                    <textarea 
                        name="rejection_reason" 
                        rows="4" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600"
                        placeholder="Please explain why you're rejecting this application..."></textarea>
                </div>

                <div class="flex gap-3">
                    <button 
                        type="button" 
                        onclick="closeRejectModal()"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div id="editRoleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-gray-800 mb-2">Edit User Role</h3>
            <p class="text-gray-600 text-sm mb-4" id="userName"></p>
            
            <form method="POST" id="editRoleForm" action="">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Role *</label>
                    <select 
                        name="role" 
                        id="roleSelect"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">-- Select Role --</option>
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="flex gap-3">
                    <button 
                        type="button" 
                        onclick="closeEditRoleModal()"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-semibold">
                        Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>
    
        <!-- User Details Modal -->
        <div id="userDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-3xl w-full mx-4">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-bold text-gray-800">User Details</h3>
                    <button onclick="closeUserDetailsModal()" class="text-gray-500 hover:text-gray-800">✕</button>
                </div>

                <div id="userDetailsContent" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded p-4 border">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Basic Information</p>
                            <p><span class="font-semibold">Name:</span> <span id="ud_name" class="text-gray-700"></span></p>
                            <p><span class="font-semibold">Email:</span> <span id="ud_email" class="text-gray-700"></span></p>
                            <p><span class="font-semibold">Role:</span> <span id="ud_role" class="inline-block px-2 py-1 rounded-full text-xs font-semibold"></span></p>
                            <p><span class="font-semibold">Faculty / Program:</span> <span id="ud_faculty" class="text-gray-700"></span></p>
                            <p><span class="font-semibold">Account Status:</span> <span id="ud_status" class="inline-block px-2 py-1 rounded-full text-xs font-semibold"></span></p>
                            <p><span class="font-semibold">Joined:</span> <span id="ud_joined" class="text-gray-700"></span></p>
                        </div>

                        <div class="bg-gray-50 rounded p-4 border">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Activity</p>
                            <p><span class="font-semibold">Total Events Joined:</span> <span id="ud_total_joined" class="text-gray-700"></span></p>
                            <p><span class="font-semibold">Total Events Liked:</span> <span id="ud_total_liked" class="text-gray-700"></span></p>
                            <p><span class="font-semibold">Last Active:</span> <span id="ud_last_active" class="text-gray-700"></span></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded p-4 border max-h-56 overflow-auto">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Events Joined</p>
                            <ul id="ud_joined_list" class="space-y-2 text-sm text-gray-700"></ul>
                        </div>

                        <div class="bg-white rounded p-4 border max-h-56 overflow-auto">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Events Liked</p>
                            <ul id="ud_liked_list" class="space-y-2 text-sm text-gray-700"></ul>
                        </div>
                    </div>

                    <div class="flex gap-3 justify-end">
                        <form id="ud_delete_form" method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Delete User</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <script>
            document.addEventListener('DOMContentLoaded', () => {
                const searchInput = document.getElementById('userSearchInput');
                const roleFilter = document.getElementById('userRoleFilter');
                const statusFilter = document.getElementById('userStatusFilter');
                const tableBody = document.getElementById('usersTableBody');

                if (searchInput && roleFilter && statusFilter && tableBody) {
                    const rows = Array.from(tableBody.querySelectorAll('tr[data-user-name]'));

                    const applyFilters = () => {
                        const searchTerm = searchInput.value.trim().toLowerCase();
                        const selectedRole = roleFilter.value;
                        const selectedStatus = statusFilter.value;

                        rows.forEach((row) => {
                            const name = row.dataset.userName || '';
                            const email = row.dataset.userEmail || '';
                            const role = row.dataset.userRole || '';
                            const status = row.dataset.userStatus || '';

                            const matchesSearch = !searchTerm || name.includes(searchTerm) || email.includes(searchTerm);
                            const matchesRole = !selectedRole || role === selectedRole;
                            const matchesStatus = !selectedStatus || status === selectedStatus;

                            row.style.display = matchesSearch && matchesRole && matchesStatus ? '' : 'none';
                        });
                    };

                    searchInput.addEventListener('input', applyFilters);
                    roleFilter.addEventListener('change', applyFilters);
                    statusFilter.addEventListener('change', applyFilters);
                }

                document.querySelectorAll('[data-reject-user-id]').forEach((button) => {
                    button.addEventListener('click', () => {
                        openRejectModal(button.dataset.rejectUserId);
                    });
                });

                document.querySelectorAll('[data-view-user-id]').forEach((button) => {
                    button.addEventListener('click', () => {
                        openUserDetailsModal(button.dataset.viewUserId);
                    });
                });

                document.querySelectorAll('[data-edit-user-id]').forEach((button) => {
                    button.addEventListener('click', () => {
                        openEditRoleModal(
                            button.dataset.editUserId,
                            button.dataset.editUserName,
                            button.dataset.editUserRole
                        );
                    });
                });
            });

        function openRejectModal(userId) {
            document.getElementById('rejectForm').action = `/super-admin/users/${userId}/reject`;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        function openEditRoleModal(userId, userName, currentRole) {
            document.getElementById('editRoleForm').action = `/super-admin/users/${userId}/update-role`;
            document.getElementById('userName').textContent = `Update role for: ${userName}`;
            document.getElementById('roleSelect').value = currentRole;
            document.getElementById('editRoleModal').classList.remove('hidden');
        }

        function closeEditRoleModal() {
            document.getElementById('editRoleModal').classList.add('hidden');
        }

        // User Details modal: fetch /api/admin/users/{id}
        async function openUserDetailsModal(userId) {
            document.getElementById('userDetailsModal').classList.remove('hidden');

            // clear
            document.getElementById('ud_name').textContent = 'Loading...';
            document.getElementById('ud_email').textContent = '';
            document.getElementById('ud_role').textContent = '';
            document.getElementById('ud_faculty').textContent = '';
            document.getElementById('ud_status').textContent = '';
            document.getElementById('ud_joined').textContent = '';
            document.getElementById('ud_total_joined').textContent = '';
            document.getElementById('ud_total_liked').textContent = '';
            document.getElementById('ud_last_active').textContent = '';
            document.getElementById('ud_joined_list').innerHTML = '';
            document.getElementById('ud_liked_list').innerHTML = '';

            try {
                const res = await fetch(`/api/admin/users/${userId}`, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('Failed to fetch');
                const data = await res.json();

                const b = data.basic || {};
                const a = data.activity || {};

                document.getElementById('ud_name').textContent = b.name || '-';
                document.getElementById('ud_email').textContent = b.email || '-';
                document.getElementById('ud_role').textContent = b.role || '-';
                // badge styling for role
                const roleEl = document.getElementById('ud_role');
                roleEl.className = 'inline-block px-2 py-1 rounded-full text-xs font-semibold';
                if (b.role === 'super_admin') roleEl.classList.add('bg-red-100','text-red-700');
                else if (b.role === 'admin') roleEl.classList.add('bg-blue-100','text-blue-700');
                else roleEl.classList.add('bg-gray-100','text-gray-700');

                document.getElementById('ud_faculty').textContent = b.faculty_or_program || '-';

                document.getElementById('ud_status').textContent = b.account_status || '-';
                const statusEl = document.getElementById('ud_status');
                statusEl.className = 'inline-block px-2 py-1 rounded-full text-xs font-semibold';
                if ((b.account_status||'').includes('pending')) statusEl.classList.add('bg-orange-100','text-orange-700');
                else if ((b.account_status||'').includes('approved')) statusEl.classList.add('bg-green-100','text-green-700');
                else if ((b.account_status||'').includes('rejected')) statusEl.classList.add('bg-red-100','text-red-700');
                else statusEl.classList.add('bg-gray-100','text-gray-700');

                document.getElementById('ud_joined').textContent = b.joined_date || '-';

                document.getElementById('ud_total_joined').textContent = a.total_events_joined ?? 0;
                document.getElementById('ud_total_liked').textContent = a.total_events_liked ?? 0;
                document.getElementById('ud_last_active').textContent = a.last_active_date || '-';

                // joined events
                const jl = document.getElementById('ud_joined_list');
                (data.joined_events || []).forEach(ev => {
                    const li = document.createElement('li');
                    li.textContent = `${ev.event_name || 'N/A'} ${ev.event_date ? '(' + ev.event_date + ')' : ''}`;
                    jl.appendChild(li);
                });

                const ll = document.getElementById('ud_liked_list');
                (data.liked_events || []).forEach(ev => {
                    const li = document.createElement('li');
                    li.textContent = `${ev.event_name || 'N/A'} ${ev.event_date ? '(' + ev.event_date + ')' : ''}`;
                    ll.appendChild(li);
                });

                // set delete form action to the existing web route
                const deleteForm = document.getElementById('ud_delete_form');
                deleteForm.action = `/super-admin/users/${userId}`;

            } catch (err) {
                document.getElementById('ud_name').textContent = 'Error loading user';
            }
        }

        function closeUserDetailsModal() {
            document.getElementById('userDetailsModal').classList.add('hidden');
        }
    </script>
</body>
</html>
