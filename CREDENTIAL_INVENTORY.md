# Credential Inventory Report

**Date Generated:** 2026-06-11  
**Repositories:** Campus Event Hub (Laravel Backend) & Campus Event Hub Mobile (Flutter)

---

## PHASE 1: CREDENTIAL INVENTORY

### Category: API Keys & Tokens

| Secret Type | Service Provider | File Location | Purpose | Status | Recommended Action | Severity |
|-------------|------------------|---------------|---------|--------|-------------------|----------|
| Telegram Bot Token | Telegram BotFather | .env (removed) | Bot API authentication | Compromised | Rotate immediately | CRITICAL |
| Gemini API Key | Google AI Studio | .env (removed) | Generative AI integration | Compromised | Rotate immediately | CRITICAL |
| ImgBB API Key | ImgBB | .env (removed) | Image upload service | Compromised | Rotate immediately | CRITICAL |
| ToyyibPay Secret Key | ToyyibPay | .env (removed) | Payment gateway secret | Compromised | Rotate immediately | CRITICAL |
| Instagram App Secret | Meta/Facebook | .env (removed) | OAuth app secret | Compromised | Regenerate immediately | CRITICAL |
| Instagram Access Token | Meta/Facebook | .env (removed) | User access token | Compromised | Revoke immediately | CRITICAL |
| Instagram Business Account ID | Meta/Facebook | .env (removed) | Business account identifier | Exposed | Treat as compromised | MEDIUM |
| Instagram App ID | Meta/Facebook | .env (removed) | OAuth app identifier | Exposed | No rotation needed; use with rotation | LOW |

### Category: Encryption & Application Keys

| Secret Type | Service Provider | File Location | Purpose | Status | Recommended Action | Severity |
|-------------|------------------|---------------|---------|--------|-------------------|----------|
| APP_KEY (Encryption) | Laravel | .env (removed) | Laravel encryption key | Compromised | Regenerate & migrate encrypted data | CRITICAL |

### Category: Database & Infrastructure

| Secret Type | Service Provider | File Location | Purpose | Status | Recommended Action | Severity |
|-------------|------------------|---------------|---------|--------|-------------------|----------|
| DB_PASSWORD | MySQL | .env | Database authentication | Active (null) | Monitor for changes | LOW |
| REDIS_PASSWORD | Redis | .env | Cache authentication | Active (null) | No action needed | LOW |
| MAIL_PASSWORD | SMTP | .env | Email service auth | Active (null) | No action needed | LOW |

### Category: URLs & Identifiers

| Secret Type | Service Provider | File Location | Purpose | Status | Recommended Action | Severity |
|-------------|------------------|---------------|---------|--------|-------------------|----------|
| APP_URL | ngrok | .env | Public tunnel endpoint | Exposed | Update or replace | MEDIUM |
| NGROK_URL | ngrok | .env | Public tunnel endpoint | Exposed | Update or replace | MEDIUM |
| TOYYIBPAY_CATEGORY_CODE | ToyyibPay | .env (removed) | Merchant category | Exposed | Update with rotation | LOW |

---

## PHASE 2: FILES CONTAINING CREDENTIALS

### Laravel Repository - Removed/Secured:

- [.env](.env) - **CLEANED**: All secrets replaced with `YOUR_*_HERE` placeholders
- [.env.example](.env.example) - Template without secrets (already secure)
- [.env.instagram.example](.env.instagram.example) - Instagram template without secrets (already secure)

### Flutter Repository:

- `.env` files ignored in `.gitignore` (proper practice)
- Secure storage configured in `lib/services/api_service.dart`

---

## PHASE 3: CREDENTIALS REQUIRING ROTATION

All exposed credentials **MUST be rotated immediately**:

1. **Telegram Bot Token**
   - Location: BotFather interface
   - Action: Revoke current token and generate new one
   - Timeline: IMMEDIATE

2. **Gemini/Google API Key**
   - Location: Google AI Studio or Google Cloud Console
   - Action: Disable current key, create new one
   - Timeline: IMMEDIATE

3. **ImgBB API Key**
   - Location: ImgBB account settings
   - Action: Regenerate API key
   - Timeline: IMMEDIATE

4. **ToyyibPay Secret Key**
   - Location: ToyyibPay merchant dashboard
   - Action: Regenerate secret key
   - Timeline: IMMEDIATE

5. **Instagram App Secret**
   - Location: Meta App Dashboard
   - Action: Regenerate app secret
   - Timeline: IMMEDIATE

6. **Instagram User Access Token**
   - Location: Instagram Graph API / Meta Business Manager
   - Action: Revoke token, re-authorize user
   - Timeline: IMMEDIATE

7. **APP_KEY (Laravel Encryption)**
   - Location: Laravel config
   - Action: Generate new key with `php artisan key:generate`
   - Action: Migrate all encrypted data (Instagram tokens encrypted with this key)
   - Timeline: URGENT (requires data migration)

---

## PHASE 4: REMAINING RISKS & NOTES

### High Priority:
- **Git History**: Credentials exist in historical commits. History rewrite required.
- **Clones/Forks**: Anyone with repo access before cleanup has the credentials.
- **ngrok URLs**: Public URLs with timestamps indicate development environment exposure.

### Medium Priority:
- **Backups**: Check server backups for removed `.sql` dumps containing credentials.
- **Debug Routes**: Previously removed `routes/debug.php` should not exist.
- **Logs**: Check `storage/logs/laravel.log` for API response data containing credentials.

### Action Items:
1. Rewrite Git history to remove commits containing secrets
2. Force-push cleaned history to all branches
3. Notify all contributors to re-clone
4. Update all services with new credentials
5. Monitor for API abuse from exposed credentials
6. Configure secret scanning in CI/CD pipeline

---

## STATUS SUMMARY

✅ **Completed:**
- Removed all secrets from `.env` file
- Updated `.env.example` templates (already clean)
- Configured Flutter to use secure storage

⚠️ **In Progress:**
- Credential Inventory created
- Git history analysis pending
- Build validation pending

❌ **Required:**
- Git history rewrite (CRITICAL)
- Credential rotation (CRITICAL)
- Build validation (Laravel & Flutter)
- Final security audit

---

## Next Steps

See [SECURITY_REMEDIATION_REPORT.md](SECURITY_REMEDIATION_REPORT.md) for Phase 1-2 summary.  
Proceed to Phase 3 (Git History Remediation) immediately after rotation.
