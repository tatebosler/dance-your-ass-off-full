<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're invited to DANCE YOUR ASS OFF!</title>
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
        .cta-button {
            display: inline-block;
            background: linear-gradient(120deg, #7e22ce, #6d28d9);
            color: #fef9c3;
            font-size: 16px;
            font-weight: 700;
            padding: 12px 32px;
            border-radius: 8px;
            text-decoration: none;
            margin: 8px 0 24px;
            transition: opacity 0.3s ease;
        }
        .cta-button:hover {
            opacity: 0.9;
        }
        .event-details {
            background: #f9fafb;
            border-left: 4px solid #7e22ce;
            padding: 16px;
            margin: 24px 0;
            text-align: left;
        }
        .event-details p {
            margin: 4px 0;
            font-size: 14px;
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
            <h3>It's time to</h3>
            <h1>Dance Your Ass Off</h1>
        </div>
        <div class="body">
            <p>You are invited to Saint Paul, Minnesota to celebrate Wendy's 59&frac12; and Tate's 29&frac12;!</p>
            <div class="event-details">
                <p><strong>Friday, August 28, 2026</strong></p>
                <p><strong>6:30 PM</strong></p>
                <p><strong>North Garden Theater, 929 7th St W, Saint Paul, MN 55102</strong></p>
            </div>
            <p><strong>Please RSVP by July 13th at 5:00 PM CT.</strong></p>
            <a href="{{ $rsvpUrl }}" class="cta-button">RSVP Now</a>
        </div>
        <div class="footer">
            <p>Message sent by Tate Bosler &amp; Wendy Lutter for the <em>Dance Your Ass Off</em> party on August 28, 2026. More info at <a href="https://danceyourassoff.party">danceyourassoff.party</a></p>
        </div>
    </div>
</body>
</html>
