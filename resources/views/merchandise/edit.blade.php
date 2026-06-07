<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | Campus Event Hub</title>
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
                    <h2 class="text-2xl font-bold text-gray-800">Edit Product</h2>
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

                <form id="productEditForm" method="POST" action="{{ route('merchandise.update', $product->id) }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-8">
                    @csrf
                    @method('PUT')

                    <!-- Product Media (Photos & Videos) - UNIFIED MANAGEMENT -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Product Media (Photos & Videos)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-purple-600 transition" id="mediaDropZone">
                            <input type="file" name="media_files[]" id="mediaInput" class="hidden" accept="image/*,video/*" multiple onchange="previewAllMedia(this)">
                            <div id="mediaPlaceholder">
                                <p class="text-gray-500 text-4xl mb-2">📷 🎥</p>
                                <p class="text-gray-600">Click to upload or drag and drop</p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF, MP4, MOV up to 50MB each</p>
                            </div>
                            <div id="mediaPreviewContainer" class="grid grid-cols-4 gap-4 mt-4"></div>
                        </div>
                    </div>

                    <!-- Media Gallery (Existing + New) -->
                    <div class="mb-6" id="allMediaGallery" style="display: none;">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Media Gallery</label>
                        <p class="text-xs text-gray-500 mb-3">💡 Hover over photos to select which one becomes the cover image or delete media</p>
                        <div class="grid grid-cols-4 gap-4" id="galleryContainer"></div>
                    </div>

                    <!-- Product Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Product Name</label>
                        <input type="text" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" value="{{ $product->name }}" required>
                    </div>

                    <!-- Product Type -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Product Type</label>
                        <select name="product_type" id="productType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                            <option value="simple" {{ $product->product_type !== 'variant' ? 'selected' : '' }}>Simple Product</option>
                            <option value="variant" {{ $product->product_type === 'variant' ? 'selected' : '' }}>Product With Variants</option>
                        </select>
                    </div>

                    <!-- Price -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Price (RM)</label>
                        <input type="number" name="price" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" value="{{ $product->price }}" required>
                    </div>

                    <!-- Stock -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Stock Quantity</label>
                        <input type="number" name="stock" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" value="{{ $product->stock }}" required>
                    </div>

                    @php
                        $existingVariants = $product->variants->map(function ($variant) {
                            return [
                                'size' => $variant->size,
                                'color' => $variant->color,
                                'price' => $variant->price,
                                'stock' => $variant->stock,
                            ];
                        })->values();
                    @endphp

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
                        <input type="text" name="category" placeholder="e.g., Apparel, Accessories, etc." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" value="{{ $product->category }}" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" placeholder="Product description...">{{ $product->description }}</textarea>
                    </div>

                    <!-- Hidden fields for deleted media IDs and cover selection -->
                    <input type="hidden" name="deleted_media_ids" id="deletedMediaIdsInput" value="">
                    <input type="hidden" name="featured_media_id" id="featuredMediaIdInput" value="">

                    <!-- Buttons -->
                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                            Update Product
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
        const initialVariants = @json(old('variants', $existingVariants));
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
            const rowIndex = variantRowIndex++;
            const row = document.createElement('div');
            row.className = 'grid grid-cols-12 gap-3 items-end bg-gray-50 border border-gray-200 rounded-lg p-4';
            row.innerHTML = `
                <div class="col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Size</label>
                    <input type="text" name="variants[${rowIndex}][size]" value="${escapeHtml(variant.size)}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="S, M, L">
                </div>
                <div class="col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Color</label>
                    <input type="text" name="variants[${rowIndex}][color]" value="${escapeHtml(variant.color)}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Black, White">
                </div>
                <div class="col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Price (RM)</label>
                    <input type="number" step="0.01" min="0" name="variants[${rowIndex}][price]" value="${escapeHtml(variant.price ?? '')}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="0.00">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Stock</label>
                    <input type="number" min="0" name="variants[${rowIndex}][stock]" value="${escapeHtml(variant.stock ?? '')}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="0">
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

        // Track existing media and items to delete
        <?php
            $mediaArray = $product->media->map(function($m) {
                return [
                    'id' => $m->id,
                    'file_type' => $m->file_type,
                    'file_path' => asset('storage/' . $m->file_path),
                    'order' => $m->order
                ];
            })->values();
        ?>
        const existingMedia = @json($mediaArray);
        const deletedMediaIds = new Set();
        const newMediaFiles = new Map();
        
        // Initialize featured media ID with first photo or first media
        let featuredMediaId = existingMedia.length > 0 
            ? (existingMedia.find(m => m.file_type === 'photo')?.id || existingMedia[0].id) 
            : null;

        const mediaInput = document.getElementById('mediaInput');
        const mediaDropZone = document.getElementById('mediaDropZone');
        const mediaPlaceholder = document.getElementById('mediaPlaceholder');
        const mediaPreviewContainer = document.getElementById('mediaPreviewContainer');
        const allMediaGallery = document.getElementById('allMediaGallery');
        const galleryContainer = document.getElementById('galleryContainer');

        function syncMediaInput() {
            const dt = new DataTransfer();
            newMediaFiles.forEach((file) => dt.items.add(file));
            mediaInput.files = dt.files;
        }

        function pickDefaultFeatured() {
            const next = existingMedia.find(m => m.file_type === 'photo' && !deletedMediaIds.has(m.id));
            featuredMediaId = next ? next.id : null;
        }

        // Initialize gallery with existing media
        function renderGallery() {
            galleryContainer.innerHTML = '';

            // Render existing media items
            existingMedia.forEach((media) => {
                if (!deletedMediaIds.has(media.id)) {
                    const item = createMediaItem(media.file_type, media.file_path, 'existing', media.id);
                    if (media.id === featuredMediaId) {
                        item.classList.add('ring-2', 'ring-purple-500');
                        const badge = document.createElement('div');
                        badge.className = 'absolute top-2 left-2 bg-purple-600 text-white text-xs font-semibold px-2 py-1 rounded';
                        badge.textContent = 'Cover';
                        item.appendChild(badge);
                    }
                    galleryContainer.appendChild(item);
                }
            });

            // Render new media files
            newMediaFiles.forEach((file, fileId) => {
                const reader = new FileReader();
                const isVideo = file.type.startsWith('video');
                
                reader.onload = (e) => {
                    const item = createMediaItem(isVideo ? 'video' : 'photo', e.target.result, 'new', fileId);
                    galleryContainer.appendChild(item);
                };
                
                if (!isVideo) {
                    reader.readAsDataURL(file);
                } else {
                    const item = createMediaItem('video', null, 'new', fileId);
                    galleryContainer.appendChild(item);
                }
            });

            // Show gallery based on actual media count (not DOM render which is async)
            const existingCount = existingMedia.filter(m => !deletedMediaIds.has(m.id)).length;
            if (existingCount > 0 || newMediaFiles.size > 0) {
                allMediaGallery.style.display = 'block';
            } else {
                allMediaGallery.style.display = 'none';
            }
        }

        function createMediaItem(fileType, src, type, id) {
            const item = document.createElement('div');
            item.className = 'relative rounded-lg overflow-hidden bg-gray-200 aspect-square flex items-center justify-center group cursor-pointer';
            item.id = `media-${type}-${id}`;
            item.dataset.mediaType = type;
            item.dataset.mediaId = id;

            let content = '';
            if (fileType === 'photo' && src) {
                content = `<img src="${src}" alt="Product media" class="w-full h-full object-cover">`;
            } else {
                content = `<span class="text-4xl">🎥</span>`;
            }

            item.innerHTML = `
                ${content}
                ${type === 'existing' && fileType === 'photo' ? `
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center gap-2">
                        <button type="button" class="set-cover-btn bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-sm font-bold px-3 py-2 rounded transition">
                            ⭐ Set Cover
                        </button>
                        <button type="button" class="delete-media-btn bg-red-500 hover:bg-red-600 text-white text-sm font-bold px-3 py-2 rounded transition">
                            🗑️ Delete
                        </button>
                    </div>
                ` : `
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                        <button type="button" class="delete-media-btn bg-red-500 hover:bg-red-600 text-white text-sm font-bold px-3 py-2 rounded transition">
                            🗑️ Delete
                        </button>
                    </div>
                `}
            `;

            return item;
        }

        // Event delegation for delete buttons
        galleryContainer.addEventListener('click', function(e) {
            const setCoverBtn = e.target.closest('.set-cover-btn');
            if (setCoverBtn) {
                e.preventDefault();
                const mediaItem = setCoverBtn.closest('[data-media-type][data-media-id]');
                const id = parseInt(mediaItem.dataset.mediaId, 10);
                featuredMediaId = id;
                document.getElementById('featuredMediaIdInput').value = String(id);
                renderGallery();
                return;
            }

            const deleteBtn = e.target.closest('.delete-media-btn');
            if (!deleteBtn) return;
            
            e.preventDefault();
            
            const mediaItem = deleteBtn.closest('[data-media-type][data-media-id]');
            const type = mediaItem.dataset.mediaType;
            const id = mediaItem.dataset.mediaId;
            
            if (type === 'existing') {
                const idInt = parseInt(id);
                deletedMediaIds.add(idInt);
                if (featuredMediaId === idInt) {
                    pickDefaultFeatured();
                }
            } else {
                newMediaFiles.delete(id);
            }
            document.getElementById('deletedMediaIdsInput').value = Array.from(deletedMediaIds).join(',');
            document.getElementById('featuredMediaIdInput').value = featuredMediaId ? String(featuredMediaId) : '';
            syncMediaInput();
            renderGallery();
        });

        // Media drop zone handlers
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
                const dt = new DataTransfer();
                Array.from(mediaInput.files).forEach(file => dt.items.add(file));
                Array.from(e.dataTransfer.files).forEach(file => dt.items.add(file));
                mediaInput.files = dt.files;
                previewAllMedia(mediaInput);
            }
        });

        function previewAllMedia(input) {
            if (input.files && input.files.length) {
                mediaPlaceholder.style.display = 'none';
                
                // Add new files to existing tracking (don't clear!)
                Array.from(input.files).forEach((file) => {
                    const uniqueId = Date.now() + Math.random();
                    newMediaFiles.set(uniqueId.toString(), file);
                });

                syncMediaInput();
                renderGallery();
            }
        }

        // Wait for DOM to be ready and attach form submission handler
        function setupFormHandler() {
            const form = document.getElementById('productEditForm');
            const deletedMediaIdsInput = document.getElementById('deletedMediaIdsInput');

            if (form && deletedMediaIdsInput) {
                form.addEventListener('submit', function(e) {
                    const idsArray = Array.from(deletedMediaIds);
                    const idsString = idsArray.join(',');
                    deletedMediaIdsInput.value = idsString;
                    document.getElementById('featuredMediaIdInput').value = featuredMediaId ? String(featuredMediaId) : '';
                }, false); // Use capture phase to ensure it fires early
            }
        }
        
        // Call setup immediately
        setupFormHandler();

        // Initial render
        document.getElementById('featuredMediaIdInput').value = featuredMediaId ? String(featuredMediaId) : '';
        renderGallery();
    </script>
</body>
</html>
