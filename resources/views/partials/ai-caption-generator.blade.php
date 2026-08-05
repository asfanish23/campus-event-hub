<div class="mt-6">
    <label class="block text-sm font-semibold text-gray-700 mb-2">Description *</label>
    <div style="position: relative;">
        <textarea id="eventDescription" name="description" rows="5" required placeholder="(Optional) Add extra details before generating! E.g., free breakfast, free merchandise, limited seats, special perks, etc." maxlength="500" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-purple-600 transition-all">{{ old('description') }}</textarea>
        
        <button type="button" id="generateBtn" 
                style="position: absolute; bottom: 10px; right: 10px; border: none; background: #6e45e2; color: white; padding: 6px 14px; border-radius: 20px; font-size: 12px; cursor: pointer; font-weight: 500; transition: all 0.3s ease; hover:background: #5a36c7;">
            ✨ Generate with AI
        </button>
    </div>
    <small style="color: #888; display: block; margin-top: 6px;">💡 Tip: Add any special details (free food, limited spots, prizes, etc.) above. Gemini will use them to create a better description! (Max 500 characters)</small>
    <small id="aiStatus" style="color: #888; display: none; margin-top: 8px; display: block;">⏳ Gemini is thinking...</small>

    <!-- Tweak Buttons -->
    <div id="tweakContainer" style="display: none; margin-top: 12px; gap: 8px; display: none; flex-wrap: wrap;">
        <button type="button" class="tweak-btn" data-style="funnier" style="padding: 6px 12px; border: 1px solid #ddd; background: white; color: #666; border-radius: 16px; font-size: 11px; cursor: pointer; transition: all 0.2s ease;">😄 Funnier</button>
        <button type="button" class="tweak-btn" data-style="professional" style="padding: 6px 12px; border: 1px solid #ddd; background: white; color: #666; border-radius: 16px; font-size: 11px; cursor: pointer; transition: all 0.2s ease;">📋 Professional</button>
        <button type="button" class="tweak-btn" data-style="shorter" style="padding: 6px 12px; border: 1px solid #ddd; background: white; color: #666; border-radius: 16px; font-size: 11px; cursor: pointer; transition: all 0.2s ease;">✂️ Shorter</button>
    </div>
</div>
