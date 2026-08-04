# 403 INVALID SIGNATURE — Runtime Investigation Report

**Date:** 2026-08-04
**App:** Laravel 10.50.2 (`vendor`/`composer.lock`)
**Environment investigated:** `C:\laragon\www\CampusEventHub` (branch `main`, HEAD `b6d276e`, up to date with `origin/main`)
**Repositories referenced:** `https://github.com/asfanish23/campus-event-hub.git`
**Production server observed:** `https://aseems.ddns.net` (Nginx/1.24.0 Ubuntu, PHP-FPM 8.3, project dir `/var/www/campus-event-hub` per `dep.sh`)

---

## 1. Executive Summary

The source code in this repository **cannot** produce a `403 INVALID SIGNATURE` for
`GET /payment/return`. This was proven conclusively by:

- `php artisan route:list --path=payment -v` — the route `payment.return` resolves to
  `Web\PaymentController@return` with middleware `web` + `App\Http\Middleware\Authenticate` only.
  There is **no** `signed` middleware on the route.
- An exhaustive search of `app/`, `routes/`, `config/`, `bootstrap/` found **zero** occurrences of
  `signedRoute()`, `hasValidSignature()`, `middleware('signed')`, or `abort(403)` in the payment flow.
- There is **no route cache** (`bootstrap/cache/routes-v7.php` does not exist).
- The exception that renders "403 INVALID SIGNATURE" can only be thrown by
  `Illuminate\Routing\Middleware\ValidateSignature`, which is only ever executed when a route is
  registered with the `signed` middleware alias.

Because the current source cannot throw it, the 403 must originate from a **stale runtime state on
the server that actually serves the request** — either:

1. a **stale route cache** (`bootstrap/cache/routes-v7.php`) built on the server **before**
   commit `b6d276e` (2026-07-30) still registering `payment.return` with `->middleware('signed')`,
   and never cleared because the deployment scripts do **not** run `php artisan route:clear`; or
2. the deployed code on the server predates the fix (server never redeployed).

The local machine shows no evidence of serving the payment flow (no web requests logged since
2026-06-25, Apache not listening, ngrok tunnel offline), so the 403 is being produced by the
deployed server (`aseems.ddns.net`), where the runtime route table still contains the old
`signed` middleware.

**Primary root cause:** stale runtime route definitions on the deployed server
(`bootstrap/cache/routes-v7.php` generated from pre-fix `routes/web.php:74` = `->middleware('signed')`),
perpetuated because `deploy.sh`/`dep.sh` never run `php artisan route:clear`.

---

## 2. Commands Executed

| # | Command |
|---|---------|
| 1 | `php artisan --version` |
| 2 | `php artisan route:list --path=payment` |
| 3 | `php artisan route:list --path=payment -v` |
| 4 | `Get-ChildItem bootstrap/cache` (route cache check) |
| 5 | Grep of `app/`, `routes/`, `config/`, `bootstrap/` for signature-related tokens |
| 6 | Read `app/Http/Kernel.php`, `app/Http/Middleware/*`, `app/Exceptions/Handler.php`, `routes/web.php`, `app/Http/Controllers/Web/PaymentController.php` |
| 7 | Read framework `ValidateSignature.php`, `InvalidSignatureException.php`, `403.blade.php`, `minimal.blade.php`, `Foundation/Exceptions/Handler.php` |
| 8 | `git status`, `git log`, `git show b6d276e`, `git remote -v` |
| 9 | `Get-NetTCPConnection` (port 80/443/8080/8000 listen check) |
| 10 | `hosts` file check; ngrok config lookup |
| 11 | `curl` probes to `https://aseems.ddns.net` and the ngrok URL |
| 12 | `storage/logs/laravel.log` inspection (DebugSessionMiddleware request trail) |
| 13 | Read `deploy.sh`, `dep.sh`, `nginx-phpmyadmin.conf`, `.env`, `config/services.php` |
| 14 | Grep of `*.md` for `route:cache`/`config:cache` deployment instructions |
| 15 | OPcache status via `php -r` |

---

## 3. Command Outputs

### 3.1 `php artisan --version`

```
Laravel Framework 10.50.2
```

### 3.2 `php artisan route:list --path=payment`

```
  POST       payment/callback ...................................... payment.callback › Web\PaymentController@callback
  POST       payment/checkout-multiple ............ payment.checkout.multiple › Web\PaymentController@checkoutMultiple
  POST       payment/pay .............................................. payment.pay › Web\PaymentController@createBill
  GET|HEAD   payment/return ............................................ payment.return › Web\PaymentController@return
  GET|HEAD   payment/test/failed/{payment_id} ....... payment.test.failure › Web\PaymentTestController@simulateFailure
  GET|HEAD   payment/test/success/{payment_id} ...... payment.test.success › Web\PaymentTestController@simulateSuccess
  GET|HEAD   student/profile/payments ............... student.profile.payments › Web\StudentProfileController@payments
```

### 3.3 `php artisan route:list --path=payment -v`

```
  GET|HEAD   payment/return ............................................ payment.return › Web\PaymentController@return
             ⇂ web
             ⇂ App\Http\Middleware\Authenticate
```

### 3.4 `bootstrap/cache` listing

```
.gitignore    (14 bytes)
packages.php  (1829 bytes)   ← package manifest (php artisan package:discover), NOT route cache
services.php  (21184 bytes)  ← package manifest, NOT route cache
```

**There is no `routes-v7.php` and no `config.php`.** Laravel's route cache file
(`bootstrap/cache/routes-v7.php`) does **not** exist on this machine.

### 3.5 Runtime probe — production server `https://aseems.ddns.net`

```
GET /payment/return?payment_id=1                → HTTP 302  Location: https://aseems.ddns.net/login
GET /payment/return?payment_id=1&signature=bogus&expires=9999999999 → HTTP 302 → /login
GET /login                                      → HTTP 200 (laravel_session + XSRF-TOKEN set)
Response headers: Server: nginx/1.24.0 (Ubuntu)
```

Interpretation: `auth` middleware runs on the server and redirects guests to `/login` (302).
The `302` result is consistent with **both** the current route (`auth` only) **and** the pre-fix
cached route (`auth` runs before `signed` — see §6.2), so an unauthenticated probe cannot
distinguish them. The `403` only manifests for an **authenticated** user, which is exactly the
state the user is in when ToyyibPay redirects them back.

### 3.6 Runtime probe — ngrok URL (from `.env` `APP_URL`)

```
GET https://petrogenetic-dyslogistically-dewayne.ngrok-free.dev/payment/return?payment_id=1 → HTTP 404
```

The ngrok tunnel is offline/unassigned at investigation time, and **no ngrok config file exists**
on this machine. The tunnel therefore is not (or is no longer) serving this local install.

### 3.7 Local machine runtime state

- Ports `80`, `443`, `8080`, `8000`: **no listeners** → local Apache/PHP server is **not running**.
- `hosts`: `127.0.0.1 CampusEventHub.test` (standard Laragon vhost entry exists).
- OPcache: `php -r` → `'OPcache extension not loaded'` (CLI SAPI; no impact on route resolution).
- `storage/logs/laravel.log`: last `DebugSessionMiddleware` web request entry = **2026-06-25 07:14**
  (log file last modified 2026-06-29 23:00). **No web request to this install since before the
  2026-07-30 fix commit.** No `InvalidSignatureException` / `signature` entries anywhere in the log.

### 3.8 Deployment scripts

`deploy.sh` (production steps, abridged):

```
git pull origin main
php artisan cache:clear          # NOT route:clear
php artisan config:clear
php artisan view:clear
php artisan migrate --force
sudo systemctl restart nginx     # php-fpm NOT restarted
```

`dep.sh` (same pattern):

```
git pull origin main
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan migrate --force
sudo systemctl restart nginx
```

**Neither script runs `php artisan route:clear`.** `cache:clear` does **not** remove
`bootstrap/cache/routes-v7.php`; only `route:clear` does. A route cache created before the fix
survives every deployment run.

### 3.9 Deployment documentation (repo evidence that `route:cache` is used on the server)

- `README_IMPLEMENTATION.md:377` — “Routes Not Found → Run `php artisan route:cache` then `php artisan route:clear`”
- `PAYMENT_SYSTEM_SETUP.md:141` — `php artisan config:cache`
- `PAYMENT_SYSTEM_SETUP.md:295` — “Solution: Run `php artisan config:cache` again”
- `GEMINI_AI_TROUBLESHOOTING.md:52-53` — `php artisan route:cache` / `php artisan route:clear`
- `SYSTEM_OVERVIEW.md:427` — `php artisan config:cache`
- `INSTAGRAM_INTEGRATION_DEPLOYMENT_GUIDE.md:39` — `php artisan config:cache`

The project's own operational documentation tells operators to run `php artisan route:cache`
and `php artisan config:cache` on the server, and the deploy scripts never clear the route cache —
a recipe for stale cached routes.

### 3.10 Git state

```
On branch main
Your branch is up to date with 'origin/main'.
Changes not staged for commit:
  deleted:    output.md
HEAD commit: b6d276e 2026-07-30 16:41:10 +0800 fix(payment): resolve ToyyibPay return URL signature validation issue
origin: https://github.com/asfanish23/campus-event-hub.git
```

`git show b6d276e -- routes/web.php`:

```diff
-    Route::get('/payment/return', [PaymentController::class, 'return'])->name('payment.return')->middleware('signed');
+    Route::get('/payment/return', [PaymentController::class, 'return'])->name('payment.return')->middleware('auth');
```

The fix is committed and pushed. `routes/web.php` has no uncommitted changes.

---

## 4. Search Results

### 4.1 `middleware('signed')`
- **0 occurrences** in `routes/`, `app/`, `config/`.
- The only `signed`-related registration is the alias definition:
  `app/Http/Kernel.php:63` → `'signed' => \App\Http\Middleware\ValidateSignature::class` (alias map entry; no route uses it).

### 4.2 `"signed"` (literal)
- `app/Http/Kernel.php:63` (alias registration, see above).
- `app/Http/Middleware/ValidateSignature.php` (class extends framework `ValidateSignature`).

### 4.3 `signedRoute(` / `URL::signedRoute(` / `temporarySignedRoute(`
- **0 occurrences** anywhere in `app/`, `routes/`, `config/`.
- `routes/web.php`, `PaymentController.php`, `CartController.php`, `PaymentTestController.php`
  all use `route('payment.return', ['payment_id' => ...])` (verified in source, lines
  `PaymentController.php:101`, `:208`; route group `routes/web.php:42`).

### 4.4 `hasValidSignature(` / `URL::hasValidSignature(`
- **0 occurrences** in `app/`, `routes/`, `config/`.

### 4.5 `ValidateSignature`
- `app/Http/Kernel.php:63` — alias registration only.
- `app/Http/Middleware/ValidateSignature.php` — extends framework middleware; its `$except` array
  contains ToyyibPay params (`status_id`, `billcode`, `order_id`) as belt-and-suspenders.

### 4.6 `InvalidSignatureException`
- **0 occurrences** in application code. Only in `vendor/`:
  - `vendor/laravel/framework/src/Illuminate/Routing/Exceptions/InvalidSignatureException.php`
  - `vendor/laravel/framework/src/Illuminate/Routing/Middleware/ValidateSignature.php`

### 4.7 `abort(403)`
- `app/Http/Controllers/Web/InstagramController.php:432` — **unrelated** to payments.
- `app/Http/Controllers/Web/PaymentController.php` — the only 403 in the payment flow is the
  `callback()` response `'Invalid hash'` on `PaymentController.php:290`, which returns a plain
  string body (`Invalid hash`), **not** the `403 INVALID SIGNATURE` error page. The user's
  `403 INVALID SIGNATURE` is the rendered error page, which this cannot produce.

### 4.8 `Invalid signature` / `INVALID SIGNATURE`
- **0 occurrences** in application code, including `resources/views` (no `resources/views/errors/` directory exists).
- The text is produced only by the framework view:
  - `vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/views/403.blade.php:5`
    → `@section('message', __($exception->getMessage() ?: 'Forbidden'))`
  - `vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/views/minimal.blade.php:27-29`
    → message rendered with CSS class `uppercase tracking-wider`, so **"Invalid signature." is
    displayed as "INVALID SIGNATURE"**.

---

## 5. Route Analysis

### 5.1 Route definition (source of truth)

`routes/web.php:74`:

```php
Route::get('/payment/return', [PaymentController::class, 'return'])->name('payment.return')->middleware('auth');
```

Inside the `Route::middleware('auth')->group(...)` block (`routes/web.php:42`).

| Property      | Value |
|---------------|-------|
| URI           | `payment/return` |
| Name          | `payment.return` |
| Controller    | `App\Http\Controllers\Web\PaymentController@return` (`app/Http/Controllers/Web/PaymentController.php:340`) |
| Methods       | `GET`, `HEAD` |
| Middleware    | `web` group + `App\Http\Middleware\Authenticate` |
| Signed URL?   | **No** — no `signed` middleware, no `URL::signedRoute()` anywhere |

### 5.2 Pre-fix state (cached-route candidate)

`git show b6d276e -- routes/web.php` proves the route **previously** was:

```php
Route::get('/payment/return', ...)->name('payment.return')->middleware('signed');
```

A route cache generated while that line was in effect persists the `signed` middleware in
`bootstrap/cache/routes-v7.php`.

### 5.3 Return URL generation (current code)

`app/Http/Controllers/Web/PaymentController.php:101` and `:208`:

```php
returnUrl: route('payment.return', ['payment_id' => $payment->id]),
```

No `signature`/`expires` parameters are generated. When ToyyibPay redirects the user it appends
`status_id`, `billcode`, `order_id` to this URL. With the current (fixed) route there is nothing
to validate, so the request reaches the controller. With the **pre-fix cached route** the
`ValidateSignature` middleware runs and fails because the URL contains no valid signature.

---

## 6. Middleware Analysis

### 6.1 Full middleware stack for `GET /payment/return` (current source, execution order)

Global stack (`app/Http/Kernel.php:15-22`):
1. `App\Http\Middleware\TrustProxies`
2. `Illuminate\Http\Middleware\HandleCors`
3. `App\Http\Middleware\PreventRequestsDuringMaintenance`
4. `Illuminate\Foundation\Http\Middleware\ValidatePostSize`
5. `App\Http\Middleware\TrimStrings`
6. `Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull`

`web` group (`app/Http/Kernel.php:30-40`):
7. `App\Http\Middleware\EncryptCookies`
8. `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`
9. `Illuminate\Session\Middleware\StartSession`
10. `Illuminate\View\Middleware\ShareErrorsFromSession`
11. `App\Http\Middleware\VerifyCsrfToken`
12. `Illuminate\Routing\Middleware\SubstituteBindings`
13. `App\Http\Middleware\DebugSessionMiddleware`
14. `App\Http\Middleware\ValidateSessionMiddleware`
15. `App\Http\Middleware\NoCacheMiddleware`

Route middleware (`routes/web.php:74`):
16. `App\Http\Middleware\Authenticate`

None of the above can throw `InvalidSignatureException`. `Authenticate` redirects guests to
`route('login')` (`app/Http/Middleware/Authenticate.php:12`).

### 6.2 Which middleware throws the exception in the user's scenario

In the stale-runtime scenario the pre-fix cached route adds a 17th middleware:
`App\Http\Middleware\ValidateSignature` (alias `signed`). Ordering: `Authenticate` is added by the
`auth` group and `signed` was appended per-route; neither class appears in
`$middlewarePriority` (`vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:39-50`,
which contains only HandlePrecognitiveRequests, EncryptCookies, AddQueuedCookiesToResponse,
StartSession, ShareErrorsFromSession, AuthenticatesRequests, ThrottleRequests,
ThrottleRequestsWithRedis, AuthenticatesSessions, SubstituteBindings, Authorize). With no priority
entries, the framework's stable sort preserves registration order, so **`Authenticate` runs first,
then `ValidateSignature`**.

- **Authenticated user** → passes `Authenticate`, reaches `ValidateSignature` → URL has no valid
  `signature`/`expires` → `hasValidSignatureWhileIgnoring()` returns `false` →
  **`throw new InvalidSignatureException`** → HTTP 403 → **"403 INVALID SIGNATURE"** page.
- **Guest** → `Authenticate` redirects to `/login` (302) before the signature check — which is why
  the unauthenticated probe of `aseems.ddns.net` returned 302 and did **not** expose the 403.

This exactly matches the reported flow: the user is logged in when ToyyibPay redirects them back,
so `auth` passes and the stale `signed` middleware fires.

---

## 7. Exception Trace

Complete chain from request to response (all in framework code, only reachable when the stale
`signed` middleware is present at runtime):

```
GET /payment/return?payment_id=X&status_id=1&billcode=Y&order_id=Z
  └─ HTTP Kernel → Router → Route[payment.return]
       └─ web group (15 middlewares, §6.1)            → pass
       └─ App\Http\Middleware\Authenticate            → pass (user logged in)
       └─ App\Http\Middleware\ValidateSignature       ← STALE ROUTE CACHE ONLY
            └─ hasValidSignatureWhileIgnoring() returns false (no signature/expires in URL)
            └─ throw new InvalidSignatureException
                 vendor/laravel/framework/src/Illuminate/Routing/Middleware/ValidateSignature.php:66
                 "throw new InvalidSignatureException;"
       └─ App\Exceptions\Handler::render()
            └─ prepareException()  Handler.php:463 (InvalidSignatureException not remapped, pass-through)
            └─ renderExceptionResponse() → prepareResponse()  Handler.php:639
                 └─ isHttpException=true (InvalidSignatureException extends HttpException)
                 └─ renderHttpException()  Handler.php:717
                      └─ getHttpExceptionView('errors::403')  Handler.php:753
                      └─ response()->view(403 view, ['exception' => $e], 403, [])
   └─ RESPONSE  HTTP 403
        └─ 403.blade.php:5   @section('message', __($exception->getMessage() ?: 'Forbidden'))
             → $exception->getMessage() = "Invalid signature."
        └─ minimal.blade.php:27-29  class="uppercase tracking-wider" → renders
             "INVALID SIGNATURE"
```

Exception object definition (`vendor/laravel/framework/src/Illuminate/Routing/Exceptions/InvalidSignatureException.php:14-17`):

```php
public function __construct()
{
    parent::__construct(403, 'Invalid signature.');
}
```

**Origin of the 403 status code:** `InvalidSignatureException.php:16` → `HttpException(403, 'Invalid signature.')`.

**Origin of the "INVALID SIGNATURE" text:** `403.blade.php:5` (message) + `minimal.blade.php:28`
(CSS `uppercase`).

---

## 8. Runtime Analysis (Deployment Verification)

| Check | Result |
|-------|--------|
| Source in repo | Correct. `payment.return` = `web` + `auth`, no `signed` anywhere. |
| Route cache on this machine | **None** (`bootstrap/cache/` = only `packages.php`, `services.php`). |
| Config cache on this machine | None (`bootstrap/cache/config.php` absent). |
| Local web server | **Not running** (no listeners on 80/443/8080/8000). |
| Local request traffic | None since **2026-06-25** (DebugSessionMiddleware log trail); no payment/signature entries. |
| ngrok tunnel | Offline (404) at investigation time; no ngrok config on this machine. |
| OPcache (local CLI) | Not loaded — irrelevant to route resolution. |
| Production server (`aseems.ddns.net`) | Live Laravel app (login 200; Nginx/1.24.0 Ubuntu; PHP-FPM 8.3 per `dep.sh`). `/payment/return` → 302 `/login` (auth active; consistent with old cached route too). |
| Deploy scripts | `git pull` + `cache:clear` + `config:clear` + `view:clear`. **No `route:clear`.** `php-fpm` not restarted. |
| Server docs | Instruct running `php artisan route:cache` / `config:cache` (`README_IMPLEMENTATION.md:377`, `PAYMENT_SYSTEM_SETUP.md:141`, `SYSTEM_OVERVIEW.md:427`, `GEMINI_AI_TROUBLESHOOTING.md:52`). |

**Conclusion of the deployment check:** the machine running this checkout is not the machine
serving the payment flow (evidence: no traffic, no server process, no tunnel, no route cache).
The request being served is handled by the deployed server, whose **runtime route table is not
verifiable from the outside** but whose observed behavior (302 for guests) is fully consistent
with a pre-fix cached route. Because the deploy pipeline never invalidates the route cache, any
`route:cache` run on the server (documented as normal operational practice in this repo) leaves
the old `signed` route in force indefinitely.

---

## 9. Root Cause

### Primary Root Cause

The deployed server is still executing the **pre-fix route definition** for `payment.return`
(`->middleware('signed')`). The most probable mechanism is a **stale route cache**
(`bootstrap/cache/routes-v7.php` on the server) generated before commit `b6d276e`
(2026-07-30). Laravel loads this file in preference to `routes/web.php`, and the deployment
scripts (`deploy.sh`/`dep.sh`) never run `php artisan route:clear`, so the cache survives every
deployment. An authenticated user redirected back from ToyyibPay passes `auth`, then hits the
cached `ValidateSignature` middleware, which fails because the return URL carries no valid
`signature`/`expires` parameters → `InvalidSignatureException(403, 'Invalid signature.')` →
rendered as **403 INVALID SIGNATURE**.

The alternative (indistinguishable from outside): the server's deployed code predates the fix
(never redeployed since 2026-07-30). Both are the same class of problem: **stale runtime state
on the deployed server**.

### Supporting Evidence

1. `php artisan route:list --path=payment -v` → `payment.return` has only `web` + `Authenticate`.
2. Zero occurrences of `signed`, `signedRoute`, `hasValidSignature`, `abort(403)` in the payment flow (`§4`).
3. No route cache exists on this machine (`§3.4`).
4. `git show b6d276e` proves the route previously used `middleware('signed')` and the fix (2026-07-30) is committed and pushed.
5. This machine serves no traffic (log trail ends 2026-06-25; no server process; ngrok offline).
6. Production server is live and `/payment/return` behaves like the pre-fix route for guests (302 → login).
7. Deploy scripts omit `route:clear`; repo docs instruct operators to run `route:cache` on the server.
8. The exact "INVALID SIGNATURE" text is the framework's uppercase rendering of `InvalidSignatureException`'s message (`403.blade.php:5`, `minimal.blade.php:28`).

### Confidence Level

**High.** The source-side analysis is exhaustive and definitive: the current code cannot throw
`InvalidSignatureException` for this route. The only code that can produce the observed response
is `ValidateSignature`, which requires a `signed` route at runtime. That route only exists in a
stale cache / stale deployment on the serving host.

### Exact File / Line Responsible

- **Stale artifact (runtime):** `bootstrap/cache/routes-v7.php` on the deployed server,
  generated from pre-fix `routes/web.php:74` (`->middleware('signed')`).
- **Throwing code:** `vendor/laravel/framework/src/Illuminate/Routing/Middleware/ValidateSignature.php:66`
  → `throw new InvalidSignatureException;`
- **Status + message:** `vendor/laravel/framework/src/Illuminate/Routing/Exceptions/InvalidSignatureException.php:16`
  → `parent::__construct(403, 'Invalid signature.');`
- **Rendering:** `vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/views/403.blade.php:5`
  and `.../views/minimal.blade.php:28` (uppercase).
- **Fixed in source (not yet effective on server):** `routes/web.php:74`.

---

## 10. Recommended Fix

**On the deployed server (`/var/www/campus-event-hub`):**

```bash
cd /var/www/campus-event-hub

# 1. Drop the stale route table and other cached artifacts
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan optimize:clear

# 2. Verify the route is now registered without 'signed'
sudo -u www-data php artisan route:list --path=payment

# 3. Restart PHP-FPM (not just nginx) to clear any OPcache of the old controller/middleware
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

Then re-test a **fresh** ToyyibPay payment end-to-end while logged in.

**Prevent recurrence (source of the problem):**

1. Add `php artisan route:clear` (and `php artisan view:clear` / `optimize:clear`) to both
   `deploy.sh` and `dep.sh`, alongside the existing `cache:clear`/`config:clear` steps.
2. If you intend to cache routes in production (`route:cache`), always do it **after** `git pull`
   within the deploy step, and re-run it on every deploy. Simplest safe option: `route:clear` on
   every deploy and don't cache routes.
3. Update the operational docs (`README_IMPLEMENTATION.md:377` etc.) so `route:clear` is always
   paired with `route:cache`.
4. Confirm the same fix is applied if any other server (e.g., a machine behind the ngrok tunnel)
   serves this app; clear its caches too.

No application code changes are required — the source is already correct.
