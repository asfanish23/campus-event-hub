<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/uitm_logo.png') }}?v={{ filemtime(public_path('images/uitm_logo.png')) }}">
    <title>Manage Reviews | Campus Event Hub</title>
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
                <a href="{{ route('super-admin.manage-users') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👤</span>
                    <span>Manage Users</span>
                </a>
                <a href="{{ route('super-admin.manage-reviews') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
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
                <h2 class="text-2xl font-bold text-gray-800">Manage Reviews</h2>
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
                <div class="admin-filters-card">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h3 class="admin-shell-title">Review Filters</h3>
                            <p class="admin-shell-subtitle">Review and resolve reports submitted by club admins</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full md:max-w-2xl">
                            <input
                                type="text"
                                id="reviewSearchInput"
                                placeholder="Search reported reviews..."
                                class="admin-input"
                            >

                            <select id="reviewRatingFilter" class="admin-select">
                                <option value="">All Ratings</option>
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="admin-table-wrap">
                    <div class="admin-table-header">
                        <h3 class="admin-table-title">Reported Reviews</h3>
                        <p class="admin-table-subtitle">Ignore reports or permanently delete inappropriate reviews</p>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-3">{{ session('success') }}</div>
                    @endif
                    @if(session('info'))
                        <div class="mb-4 rounded-lg bg-blue-100 text-blue-800 px-4 py-3">{{ session('info') }}</div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Club</th>
                                    <th>Reviewer</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                    <th>Report Status</th>
                                    <th>Report Date</th>
                                    <th class="is-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="reviewsTableBody">
                                @forelse($reviews as $review)
                                <tr
                                    data-review-event="{{ strtolower($review->event->name ?? 'n/a') }}"
                                    data-reviewer="{{ strtolower($review->user->name ?? $review->reviewer_name ?? 'n/a') }}"
                                    data-review-comment="{{ strtolower($review->comment ?? $review->review_text ?? '') }}"
                                    data-review-rating="{{ $review->rating }}"
                                >
                                    <td class="font-semibold text-gray-800 whitespace-nowrap">{{ $review->event->name ?? 'N/A' }}</td>
                                    <td class="text-gray-600 whitespace-nowrap">{{ $review->event->club->name ?? 'N/A' }}</td>
                                    <td class="text-gray-600 whitespace-nowrap">{{ $review->user->name ?? $review->reviewer_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="text-yellow-500">{{ str_repeat('⭐', $review->rating) }}</span>
                                    </td>
                                    <td class="text-gray-600">{{ Str::limit($review->comment ?? $review->review_text, 80) }}</td>
                                    <td>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Reported</span>
                                    </td>
                                    <td class="text-gray-600 whitespace-nowrap">{{ optional($review->reported_at)->format('M d, Y H:i') ?? 'N/A' }}</td>
                                    <td class="is-center">
                                        <div class="admin-actions">
                                            <form method="POST" action="{{ route('super-admin.manage-reviews.ignore', $review) }}">
                                                @csrf
                                                <button type="submit" class="admin-action-btn admin-action-btn--view">Ignore</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.manage-reviews.delete', $review) }}" onsubmit="return confirm('Delete this review permanently?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-action-btn admin-action-btn--delete">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">No reported reviews found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="admin-pagination">
                        {{ $reviews->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('reviewSearchInput');
            const ratingFilter = document.getElementById('reviewRatingFilter');
            const tableBody = document.getElementById('reviewsTableBody');

            if (!searchInput || !ratingFilter || !tableBody) {
                return;
            }

            const rows = Array.from(tableBody.querySelectorAll('tr[data-review-event]'));

            const applyFilters = () => {
                const searchTerm = searchInput.value.trim().toLowerCase();
                const selectedRating = ratingFilter.value;

                rows.forEach((row) => {
                    const eventName = row.dataset.reviewEvent || '';
                    const reviewer = row.dataset.reviewer || '';
                    const comment = row.dataset.reviewComment || '';
                    const rating = row.dataset.reviewRating || '';

                    const matchesSearch = !searchTerm || eventName.includes(searchTerm) || reviewer.includes(searchTerm) || comment.includes(searchTerm);
                    const matchesRating = !selectedRating || rating === selectedRating;

                    row.style.display = matchesSearch && matchesRating ? '' : 'none';
                });
            };

            searchInput.addEventListener('input', applyFilters);
            ratingFilter.addEventListener('change', applyFilters);
        });
    </script>
</body>
</html>
