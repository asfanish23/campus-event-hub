<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/png" href="{{ asset('images/uitm_logo.png') }}?v={{ filemtime(public_path('images/uitm_logo.png')) }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Threads Credentials</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f4f5; color: #18181b; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; border: 1px solid #e4e4e7; border-radius: 8px; padding: 32px; max-width: 720px; width: 100%; box-sizing: border-box; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        p { font-size: 14px; color: #52525b; margin: 0 0 20px; }
        pre { background: #18181b; color: #e4e4e7; border-radius: 6px; padding: 16px; overflow-x: auto; font-size: 13px; line-height: 1.6; margin: 0; }
        .warn { font-size: 12px; color: #b45309; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Threads OAuth Credentials</h1>
        <p>Copy these values into your <code>.env</code> file. This page is the only place they will be shown.</p>
        <pre>THREADS_ACCESS_TOKEN={{ $accessToken }}
THREADS_USER_ID={{ $userId }}
THREADS_USERNAME={{ $username }}</pre>
        <p class="warn">Treat the access token like a password. It is valid for 60 days and will need to be regenerated after expiry.</p>
    </div>
</body>
</html>
