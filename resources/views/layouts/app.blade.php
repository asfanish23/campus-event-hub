<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/uitm_logo.png') }}?v={{ filemtime(public_path('images/uitm_logo.png')) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Prevent bfcache (Back/Forward Cache) --}}
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="Sun, 02 Jan 1990 00:00:00 GMT">
    <title>@yield('title', 'Campus Event Hub')</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gray-50">

    {{-- Navigation Bar --}}
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('student.dashboard') }}" class="flex items-center gap-2 text-purple-600 hover:text-purple-800 font-semibold">
                    ← Back
                </a>
                <a href="{{ route('student.dashboard') }}" class="text-2xl font-bold text-purple-600">Campus Event Hub</a>
            </div>
            <div class="flex items-center gap-6">
                <span class="text-gray-700">Welcome, <strong>{{ auth()->user()->name }}</strong></span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p>&copy; 2026 Campus Event Hub. All rights reserved.</p>
        </div>
    </footer>

    @vite('resources/js/app.js')
    
    {{-- Frontend Auth Guard & bfcache Prevention Script --}}
    <script>
        /**
         * FRONTEND GUARD: Prevents rendering of cached pages after logout
         * Checks authentication state and validates session on every page load
         */
        
        // Flag to track if page was loaded from bfcache
        let isRestoredFromBfcache = false;

        // Handle pageshow event (when page is shown, including from bfcache)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was restored from bfcache
                isRestoredFromBfcache = true;
                console.warn('⚠️ Page restored from browser cache (bfcache). Validating session...');
                
                // Validate session immediately
                validateSessionAndReload();
            }
        });

        // Handle pagehide event (when page is about to be cached)
        window.addEventListener('pagehide', function(event) {
            if (event.persisted) {
                console.log('📄 Page is being cached in bfcache');
            }
        });

        /**
         * Validate that the user's session is still active
         * If not, redirect to login (prevents viewing cached protected pages after logout)
         */
        function validateSessionAndReload() {
            // Check if CSRF token exists (indicates valid session)
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            
            if (!csrfToken || !csrfToken.content) {
                // No CSRF token = not authenticated, redirect to login
                console.error('❌ No CSRF token found. Session may be invalid. Redirecting to login...');
                window.location.href = '{{ route("login") }}';
                return;
            }

            // Additional check: fetch user info to verify session is still valid
            fetch('{{ route("student.profile.show") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken.content
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (response.status === 401 || response.status === 403) {
                    // Unauthorized - session expired or user logged out
                    console.error('❌ Session invalid. Redirecting to login...');
                    window.location.href = '{{ route("login") }}';
                } else if (!response.ok) {
                    // Other error, but user might still be authenticated
                    console.warn('⚠️ Session validation returned status: ' + response.status);
                } else {
                    // Session is valid
                    console.log('✅ Session validated successfully');
                    
                    // If restored from bfcache, refresh the page to get fresh data
                    if (isRestoredFromBfcache) {
                        console.log('🔄 Refreshing page to get fresh data...');
                        location.reload(true); // Force reload bypassing cache
                    }
                }
            })
            .catch(error => {
                console.warn('⚠️ Session validation check failed:', error);
                // If validation check fails (network error, etc), allow page to load
                // The server-side middleware will handle it if session is actually invalid
            });
        }

        // Run initial validation when page loads (not from bfcache)
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 Page loaded. Auth state: {{ auth()->check() ? "authenticated" : "not authenticated" }}');
            
            // Initial check - validate CSRF token on page load
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken || !csrfToken.content) {
                console.error('❌ Invalid auth state on page load. Session may have expired.');
                window.location.href = '{{ route("login") }}';
            }
        });

        // Additional layer: Check auth every 30 seconds while page is active
        setInterval(function() {
            if (document.hidden) return; // Skip if tab is not active
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken && csrfToken.content) {
                // Silent validation - only log if there's an issue
                fetch('{{ route("student.profile.show") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (response.status === 401 || response.status === 403) {
                        console.error('❌ Session expired during page activity. Redirecting...');
                        window.location.href = '{{ route("login") }}';
                    }
                })
                .catch(err => {/* Silent fail */});
            }
        }, 30000); // Every 30 seconds

        // Prevent page from being cached by disabling bfcache when needed
        // This is helpful for critical pages where stale data is dangerous
        if (window.performance && window.performance.navigation.type === 2) {
            // User used back button - validate session
            console.warn('🔙 User navigated back. Validating session...');
            validateSessionAndReload();
        }
    </script>
</body>
</html>
