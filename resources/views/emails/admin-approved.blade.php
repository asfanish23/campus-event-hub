<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Application Approved</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px 20px;
        }
        .content h2 {
            color: #333;
            margin-top: 0;
        }
        .content p {
            color: #666;
            line-height: 1.6;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            background-color: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #5568d3;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            color: #999;
            font-size: 12px;
            border-top: 1px solid #eee;
        }
        .success-badge {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Application Approved!</h1>
        </div>
        <div class="content">
            <div class="success-badge">✓ Approved</div>
            <h2>Hello {{ $user->name }},</h2>
            <p>Great news! Your admin application for <strong>{{ optional($user->club)->name ?? 'the club' }}</strong> has been <strong>APPROVED</strong> by the Super Admin.</p>
            
            <p>You can now log in to Campus Event Hub with your credentials and start managing your club events.</p>
            
            <p><strong>Login Details:</strong></p>
            <ul style="color: #666; line-height: 1.8;">
                <li><strong>Email:</strong> {{ $user->email }}</li>
                <li><strong>Role:</strong> Admin</li>
                <li><strong>Club:</strong> {{ optional($user->club)->name ?? 'N/A' }}</li>
            </ul>

            <a href="{{ config('app.url') }}/login" class="button">Login to Dashboard</a>

            <p style="margin-top: 30px; color: #999; font-size: 14px;">
                If you did not apply for this position, please contact the Super Admin immediately.
            </p>
        </div>
        <div class="footer">
            <p>Campus Event Hub | Admin Notification System</p>
            <p>© {{ date('Y') }} All rights reserved.</p>
        </div>
    </div>
</body>
</html>
