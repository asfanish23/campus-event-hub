# Security Remediation Report

Date: 2026-06-11

## Scope

This review covered the Laravel repository and the companion Flutter mobile
repository, including tracked source, configuration, documentation, helper
scripts, generated artifacts, and Git history.

## Findings Remediated

- Removed a committed Telegram bot token from the webhook setup script and all
  documentation copies.
- Replaced committed Telegram examples with `YOUR_TELEGRAM_BOT_TOKEN` and
  `YOUR_TELEGRAM_CHAT_ID`.
- Removed a committed Gemini/Google API key from implementation documentation
  and replaced it with `YOUR_GEMINI_API_KEY`.
- Removed a tracked SQL database dump containing account data and password
  hashes.
- Removed a tracked phpMyAdmin configuration containing a blowfish secret and
  default database credentials.
- Removed an unused debug route that disclosed part of an Instagram token.
- Stopped the Instagram setup script from printing token material.
- Removed hardcoded super-admin identities and the default `admin` password
  from the admin seeder.
- Added environment templates for Telegram, Gemini, ToyyibPay, Meta/Instagram,
  ImgBB, mail, database, and admin-seeder credentials.
- Added fail-closed Telegram behavior when credentials are absent.
- Moved Flutter authentication tokens from shared preferences to platform
  secure storage and retained a one-time migration for existing installations.
- Kept Telegram, payment, Gemini, Meta, SMTP, database, and other server secrets
  out of the Flutter client.

## Files Affected

Primary implementation and configuration files:

- `.env.example`, `.env.instagram.example`, `.gitignore`
- `config/services.php`, `config/admin.php`
- `app/Services/TelegramBotService.php`
- `app/Http/Controllers/Web/InstagramOAuthController.php`
- `database/seeders/AdminUserSeeder.php`
- `setup-telegram-webhook.php`, `setup-ig-account.php`
- `backup.sql`, `phpmyadmin-config.inc.php`, `routes/debug.php` (removed)
- Telegram, Gemini, OAuth, and payment documentation containing credential
  examples
- Flutter `.env.example`, `.gitignore`, `pubspec.yaml`,
  `lib/services/api_service.dart`, and authentication documentation

## Required Rotations

Rotate or revoke these credentials immediately because repository cleanup does
not invalidate credentials already copied from public Git history:

1. Telegram bot token through BotFather, then update `TELEGRAM_BOT_TOKEN`.
2. Gemini/Google API key in Google AI Studio or Google Cloud.
3. phpMyAdmin/database password used by the removed configuration.
4. Passwords for seeded super-admin accounts and any other account present in
   the removed SQL dump.

Also rotate Meta/Instagram, ImgBB, ToyyibPay, SMTP, database, and application
credentials if they were ever reused in public files, logs, support messages,
or deployments. Restrict replacement keys by API, domain/IP, environment, and
least privilege wherever the provider supports it.

## History Remediation

The exposed token and API key existed across historical commits. Git history
must be rewritten and force-pushed for all branches and tags. Existing clones
and forks retain the old objects, so rotation remains mandatory even after the
rewrite. Collaborators must re-clone or hard-reset to the rewritten branch.

## Remaining Risks

- Flutter binaries are inspectable. Only public configuration such as the API
  base URL may be supplied at build time; all privileged API calls must remain
  on Laravel.
- User-linked Instagram tokens are encrypted using Laravel's `APP_KEY`.
  Protect and back up that key separately; rotating it requires a data
  migration.
- The public Telegram webhook is not authenticated with Telegram's optional
  secret-token header. Add webhook request verification as a defense-in-depth
  follow-up.
- Existing application debug logging includes API response bodies in several
  mobile code paths. Production logging should be reduced to status and request
  identifiers to avoid leaking personal data from server responses.
- Secret scanning should be added to pre-commit and CI checks to prevent
  recurrence.
