<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're on the list! Here's your verification code.</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 520px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background: linear-gradient(120deg, #7e22ce, #4c1d95);
            color: #fef9c3;
            text-align: center;
            padding: 32px 24px;
        }
        .header h1 {
            margin: 0 0 4px;
            font-size: 28px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .body {
            padding: 32px 40px;
            color: #1a1a1a;
            text-align: center;
        }
        .body p {
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 24px;
        }
        .code {
            display: inline-block;
            background: #fef08a;
            color: #1e1b4b;
            font-size: 40px;
            font-weight: 900;
            padding: 16px 32px;
            border-radius: 8px;
            margin: 8px 0 24px;
        }
        .footer {
            padding: 16px 40px 32px;
            color: #6b7280;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Dance Your Ass Off</h1>
        </div>
        <div class="body">
            <p>Use the code below to log in to your RSVP. It expires in one hour.</p>
            <div class="code">{{ $code }}</div>
            <p>If you didn&rsquo;t request this code, you can safely ignore this email.</p>
        </div>
        <div class="footer">
            <p>This code expires in 1 hour.</p>
            <p>Message sent by Tate Bosler &amp; Wendy Lutter for the <em>Dance Your Ass Off</em> party on August 28, 2026. More info at <a href="https://danceyourassoff.party">danceyourassoff.party</a></p>
        </div>
    </div>
</body>
</html>
