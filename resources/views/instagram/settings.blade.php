<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/UITM_FAVICON.png') }}?v={{ filemtime(public_path('images/UITM_FAVICON.png')) }}">
    <title>Instagram Settings | Campus Event Hub</title>
    @vite('resources/css/app.css')
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
                <a href="{{ route('event.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📋</span>
                    <span>Event List</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Social Media</p>
                </div>
                <a href="{{ route('instagram.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
                    <span>📱</span>
                    <span>Social Media</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Manage Shop</p>
                </div>
                <a href="{{ route('merchandise.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👕</span>
                    <span>Merchandise</span>
                </a>
                <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📦</span>
                    <span>Orders</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">More</p>
                </div>
                <a href="{{ route('club-profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>⚙️</span>
                    <span>Settings</span>
                </a>
            </nav>

            <div class="p-3 border-t border-purple-500">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-red-600 hover:bg-red-700 transition text-sm font-medium">
                        <span>🚪</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('instagram.index') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Instagram Settings</h1>
        </div>
        <p class="text-gray-600">Configure your Instagram Business Account for automatic event posting</p>
    </div>

    <!-- Settings Card -->
    <div class="bg-white rounded-lg shadow-md p-8">
        <!-- Credentials Status -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Connection Status</h2>
            <div class="space-y-3">
                <!-- Access Token -->
                <div class="flex items-center justify-between p-4 border rounded-lg
                    @if($hasToken) bg-green-50 border-green-200 @else bg-red-50 border-red-200 @endif">
                    <div>
                        <p class="font-semibold
                            @if($hasToken) text-green-800 @else text-red-800 @endif">
                            Instagram Access Token
                        </p>
                        <p class="text-sm
                            @if($hasToken) text-green-700 @else text-red-700 @endif">
                            @if($hasToken)
                                ✓ Configured
                            @else
                                ✗ Not configured
                            @endif
                        </p>
                    </div>
                    <i class="fas fa-2x
                        @if($hasToken) fa-check-circle text-green-600 @else fa-times-circle text-red-600 @endif">
                    </i>
                </div>

                <!-- Business Account ID -->
                <div class="flex items-center justify-between p-4 border rounded-lg
                    @if($hasUserId) bg-green-50 border-green-200 @else bg-red-50 border-red-200 @endif">
                    <div>
                        <p class="font-semibold
                            @if($hasUserId) text-green-800 @else text-red-800 @endif">
                            Instagram Business Account ID
                        </p>
                        <p class="text-sm
                            @if($hasUserId) text-green-700 @else text-red-700 @endif">
                            @if($hasUserId)
                                ✓ Configured
                            @else
                                ✗ Not configured
                            @endif
                        </p>
                    </div>
                    <i class="fas fa-2x
                        @if($hasUserId) fa-check-circle text-green-600 @else fa-times-circle text-red-600 @endif">
                    </i>
                </div>
            </div>
        </div>

        <!-- Configuration Instructions -->
        <div class="border-t pt-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Setup Instructions</h2>
            
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <p class="text-blue-800 font-semibold mb-2">
                    <i class="fas fa-info-circle"></i> How to Get Your Credentials
                </p>
                <ol class="text-blue-800 text-sm space-y-2 ml-6 list-decimal">
                    <li>Go to <a href="https://developers.facebook.com" target="_blank" class="underline font-semibold">Facebook Developers</a></li>
                    <li>Create or select your app</li>
                    <li>Navigate to <strong>Settings > Basic</strong></li>
                    <li>Connect your Instagram Business Account</li>
                    <li>Get your Long-lived Access Token from Graph API Explorer</li>
                    <li>Find your Instagram Business Account ID</li>
                    <li>Add to your <code class="bg-blue-100 px-2 py-1 rounded">.env</code> file:
                        <pre class="bg-white p-3 rounded mt-2 text-xs overflow-x-auto">
INSTAGRAM_ACCESS_TOKEN=your_token_here
INSTAGRAM_BUSINESS_ACCOUNT_ID=your_id_here
                        </pre>
                    </li>
                </ol>
            </div>

            <!-- Environment Variables -->
            <div class="bg-gray-50 border rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-3">Current Environment Variables</h3>
                <div class="space-y-2 text-sm font-mono bg-white p-3 rounded border">
                    <div class="text-gray-700">
                        INSTAGRAM_ACCESS_TOKEN=<span class="@if($hasToken) text-green-600 @else text-red-600 @endif">
                            @if($hasToken) ✓ SET @else ✗ NOT SET @endif
                        </span>
                    </div>
                    <div class="text-gray-700">
                        INSTAGRAM_BUSINESS_ACCOUNT_ID=<span class="@if($hasUserId) text-green-600 @else text-red-600 @endif">
                            @if($hasUserId) ✓ SET @else ✗ NOT SET @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="bg-white">
                <h3 class="font-semibold text-gray-900 mb-3">Features</h3>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-center gap-2">
                        <i class="fas fa-check text-green-600"></i>
                        Automatic posting when event is created
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-check text-green-600"></i>
                        Manual repost from Instagram management page
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-check text-green-600"></i>
                        Custom event captions with emojis and details
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-check text-green-600"></i>
                        Automatic error logging and notifications
                    </li>
                </ul>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-8 border-t pt-6">
            <a href="{{ route('instagram.index') }}" class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                <i class="fas fa-arrow-left"></i> Back to Instagram
            </a>
        </div>
    </div>
</div>

        </main>
    </div>

<style>
    code {
        background-color: #f3f4f6;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-family: 'Courier New', monospace;
    }
</style>
</body>
</html>
