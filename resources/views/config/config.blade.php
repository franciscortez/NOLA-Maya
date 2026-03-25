<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOLA Maya Configuration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-10 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight">NOLA Maya Configuration</h1>
                        <p class="mt-2 text-blue-100 opacity-90">Manage your payment gateway credentials for this location.</p>
                    </div>
                    <div class="hidden md:block">
                        <div class="bg-white/10 backdrop-blur-md rounded-lg px-4 py-2 border border-white/20">
                            <span class="text-xs font-semibold uppercase tracking-wider text-blue-200">Location</span>
                            <p class="font-medium">{{ $location->location_name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                @if (session('success'))
                    <div class="mb-8 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-green-700 font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                        <div class="flex items-center mb-2">
                            <svg class="h-5 w-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-red-700 font-bold">Please correct the following errors:</span>
                        </div>
                        <ul class="list-disc list-inside text-red-600 text-sm ml-8">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('config.save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="location_id" value="{{ $location->location_id }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                        <!-- Left Side: Live Credentials -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="bg-green-100 p-2 rounded-lg">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <h2 class="text-xl font-bold text-gray-800">Live Credentials</h2>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Live Public Key</label>
                                <input type="text" name="maya_live_public_key" value="{{ old('maya_live_public_key', $location->maya_live_public_key) }}" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 outline-none placeholder-gray-400"
                                    placeholder="pk-...">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Live Secret Key</label>
                                <div class="relative">
                                    <input type="password" name="maya_live_secret_key" 
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 outline-none placeholder-gray-400"
                                        placeholder="{{ $location->maya_live_secret_key ? '••••••••••••••••' : 'sk-...' }}">
                                    @if($location->maya_live_secret_key)
                                        <span class="absolute right-4 top-3.5 text-xs font-bold text-green-500 bg-green-50 px-2 py-0.5 rounded-full uppercase tracking-tighter">Encrypted</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-xs text-gray-500 italic">Sensitive keys are encrypted before storage.</p>
                            </div>
                        </div>

                        <!-- Right Side: Test Credentials -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="bg-yellow-100 p-2 rounded-lg">
                                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                    </svg>
                                </div>
                                <h2 class="text-xl font-bold text-gray-800">Test Credentials</h2>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Test Public Key</label>
                                <input type="text" name="maya_test_public_key" value="{{ old('maya_test_public_key', $location->maya_test_public_key) }}" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 outline-none placeholder-gray-400"
                                    placeholder="pk-...">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Test Secret Key</label>
                                <div class="relative">
                                    <input type="password" name="maya_test_secret_key" 
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 outline-none placeholder-gray-400"
                                        placeholder="{{ $location->maya_test_secret_key ? '••••••••••••••••' : 'sk-...' }}">
                                    @if($location->maya_test_secret_key)
                                        <span class="absolute right-4 top-3.5 text-xs font-bold text-green-500 bg-green-50 px-2 py-0.5 rounded-full uppercase tracking-tighter">Encrypted</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-xs text-gray-500 italic">Use these for sandbox testing before going live.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 pt-8 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition duration-200 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Configuration
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400">&copy; {{ date('Y') }} NOLA Maya Integration. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
