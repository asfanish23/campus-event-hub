# Threads API Integration — Feasibility Analysis

> Analysis of the existing Meta OAuth / Instagram integration to determine reuse for the Threads API. No code was written.

---

## 1. Current State Inventory

### 1.1 InstagramOAuthController
`app/Http/Controllers/Web/InstagramOAuthController.php`

| Method | What it does |
|---|---|
| `redirectToInstagram($clubId)` | Validates club ownership (or assigns current user as admin), generates a random `state`, stores `instagram_oauth_state` + `instagram_club_id` in the session, then redirects to `https://www.instagram.com/oauth/authorize` with scopes `instagram_business_basic,instagram_business_content_publish`. |
| `handleCallback(Request)` | Verifies `state`, exchanges `code` → short-lived token via `POST https://graph.instagram.com/v18.0/oauth/access_token`, then swaps it for a **long-lived (60-day)** token via `GET https://graph.instagram.com/v18.0/access_token?grant_type=ig_refresh_token`, fetches account profile (`GET /v18.0/{$userId}?fields=id,username`), and persists to `InstagramAccount` via `firstOrNew(['club_id'])`, setting `connection_method='oauth'`. |
| `fetchAccountFromToken(Request)` | AJAX helper: validates a pasted token against `GET https://graph.instagram.com/v18.0/me`. |

**Token refresh logic:** The only refresh is the one-time `ig_refresh_token` long-lived swap during callback. There is **no scheduled/periodic refresh**, `token_expires_at` is never set during the OAuth flow, and the `refresh_token` column added by migration `2026_01_18_090000` is **not** written by this controller (and is not in `InstagramAccount::$fillable`).

### 1.2 Routes (`routes/web.php`)

- Instagram OAuth: `/instagram/oauth/redirect/{clubId}`, `/instagram/oauth/callback`, `/instagram/oauth/fetch-account` (lines 121–123).
- Social-media dashboard: `/social-media`, `/social-media/events/{event}/instagram`, `/social-media/events/{event}/facebook`, `/social-media/events/{event}/publish-all` (lines 99–102).
- Legacy Instagram routes: `/instagram` + post/schedule/repost/test (lines 105–113).
- **There are no Facebook OAuth routes and no generic "Meta" auth routes.** Facebook is configured statically via global config only.

### 1.3 Services (`app/Services/`)

| Service | Role | Platform-specific? |
|---|---|---|
| `InstagramService` | Two-step post (container → publish) and insights against `graph.instagram.com/v18.0`. Credentials read from `config('services.instagram.*')` in constructor; also `postImageWithCustomCredentials()` for per-club accounts. | Yes (Instagram) |
| `ClubInstagramService` | Posts an event image (ImgBB upload) to the **club's** stored Instagram account (decrypts `InstagramAccount.access_token`). | Yes (Instagram) |
| `ScheduledInstagramPostService` | Scheduler entrypoint (`instagram:process-scheduled-posts`, every 5 min) + reposts. | Yes (Instagram) |
| `InstagramSyncService` | Pulls metrics (`getMediaInsights`) into `events.instagram_*` columns; milestone notifications. | Yes (Instagram) |
| `InstagramNotificationService` | Milestone/sync notifications. | Yes (Instagram) |
| `ImgBBService` | Uploads local image → public URL (used by every platform path). | **No (reusable)** |
| `ClubActivityService` / `ClubNotificationService` | Generic club activity/notification logging. | **No (reusable)** |

- **No `FacebookService`, no `SocialMediaService`, no shared Graph client.** Facebook posting is **inlined in `InstagramController`** (`postImageToFacebook()` → `Http::asForm()->post("https://graph.facebook.com/v22.0/{$pageId}/photos")`).

### 1.4 Models / Database

- `InstagramAccount` — one row per club (`unique(['club_id'])`), `access_token` stored **encrypted** via mutator (`encrypt()`/`decrypt()` via `setAccessTokenAttribute()` / `getDecryptedToken()`), plus `is_active`, `token_expires_at`, `last_post_at`, and `refresh_token`, `oauth_state`, `connection_method` columns (the last three added but partially unused). Schema is **Instagram-named and not platform-generic**.
- `SocialPost` — **already platform-agnostic.** Migration `2026_06_24_000000` uses `enum('platform', ['instagram','facebook','threads'])` and the model already defines `PLATFORM_THREADS = 'threads'`. No migration needed to record Threads posts.
- `Event` — heavy `instagram_*` column set (media_id, posted_at, metrics, scheduling/repost flags). **No Threads columns.** Useful platform-agnostic helpers already exist: `socialPosts()`, `latestSocialPost($platform)`, `isPostedToPlatform($platform)`, `postedAtForPlatform($platform)`.
- `Club` — already has a `threads_url` social link field (display only).

### 1.5 Configuration

- `config/services.php` has `instagram` (`token`, `user_id`, `app_id`, `app_secret`) and `facebook` (`page_id`, `page_access_token`) blocks. **No `threads` block.**
- `.env`/`.env.example` keys: `INSTAGRAM_APP_ID`, `INSTAGRAM_APP_SECRET`, `INSTAGRAM_ACCESS_TOKEN`, `INSTAGRAM_BUSINESS_ACCOUNT_ID`. **No Threads keys.**
- Redirect URIs are built from `config('app.url') . '/instagram/oauth/callback'` and must be registered in the Meta app.

---

## 2. Threads API vs Instagram API — What Actually Differs

The OAuth **shape** is identical; the **endpoints, scopes, host, and grant type differ**. This is the crux of the reuse decision.

| Concern | Instagram (current) | Threads (new) |
|---|---|---|
| OAuth authorize host | `https://www.instagram.com/oauth/authorize` | `https://www.threads.net/oauth/authorize` |
| Token exchange | `POST https://graph.instagram.com/v18.0/oauth/access_token` | `GET https://graph.threads.net/access_token` (`client_id`, `client_secret`, `grant_type=authorization_code`, `redirect_uri`, `code`) |
| Long-lived swap | `grant_type=ig_refresh_token` (~60 days) | `grant_type=th_refresh_token` (~60 days) |
| API host | `graph.instagram.com/v18.0` | `graph.threads.net/v1.0` |
| Scopes | `instagram_business_basic`, `instagram_business_content_publish` | `threads_basic`, `threads_content_publish`, (+ optional `threads_read_replies`, `threads_manage_insights`, `threads_manage_replies`) |
| Publish flow | 2-step: `{ig_user}/media` → `{ig_user}/media_publish` | 2-step: `POST /v1.0/{threads_user_id}/threads` (container, `media_type=IMAGE&image_url=...&text=...`) → `POST /v1.0/{threads_user_id}/threads_publish` |
| Account lookup | `GET /{user_id}?fields=id,username` | `GET /v1.0/me?fields=id,username` |
| Meta app | Instagram product enabled | **Threads product must be enabled on the same Meta app** + OAuth redirect URI registered + app review for production |

---

## 3. Answers to Your Questions

### Can the existing OAuth flow be reused for Threads?
**Yes — as a parameterized pattern, not as copy-paste code.**
The controller's logic (state generation/verification, club ownership check, `code`→token exchange, long-lived refresh, account-profile fetch, encrypted persistence into a per-club account row) maps 1:1 to what Threads needs. However, every HTTP call is hard-coded to Instagram hosts/scopes/grant types, so the flow must be extracted into a platform-parameterized service before it can serve both. Storage also needs a Threads-specific account row (see §4).

### Which classes should be reused (used as-is)?
- `SocialPost` — already has `PLATFORM_THREADS` and the `threads` enum value; zero migration.
- `Event::socialPosts() / latestSocialPost() / isPostedToPlatform() / postedAtForPlatform()` — platform-agnostic, ready for Threads.
- `ImgBBService` — the image→public-URL step is identical for Threads.
- `ClubActivityService`, `ClubNotificationService` — platform-agnostic infrastructure.
- The **two-step container→publish pattern** and the **error/logging conventions** from `InstagramService`.

### Which classes should be extended?
- `config/services.php` + `.env` → add a `threads` block (`app_id`, `app_secret`, optional global `token`/`user_id`), mirroring `instagram`.
- `InstagramController` (the social-media dashboard controller) → add `postToThreads()` (+ silent variant) and extend `publishAllPlatforms()` so Threads joins the existing platform loop. Route registration required.
- `Club` model → `threadsAccount()` relation (mirrors `instagramAccount()`).
- The social-media dashboard blade (`instagram/index.blade.php`) → Threads connect/status/publish UI, reusing the Instagram section's structure.
- **Recommended (long-term):** a shared `ThreadsService` built on a common base so it mirrors `InstagramService` instead of duplicating it (see §4).

### Which classes should remain unchanged?
- `InstagramService`, `ClubInstagramService`, `InstagramSyncService`, `InstagramNotificationService`, `ScheduledInstagramPostService`, and the existing console commands — all stay as-is (Instagram-only behavior is fine).
- `Event` — avoid adding 8–10 new `threads_*` columns; track Threads status via `SocialPost` rows. (Add columns only later if you want native Threads scheduling/metrics parity.)
- Existing Instagram OAuth routes and the legacy `/instagram` routes — untouched for backward compatibility.

### Dedicated `ThreadsOAuthController` vs integrate into `InstagramOAuthController`?
**Create a dedicated `ThreadsOAuthController`** (thin), backed by a shared service — do **not** branch the existing Instagram controller with `if ($platform === ...)` blocks. Rationale:
- `InstagramOAuthController` is 100% Instagram-hard-coded (scopes, hosts, route names, session keys, flash messages).
- Piling Threads into it creates a bloated god-controller and makes the Instagram callback path riskier to regress.
- Two thin platform controllers over one shared `MetaOAuthService` keeps both flows readable, testable, and independently maintainable — which is the idiomatic Laravel layering (controllers stay thin, logic lives in services).

### Is there duplicated logic that should be refactored into a shared `MetaOAuthService`?
**Yes — several clear duplication candidates:**

1. **OAuth ceremony** — club-ownership guard, `state` generation + session storage + verification, `code`→token exchange, long-lived-token refresh, account-profile fetch, credential persistence. All of this is currently duplicated only once (Instagram), but Threads would make it twice — the classic threshold for extraction.
2. **Two-step media publish** — `InstagramService::createMediaContainer()/publishMedia()` will be near-identical to Threads' container/publish calls (only host, endpoint path, and field names differ). Extract a shared base `MetaGraphService` (HTTP helpers, error parsing, two-step publish skeleton) with `InstagramService` and `ThreadsService` extending it.
3. **Facebook post is already inlined in `InstagramController`** — it would benefit from being extracted into a `FacebookService` at the same time (optional but recommended, otherwise the controller keeps growing).

---

## 4. Recommended Clean Architecture (Laravel Best Practices)

```
app/
├─ Services/
│  ├─ Meta/
│  │  ├─ MetaOAuthService.php          # NEW — generic OAuth ceremony, platform-parameterized
│  │  ├─ MetaPlatformConfig.php        # NEW — per-platform: host, authorize_url, scopes,
│  │  │                                #        grant types, route names, redirect path, storage
│  │  └─ MetaGraphService.php          # NEW (optional but recommended) — HTTP client,
│  │                                   #        error parsing, 2-step publish skeleton
│  ├─ InstagramService.php             # EXTEND — extend MetaGraphService
│  ├─ ThreadsService.php               # NEW — extends MetaGraphService (container/publish/insights)
│  ├─ FacebookService.php              # NEW (optional) — absorb postImageToFacebook()
│  └─ ... (Instagram-only services unchanged)
├─ Http/Controllers/Web/
│  ├─ InstagramOAuthController.php     # REFACTOR → delegate to MetaOAuthService (backward-compatible)
│  ├─ ThreadsOAuthController.php       # NEW — thin wrapper over MetaOAuthService
│  ├─ InstagramController.php          # EXTEND → postToThreads() + publishAllPlatforms()
│  └─ ...
├─ Models/
│  ├─ ThreadsAccount.php               # NEW — mirrors InstagramAccount (encrypted token)
│  └─ Club.php                         # EXTEND → threadsAccount() relation
└─ database/migrations/
   └─ create_threads_accounts_table.php  # NEW — mirrors instagram_accounts (unique club_id)
```

**Key design decisions:**

1. **`MetaOAuthService`** takes a platform descriptor (config array) and exposes:
   `buildAuthorizeUrl(club, platform)`, `handleCallback(request, platform)`, `verifyState()`, `exchangeCode()`, `exchangeForLongLivedToken()`, `fetchAccount()`, `persistAccount()`. Each platform controller supplies only its own route names, session keys, redirect path, and scopes.
2. **Storage:** create a `threads_accounts` table mirroring `instagram_accounts` (encrypted `access_token`, `threads_username`, `threads_user_id`, `is_active`, `token_expires_at`, `connection_method`), unique per `club_id`. 
   - *Pragmatic choice* — zero risk to the working Instagram flow.
   - *Longer-term alternative* — a single polymorphic/generic `social_accounts` table with a `platform` discriminator. Cleaner conceptually, but requires touching `ClubInstagramService`, `InstagramSyncService`, and the Instagram relation; defer it unless you plan a third platform soon.
3. **Posting:** `ThreadsService::postImage($imageUrl, $caption)` implements the two-step `threads` container → `threads_publish` calls on `graph.threads.net/v1.0`, reusing the shared `MetaGraphService` skeleton. Add `postImageWithCustomCredentials()` mirroring Instagram for per-club accounts.
4. **Status tracking:** record Threads results as `SocialPost` rows with `PLATFORM_THREADS` — no migration and automatic reuse of `latestSocialPost()/isPostedToPlatform()`.
5. **Do NOT add `threads_*` columns to `events` initially.** Add a `threads_scheduled_at`-style set only if/when Threads scheduling is required (mirroring the `instagram_*` scheduling migration pattern).

---

## 5. Step-by-Step Implementation Plan (no code)

### Phase A — Meta Developer setup (external, prerequisite)
1. On the existing Meta app, enable the **Threads** product (or create a new app with it).
2. Register the Threads OAuth redirect URI, e.g. `https://<app-url>/threads/oauth/callback`, under the Threads product's "Redirect URIs".
3. Request scopes: `threads_basic`, `threads_content_publish` (add `threads_read_replies`, `threads_manage_insights` if metrics are wanted).
4. Note that public publishing requires **app review** in production (same as Instagram). Test mode works for admins/testers.

### Phase B — Configuration
5. Add a `threads` block to `config/services.php` (`app_id`, `app_secret`, optional global `token`/`user_id`).
6. Add `.env` keys (`THREADS_APP_ID`, `THREADS_APP_SECRET`, …) and update `.env.example` + any config docs.

### Phase C — Data layer
7. Write migration `create_threads_accounts_table` (mirror `instagram_accounts`, unique `club_id`).
8. Create `ThreadsAccount` model with encrypted-token mutator/accessor (`getDecryptedToken()`, `setAccessTokenAttribute()`, `isTokenValid()`), matching `InstagramAccount`.
9. Add `Club::threadsAccount()` HasOne relation.

### Phase D — OAuth service + controllers
10. Create `MetaOAuthService` by extracting the generic ceremony from `InstagramOAuthController` (state, exchange, long-lived refresh, profile fetch, persistence), parameterized by platform.
11. Create `ThreadsOAuthController` (redirect/callback/fetch-account) delegating to `MetaOAuthService` with Threads config; add routes `/threads/oauth/redirect/{clubId}`, `/threads/oauth/callback`, `/threads/oauth/fetch-account`.
12. Refactor `InstagramOAuthController` to delegate to the same service (verify the existing flow still passes; keep old routes/behavior intact).

### Phase E — Publishing
13. Create `MetaGraphService` (optional step) and `ThreadsService::postImage()` (two-step container → publish on `graph.threads.net/v1.0`).
14. Extend `InstagramController` with `postToThreads()` + silent variant; wire Threads into `publishAllPlatforms()`; register `/social-media/events/{event}/threads` route.
15. Record results via `recordSocialPost(..., PLATFORM_THREADS, ...)` — reuse the existing private helper.

### Phase F — UI
16. Add Threads connect/status/publish section to `resources/views/instagram/index.blade.php` (reuse the Instagram card/modal structure).
17. Add the "Connect with Threads" OAuth button to `resources/views/club-profile/edit.blade.php` alongside Instagram.

### Phase G — Operations & verification
18. Manual test: OAuth connect → verify `threads_accounts` row (encrypted) → post an event to Threads → confirm `social_posts` row with `platform='threads'`.
19. (Optional, only if parity is wanted) Threads scheduling command + `Kernel` schedule entry, mirroring the Instagram scheduler.
20. (Optional, only if parity is wanted) `ThreadsSyncService` for insights, reusing the milestone/notification services.
21. Regression test the existing Instagram + Facebook flows (`publish-all` must still work).
22. Document the new env keys and Meta redirect URI in the project's integration docs.

---

## 6. Summary Verdict

- **Reusable as-is:** `SocialPost`, `ImgBBService`, `Event` platform helpers, club activity/notification services, the two-step publish pattern.
- **Extend:** `InstagramController`, `Club`, `config/services.php`, social-media dashboard blade.
- **Refactor into shared service:** `MetaOAuthService` (and ideally `MetaGraphService`), because Instagram + Threads duplicate the OAuth ceremony and two-step publish flow.
- **New:** `ThreadsOAuthController`, `ThreadsService`, `ThreadsAccount` + migration, Threads routes/config/UI.
- **Dedicated controller, not integration:** keep `InstagramOAuthController` intact; add a thin `ThreadsOAuthController` on top of the shared service.
- **No DB change for post tracking:** `social_posts` already supports `threads`. Only a new `threads_accounts` table is required for per-club credentials.
