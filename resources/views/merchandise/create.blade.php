<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product | Campus Event Hub</title>
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
                <a href="{{ route('instagram.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📷</span>
                    <span>Instagram</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Manage Shop</p>
                </div>
                <a href="{{ route('merchandise.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
                    <span>👕</span>
                    <span>Merchandise</span>
                </a>
                <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
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
                    <a href="{{ route('merchandise.index') }}" class="text-purple-600 hover:text-purple-700 text-sm mb-1">← Back to Merchandise</a>
                    <h2 class="text-2xl font-bold text-gray-800">Create Product</h2>
                </div>
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

            <!-- Form Content -->
            <div class="p-8 max-w-2xl">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('merchandise.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-8">
                    @csrf

                    <!-- Primary Product Image -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Primary Product Image</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-purple-600 transition" id="imageDropZone">
                            <input type="file" name="image" id="imageInput" class="hidden" accept="image/*" onchange="previewImage(this)">
                            <div id="imagePlaceholder">
                                <p class="text-gray-500 text-4xl mb-2">📷</p>
                                <p class="text-gray-600">Click to upload image or drag and drop</p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                            </div>
                            <img id="imagePreview" class="hidden max-h-48 mx-auto mt-4">
                        </div>
                    </div>

                    <!-- Product Media -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Additional Media (Photos & Videos)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-purple-600 transition" id="mediaDropZone">
                            <input type="file" name="media_files[]" id="mediaInput" class="hidden" accept="image/*,video/*" multiple onchange="previewMedia(this)">
                            <div id="mediaPlaceholder">
                                <p class="text-gray-500 text-4xl mb-2">📷 🎥</p>
                                <p class="text-gray-600">Click to upload photos and videos or drag and drop</p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF, MP4, MOV up to 50MB each</p>
                            </div>
                            <div id="mediaPreviewContainer" class="grid grid-cols-4 gap-4 mt-4" style="display:none;"></div>
                        </div>
                    </div>

                    <!-- Product Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Product Name</label>
                        <input type="text" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" value="{{ old('name') }}" required>
                    </div>

                    <!-- Product Type -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Product Type</label>
                        <select name="product_type" id="productType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                            <option value="simple" {{ old('product_type', 'simple') === 'simple' ? 'selected' : '' }}>Simple Product</option>
                            <option value="variant" {{ old('product_type') === 'variant' ? 'selected' : '' }}>Product With Variants</option>
                        </select>
                    </div>

                    <!-- Price -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Price (RM)</label>
                        <input type="number" name="price" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" value="{{ old('price') }}" required>
                    </div>

                    <!-- Stock -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Stock Quantity</label>
                        <input type="number" name="stock" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" value="{{ old('stock', 0) }}" required>
                    </div>

                    <!-- Variant Builder -->
                    <div class="mb-6" id="variantSection" style="display:none;">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-semibold text-gray-700">Variant Combinations</label>
                            <button type="button" id="addVariantBtn" class="text-sm px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                                + Add Variant
                            </button>
                        </div>
                        <div id="variantRows" class="space-y-3"></div>
                        <p class="text-xs text-gray-500 mt-2">Add each size/color combination with its own price and stock.</p>
                    </div>

                    <!-- Category -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                        <input type="text" name="category" placeholder="e.g., Apparel, Accessories, etc." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" value="{{ old('category') }}" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" placeholder="Product description...">{{ old('description') }}</textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                            Create Product
                        </button>
                        <a href="{{ route('merchandise.index') }}" class="flex-1 px-6 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        const initialVariants = @json(old('variants', []));
        const productTypeSelect = document.getElementById('productType');
        const variantSection = document.getElementById('variantSection');
        const variantRows = document.getElementById('variantRows');
        const addVariantBtn = document.getElementById('addVariantBtn');
        let variantRowIndex = 0;

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function addVariantRow(variant = {}) {
            const rowId = `variant-row-${variantRowIndex++}`;
            const row = document.createElement('div');
            row.className = 'grid grid-cols-12 gap-3 items-end bg-gray-50 border border-gray-200 rounded-lg p-4';
            row.id = rowId;
            row.innerHTML = `
                <div class="col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Size</label>
                    <input type="text" name="variants[${variantRowIndex}][size]" value="${escapeHtml(variant.size)}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="S, M, L">
                </div>
                <div class="col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Color</label>
                    <input type="text" name="variants[${variantRowIndex}][color]" value="${escapeHtml(variant.color)}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Black, White">
                </div>
                <div class="col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Price (RM)</label>
                    <input type="number" step="0.01" min="0" name="variants[${variantRowIndex}][price]" value="${escapeHtml(variant.price ?? '')}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="0.00">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Stock</label>
                    <input type="number" min="0" name="variants[${variantRowIndex}][stock]" value="${escapeHtml(variant.stock ?? '')}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="0">
                </div>
                <div class="col-span-1">
                    <button type="button" class="remove-variant-btn w-full px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition">×</button>
                </div>
            `;
            variantRows.appendChild(row);
            row.querySelector('.remove-variant-btn').addEventListener('click', () => row.remove());
        }

        function syncVariantVisibility() {
            const isVariant = productTypeSelect.value === 'variant';
            variantSection.style.display = isVariant ? 'block' : 'none';
            if (isVariant && variantRows.children.length === 0) {
                addVariantRow();
            }
        }

        productTypeSelect.addEventListener('change', syncVariantVisibility);
        addVariantBtn.addEventListener('click', () => addVariantRow());

        if (Array.isArray(initialVariants) && initialVariants.length > 0) {
            initialVariants.forEach((variant) => addVariantRow(variant));
        }
        syncVariantVisibility();

        // Image preview
        const imageInput = document.getElementById('imageInput');
        const imageDropZone = document.getElementById('imageDropZone');
        const imagePlaceholder = document.getElementById('imagePlaceholder');
        const imagePreview = document.getElementById('imagePreview');

        imageDropZone.addEventListener('click', () => imageInput.click());

        imageDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageDropZone.classList.add('border-purple-600');
        });

        imageDropZone.addEventListener('dragleave', () => {
            imageDropZone.classList.remove('border-purple-600');
        });

        imageDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            imageDropZone.classList.remove('border-purple-600');
            if (e.dataTransfer.files.length) {
                imageInput.files = e.dataTransfer.files;
                previewImage(imageInput);
            }
        });

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePlaceholder.style.display = 'none';
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Track selected media files
        const selectedMediaFiles = new Map();
        
        // Media files preview
        const mediaInput = document.getElementById('mediaInput');
        const mediaDropZone = document.getElementById('mediaDropZone');
        const mediaPlaceholder = document.getElementById('mediaPlaceholder');
        const mediaPreviewContainer = document.getElementById('mediaPreviewContainer');

        mediaDropZone.addEventListener('click', () => mediaInput.click());

        mediaDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            mediaDropZone.classList.add('border-purple-600');
        });

        mediaDropZone.addEventListener('dragleave', () => {
            mediaDropZone.classList.remove('border-purple-600');
        });

        mediaDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            mediaDropZone.classList.remove('border-purple-600');
            if (e.dataTransfer.files.length) {
                // Accumulate files from drag-drop
                const dt = new DataTransfer();
                Array.from(mediaInput.files).forEach(file => dt.items.add(file));
                Array.from(e.dataTransfer.files).forEach(file => dt.items.add(file));
                mediaInput.files = dt.files;
                previewMedia(mediaInput);
            }
        });

        function previewMedia(input) {
            if (input.files && input.files.length) {
                mediaPlaceholder.style.display = 'none';
                mediaPreviewContainer.style.display = 'grid';
                
                // Add new files to tracking
                Array.from(input.files).forEach((file) => {
                    const uniqueId = Date.now() + Math.random();
                    selectedMediaFiles.set(uniqueId.toString(), file);
                });
                
                // Sync all tracked files back to input
                const dt = new DataTransfer();
                selectedMediaFiles.forEach(file => dt.items.add(file));
                mediaInput.files = dt.files;
                
                // Render all previews
                renderAllPreviews();
            }
        }

        function renderAllPreviews() {
            mediaPreviewContainer.innerHTML = '';
            
            selectedMediaFiles.forEach((file) => {
                const reader = new FileReader();
                const isVideo = file.type.startsWith('video');
                
                reader.onload = (e) => {
                    const preview = document.createElement('div');
                    preview.className = 'relative rounded-lg overflow-hidden bg-gray-200 aspect-square flex items-center justify-center group';
                    
                    if (isVideo) {
                        preview.innerHTML = `<span class="text-4xl">🎥</span>`;
                    } else {
                        preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">`;
                    }
                    
                    mediaPreviewContainer.appendChild(preview);
                };
                
                if (!isVideo) {
                    reader.readAsDataURL(file);
                } else {
                    const preview = document.createElement('div');
                    preview.className = 'relative rounded-lg overflow-hidden bg-gray-200 aspect-square flex items-center justify-center group';
                    preview.innerHTML = `<span class="text-4xl">🎥</span>`;
                    mediaPreviewContainer.appendChild(preview);
                }
            });
        }

        // Sync tracked files with actual input files before form submission
        document.querySelector('form').addEventListener('submit', function() {
            const dt = new DataTransfer();
            selectedMediaFiles.forEach(file => dt.items.add(file));
            mediaInput.files = dt.files;
        });
    </script>
</body>
</html>
