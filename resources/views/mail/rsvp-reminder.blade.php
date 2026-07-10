<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminder: Please RSVP for DANCE YOUR ASS OFF!</title>
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
            <p>Dear {{ $firstName }},</p>
            <p>We are getting excited for our party in a few short weeks and we hope to see you there!</p>
            @php
                $formatNames = function(array $names): string {
                    if (count($names) === 1) return $names[0];
                    $last = array_pop($names);
                    return implode(', ', $names) . ' and ' . $last;
                };
            @endphp
            @if ($situation === 'no-responses-multi')
                <p><strong>Your group has not yet responded.</strong> Please RSVP using the link in this email, or log in to <a href="https://danceyourassoff.party">danceyourassoff.party</a>, to let us know if you and your crew are coming.</p>
            @elseif ($situation === 'no-response-single')
                <p><strong>We haven't heard from you yet!</strong> Please RSVP using the link in this email, or log in to <a href="https://danceyourassoff.party">danceyourassoff.party</a>, to let us know if you are coming.</p>
            @elseif ($situation === 'missing-response-multi')
                <p>Your group is almost set — we're just waiting on <strong>{{ $formatNames($missingNames) }}</strong> to respond. Please RSVP so we can finalize plans! <strong>Yes, we do need individual responses per person so we can get a good head count.</strong></p>
            @elseif ($situation === 'all-maybe-multi')
                <p>Your whole group put down "maybe" — we totally understand life can be unpredictable! The deadline is approaching, so please firm up your RSVPs.</p>
            @elseif ($situation === 'maybe-single')
                <p>You put down "maybe" — we totally understand life can be unpredictable! The deadline is approaching, so please firm up your RSVP.</p>
            @else
                <p>Your group has responded, but <strong>{{ $formatNames($maybeNames) }}</strong> put down "maybe". Could you firm that up before the deadline?</p>
            @endif
            <div class="event-details">
                <p><strong>Friday, August 28, 2026</strong></p>
                <p><strong>6:30 PM</strong></p>
                <p><strong>North Garden Theater, 929 7th St W, Saint Paul, MN 55102</strong></p>
            </div>
            <p><strong>Please RSVP by Monday, July 13th at 5:00 PM CT.</strong> If you don't respond by then, we will unfortunately have to mark you and your crew as a "no" &mdash; and then everyone involved would be sad.</p>
            <a href="{{ $rsvpUrl }}" style="display: inline-block; background: linear-gradient(120deg, #7e22ce, #6d28d9); color: #ffffff; font-size: 16px; font-weight: 700; padding: 12px 32px; border-radius: 8px; text-decoration: none; margin: 8px 0 24px;">RSVP Now</a>
            <p>If you experience any sort of technical issues, just reply to this email and we can help get you sorted out.</p>
            <p>We hope to see you soon!</p>
            <p>&mdash; Tate &amp; Wendy</p>
        </div>
        <div class="footer">
            <p>Message sent by Tate Bosler &amp; Wendy Lutter for the <em>Dance Your Ass Off</em> party on August 28, 2026. More info at <a href="https://danceyourassoff.party">danceyourassoff.party</a></p>
        </div>
    </div>
</body>
</html>
