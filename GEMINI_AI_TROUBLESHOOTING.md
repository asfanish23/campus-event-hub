# 🔧 Gemini AI Implementation - Troubleshooting Guide

## Common Issues & Solutions

### ❌ Issue 1: "API key not found" error
**Symptoms:** 500 error when clicking Generate button

**Solution:**
1. Check `.env` file has `GEMINI_API_KEY` set
2. Clear Laravel cache:
   ```bash
   php artisan config:cache
   php artisan cache:clear
   ```
3. Restart Laravel server

---

### ❌ Issue 2: CSRF Token Missing
**Symptoms:** 419 error when submitting form

**Solution:**
The code automatically handles this in the JavaScript:
```javascript
'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
```

Make sure your blade template includes:
```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
```

If missing, add it in the `<head>` section of create.blade.php

---

### ❌ Issue 3: Route Not Found (404)
**Symptoms:** "POST /ai/generate-description not found"

**Solution:**
1. Verify routes in `routes/web.php`:
   ```php
   Route::post('/ai/generate-description', [AiGeneratorController::class, 'generateDescription'])->name('ai.generate-description');
   Route::post('/ai/tweak-description', [AiGeneratorController::class, 'tweakDescription'])->name('ai.tweak-description');
   ```
2. Verify import at top:
   ```php
   use App\Http\Controllers\Web\AiGeneratorController;
   ```
3. Clear route cache:
   ```bash
   php artisan route:cache
   php artisan route:clear
   ```

---

### ❌ Issue 4: Typing Effect Too Fast/Too Slow
**Symptoms:** Text appears instantly or takes too long

**Solution:**
Adjust the speed parameter in `typeEffect()`:
```javascript
// Current: 15ms per character
await typeEffect(textArea, data.text, 15);

// Slower (more cinematic): 30ms
await typeEffect(textArea, data.text, 30);

// Faster: 5ms
await typeEffect(textArea, data.text, 5);
```

---

### ❌ Issue 5: Button Stays Disabled After Error
**Symptoms:** Generate button becomes unclickable after an error

**Solution:**
The `finally` block should reset the button. If it doesn't:
```javascript
// Manually reset button:
generateBtn.disabled = false;
generateBtn.style.opacity = '1';
generateBtn.textContent = '✨ Generate with AI';
```

Check browser console (F12) for JavaScript errors.

---

### ❌ Issue 6: Tweak Buttons Don't Appear
**Symptoms:** After generation, tweak buttons are hidden

**Solution:**
In the code, check:
```javascript
// Make sure tweak container visibility toggle works
tweakContainer.style.display = 'flex';  // Show
tweakContainer.style.display = 'none';  // Hide
```

Alternative: Set via class instead:
```css
/* Add to CSS */
.show-tweaks { display: flex !important; }
.hide-tweaks { display: none !important; }
```

Then use:
```javascript
tweakContainer.classList.add('show-tweaks');
```

---

## 🔍 Debugging Steps

### Step 1: Check Backend Setup
```bash
# Verify files exist
ls -la app/Services/GeminiService.php
ls -la app/Http/Controllers/Web/AiGeneratorController.php

# Verify syntax
php -l app/Services/GeminiService.php
php -l app/Http/Controllers/Web/AiGeneratorController.php
```

### Step 2: Check Configuration
```bash
# Verify config is loaded
php artisan tinker
> config('services.gemini.api_key')
# Should output your API key, not null
```

### Step 3: Test API Directly
```bash
# Terminal test of Gemini API
curl -X POST "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "contents": [{
      "parts": [{"text": "Say hello"}]
    }]
  }'
```

### Step 4: Check Browser Console
Open Developer Tools (F12) → Console tab
- Look for JavaScript errors
- Check Network tab for failed requests
- Verify request/response in Network tab

### Step 5: Check Laravel Logs
```bash
# Watch real-time logs
tail -f storage/logs/laravel.log

# Or check specific date
cat storage/logs/laravel-2026-02-02.log
```

---

## ✅ Verification Checklist

### Files Created
- [ ] `app/Services/GeminiService.php` exists
- [ ] `app/Http/Controllers/Web/AiGeneratorController.php` exists
- [ ] `GEMINI_AI_IMPLEMENTATION.md` created
- [ ] `GEMINI_AI_QUICK_REFERENCE.md` created

### Files Modified
- [ ] `routes/web.php` has AI routes
- [ ] `config/services.php` has Gemini config
- [ ] `.env` has GEMINI_API_KEY
- [ ] `resources/views/event/create.blade.php` has new HTML and JS

### Functionality
- [ ] Event Name field validates (required)
- [ ] Generate button shows loading state
- [ ] Text appears with typing animation
- [ ] Tweak buttons appear after generation
- [ ] Funnier/Professional/Shorter buttons work
- [ ] No API key exposed in browser

---

## 🚀 Production Checklist

Before deploying to production:

```
[ ] API Key in production .env (not in code)
[ ] CSRF protection enabled
[ ] Error messages don't expose sensitive data
[ ] Rate limiting added (if needed)
[ ] SSL/HTTPS enabled
[ ] Logging configured for API calls
[ ] Error tracking (Sentry, etc.)
[ ] Performance monitoring set up
[ ] User quota/limits implemented
[ ] Documentation updated
[ ] Team trained on new feature
```

---

## 📞 Getting Help

### Check These Resources
1. **Google Gemini API Docs:** https://ai.google.dev/docs
2. **Laravel Documentation:** https://laravel.com/docs
3. **Browser Console:** F12 → Console tab
4. **Laravel Logs:** `storage/logs/laravel.log`

### Common API Errors

| Error | Meaning | Solution |
|-------|---------|----------|
| 401 | Invalid API key | Check .env GEMINI_API_KEY |
| 429 | Rate limited | Add delay between requests |
| 500 | Server error | Check API key and request format |
| 400 | Bad request | Verify JSON format |

---

## 🎯 Performance Optimization

### If Generation is Slow:
1. Check internet connection
2. Verify API key works (test endpoint)
3. Consider caching results
4. Use `gemini-1.5-flash` (faster) instead of `gemini-1.5-pro`

### If UI is Slow:
1. Reduce typing effect speed: `typeEffect(element, text, 5)` (5ms)
2. Debounce tweak button clicks
3. Lazy load tweak buttons

---

## 📊 Monitoring

### What to Monitor
- API response times
- Error rates
- User adoption
- Most used tweak style
- Failed generations

### Recommended Setup
```bash
# Add to .env
LOG_CHANNEL=stack
LOG_LEVEL=info

# Monitor API calls in database
# Table: ai_generation_logs
# Columns: user_id, prompt, response, latency, status
```

---

## 🎓 Learning Resources

### Code Architecture Pattern
This implementation uses **Service Layer Pattern**:
```
Controller → Service → External API → Response
```

Benefits:
- Easy to test
- Reusable logic
- Clean separation of concerns

### Related Concepts
- Laravel Service Container
- Dependency Injection
- Promise-based async JavaScript
- REST API best practices

---

**Last Updated:** February 2, 2026
**Status:** Ready for Production ✅
