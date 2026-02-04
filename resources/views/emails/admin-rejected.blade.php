<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Application Rejected</title>
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
        .rejection-reason {
            background-color: #fef2f2;
            border-left: 4px solid #f5576c;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .rejection-reason strong {
            color: #333;
        }
        .rejection-reason p {
            margin: 5px 0;
            color: #555;
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
        .rejected-badge {
            display: inline-block;
            background-color: #ef4444;
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
            <h1>Application Status Update</h1>
        </div>
        <div class="content">
            <div class="rejected-badge">✗ Rejected</div>
            <h2>Hello {{ $user->name }},</h2>
            <p>Thank you for your interest in becoming an admin for <strong>{{ optional($user->club)->name ?? 'the club' }}</strong>. Unfortunately, your application has been <strong>REJECTED</strong>.</p>
            
            <div class="rejection-reason">
                <strong>Reason for Rejection:</strong>
                <p>{{ $reason }}</p>
            </div>

            <p>If you have any questions or would like to know more about why your application was rejected, please reach out to the Super Admin through the platform.</p>

            <p>You are welcome to reapply in the future or apply for a different role. Thank you for your interest in Campus Event Hub!</p>

            <p style="margin-top: 30px; color: #999; font-size: 14px;">
                If you believe this is a mistake, please contact the Super Admin at the administrative panel.
            </p>
        </div>
        <div class="footer">
            <p>Campus Event Hub | Admin Notification System</p>
            <p>© {{ date('Y') }} All rights reserved.</p>
        </div>
    </div>
</body>
</html>
