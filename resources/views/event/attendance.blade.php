<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management | Campus Event Hub</title>
    @vite('resources/css/app.css')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-48 bg-gradient-to-b from-purple-600 to-purple-800 text-white overflow-y-auto flex flex-col">
            <div class="p-6 border-b border-purple-500">
                <h1 class="text-xl font-bold">Campus Event Hub</h1>
                <p class="text-sm text-purple-200">Club Admin Panel</p>
            </div>

            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('club-profile.show') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👥</span>
                    <span>Club Profile</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Manage Event</p>
                </div>
                <a href="{{ route('event.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
                    <span>📋</span>
                    <span>Event List</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Social Media</p>
                </div>
                <a href="{{ route('instagram.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📷</span>
                    <span>Instagram</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Manage Shop</p>
                </div>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👕</span>
                    <span>Merchandise</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>🛒</span>
                    <span>Orders</span>
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
                <div>
                    <a href="{{ route('event.index') }}" class="text-purple-600 hover:text-purple-700 text-sm mb-1">← Back to Events</a>
                    <h2 class="text-2xl font-bold text-gray-800">Attendance Management</h2>
                </div>
                <div class="flex items-center gap-6">
                    <button class="text-gray-600 text-xl">🔔</button>
                    <div class="flex items-center gap-3 pl-6 border-l border-gray-300">
                        <div class="text-right">
                            <p class="font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">Club President</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Content -->
            <div class="p-8">
                <p class="text-gray-600 mb-6">Track and manage your event attendance</p>

                <!-- Stats -->
                <div class="grid grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600 text-sm font-semibold mb-2">Total Registered</p>
                        <p class="text-4xl font-bold text-gray-800">{{ $registrations->count() }}</p>
                        <p class="text-xs text-gray-500 mt-2">Participants</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600 text-sm font-semibold mb-2">Present</p>
                        <p class="text-4xl font-bold text-green-600">{{ $attendances->where('status', 'Present')->count() }}</p>
                        <p class="text-xs text-green-600 mt-2">{{ $attendances->count() > 0 ? round(($attendances->where('status', 'Present')->count() / $attendances->count() * 100), 0) : 0 }}% attendance</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600 text-sm font-semibold mb-2">Absent</p>
                        <p class="text-4xl font-bold text-red-600">{{ $attendances->where('status', 'Absent')->count() }}</p>
                        <p class="text-xs text-red-600 mt-2">{{ $attendances->count() > 0 ? round(($attendances->where('status', 'Absent')->count() / $attendances->count() * 100), 0) : 0 }}% absence</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600 text-sm font-semibold mb-2">Show QR</p>
                        @if($event->qr_active)
                        <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold mt-2" onclick="openQRModal()">
                            📱 Show QR
                        </button>
                        @else
                        <button class="px-4 py-2 bg-gray-400 text-gray-700 rounded-lg cursor-not-allowed font-semibold mt-2" disabled title="QR disabled by Super Admin">
                            📱 Disabled
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Attendance Chart -->
                <div class="grid grid-cols-3 gap-8 mb-8">
                    <div class="col-span-1 bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Attendance Overview</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-gray-600 font-semibold mb-2">Present : {{ $attendances->where('status', 'Present')->count() }}</p>
                                <canvas id="attendanceChart" class="w-full"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Attendee List -->
                    <div class="col-span-2">
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Attendee List</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b-2 border-gray-200">
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Name</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Matric No.</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Check-In Time</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($registrations as $registration)
                                            @php
                                                $attendanceRecord = $attendances->firstWhere('user_id', $registration->user_id);
                                            @endphp
                                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                                <td class="py-3 px-4 text-gray-800">{{ $registration->user->name }}</td>
                                                <td class="py-3 px-4 text-gray-600">{{ $registration->user->student_id ?? '-' }}</td>
                                                <td class="py-3 px-4 text-gray-600">{{ $attendanceRecord?->check_in_time ?? '-' }}</td>
                                                <td class="py-3 px-4">
                                                    @if($attendanceRecord?->status === 'Present')
                                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Present</span>
                                                    @elseif($attendanceRecord?->status === 'Absent')
                                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Absent</span>
                                                    @else
                                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">Not Checked</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4">
                                                    @if($attendanceRecord)
                                                        <form method="POST" action="{{ route('attendance.update', $attendanceRecord) }}" style="display:inline;">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="status" value="{{ $attendanceRecord->status === 'Present' ? 'Absent' : 'Present' }}">
                                                            <button type="submit" class="text-sm font-semibold {{ $attendanceRecord->status === 'Present' ? 'text-red-600 hover:text-red-700' : 'text-green-600 hover:text-green-700' }}">
                                                                {{ $attendanceRecord->status === 'Present' ? 'Mark Absent' : 'Mark Present' }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="POST" action="{{ route('attendance.store', '') }}" style="display:inline;">
                                                            @csrf
                                                            <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                            <input type="hidden" name="user_id" value="{{ $registration->user_id }}">
                                                            <input type="hidden" name="attendee_name" value="{{ $registration->user->name }}">
                                                            <input type="hidden" name="matric_no" value="{{ $registration->user->student_id }}">
                                                            <input type="hidden" name="status" value="Present">
                                                            <button type="submit" class="text-sm font-semibold text-green-600 hover:text-green-700">
                                                                Mark Present
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-8 text-center text-gray-500">No attendance records found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const presentCount = {{ $attendances->where('status', 'Present')->count() }};
        const absentCount = {{ $attendances->where('status', 'Absent')->count() }};

        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Absent', 'Present'],
                datasets: [{
                    data: [absentCount, presentCount],
                    backgroundColor: ['#ef4444', '#10b981'],
                    borderColor: ['#dc2626', '#059669'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function openQRModal() {
            const modal = document.getElementById('qrModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeQRModal() {
            const modal = document.getElementById('qrModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }
    </script>

    <!-- QR Modal -->
    @if($event->qr_active)
    <div id="qrModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="closeQRModal()">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">Event QR Code</h3>
                <button onclick="closeQRModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            
            <div class="bg-gray-50 p-6 rounded-lg text-center mb-6">
                <p class="text-gray-600 mb-4 font-semibold">{{ $event->name }}</p>
                @php
                    $attendanceQrPayload = json_encode([
                        'event_id' => $event->id,
                        'scan_url' => url('/api/attendance/scan?event_id=' . $event->id),
                    ], JSON_UNESCAPED_SLASHES);
                @endphp
                <div id="qrCode" class="flex justify-center">
                    {!! QrCode::size(250)->generate($attendanceQrPayload) !!}
                </div>
            </div>

            <p class="text-sm text-gray-600 text-center mb-4">Scan this QR code to mark attendance</p>

            <button onclick="closeQRModal()" class="w-full px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium">
                Close
            </button>
        </div>
    </div>
    @endif
</body>
</html>
