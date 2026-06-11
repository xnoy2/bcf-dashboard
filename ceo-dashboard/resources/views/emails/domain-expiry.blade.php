<!doctype html>
<html lang="en">
<head><meta charset="utf-8"></head>
<body style="margin:0; padding:0; background:#F4F2F7; font-family: 'Segoe UI', Roboto, Arial, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
    <tr><td align="center">
        <table role="presentation" width="620" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:14px; overflow:hidden; border:1px solid #E8E4EE;">
            {{-- Header --}}
            <tr><td style="background:#3B2A4A; padding:22px 28px;">
                <span style="color:#ffffff; font-size:19px; font-weight:800;">CEO <span style="color:#C8A24B;">Dashboard</span></span>
                <span style="color:#B8AECB; font-size:11px; letter-spacing:2px; text-transform:uppercase;">&nbsp;&nbsp;Renewal Alert</span>
            </td></tr>

            <tr><td style="padding:26px 28px 8px;">
                <h2 style="margin:0 0 6px; color:#2A2230; font-size:18px;">Domains needing attention</h2>
                <p style="margin:0 0 16px; color:#7A7080; font-size:13px;">
                    The following domains are expired or expiring within {{ $windowDays }} days.
                    Renew them in GoDaddy to avoid losing the domain or taking the website/email offline.
                </p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                    <tr style="background:#FAF8FC;">
                        <th align="left"  style="padding:9px 10px; font-size:11px; color:#7A7080; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #ECE8F1;">Domain</th>
                        <th align="left"  style="padding:9px 10px; font-size:11px; color:#7A7080; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #ECE8F1;">Account</th>
                        <th align="left"  style="padding:9px 10px; font-size:11px; color:#7A7080; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #ECE8F1;">Expires</th>
                        <th align="left"  style="padding:9px 10px; font-size:11px; color:#7A7080; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #ECE8F1;">Status</th>
                        <th align="center" style="padding:9px 10px; font-size:11px; color:#7A7080; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #ECE8F1;">Auto-renew</th>
                    </tr>
                    @foreach($domains as $d)
                        @php $days = $d['days_until'] ?? null; @endphp
                        <tr>
                            <td style="padding:10px; font-size:14px; color:#2A2230; font-weight:600; border-bottom:1px solid #F3F0F7;">{{ $d['domain'] }}</td>
                            <td style="padding:10px; font-size:13px; color:#7A7080; border-bottom:1px solid #F3F0F7;">{{ $d['account'] }}</td>
                            <td style="padding:10px; font-size:13px; color:#2A2230; border-bottom:1px solid #F3F0F7;">{{ $d['expires'] }}</td>
                            <td style="padding:10px; border-bottom:1px solid #F3F0F7;">
                                @if($days !== null && $days < 0)
                                    <span style="background:#2A2230; color:#fff; font-size:11px; font-weight:700; padding:3px 9px; border-radius:6px;">EXPIRED {{ abs($days) }}d ago</span>
                                @else
                                    <span style="background:#B5495B; color:#fff; font-size:11px; font-weight:700; padding:3px 9px; border-radius:6px;">{{ $days }} days left</span>
                                @endif
                            </td>
                            <td align="center" style="padding:10px; font-size:13px; border-bottom:1px solid #F3F0F7;">
                                {!! ($d['renew_auto'] ?? false) ? '<span style="color:#4E7C59; font-weight:700;">on</span>' : '<span style="color:#B5495B; font-weight:700;">OFF</span>' !!}
                            </td>
                        </tr>
                    @endforeach
                </table>

                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:22px 0 6px;">
                    <tr>
                        <td style="background:#3B2A4A; border-radius:9px;">
                            <a href="https://dcc.godaddy.com/control/portfolio" target="_blank"
                               style="display:inline-block; padding:11px 22px; color:#ffffff; font-size:13px; font-weight:700; text-decoration:none;">
                                Renew in GoDaddy →
                            </a>
                        </td>
                        <td style="width:10px;"></td>
                        <td style="border:1px solid #D9CFE4; border-radius:9px;">
                            <a href="{{ config('app.url') }}/renewals" target="_blank"
                               style="display:inline-block; padding:11px 22px; color:#3B2A4A; font-size:13px; font-weight:700; text-decoration:none;">
                                View in CEO Dashboard
                            </a>
                        </td>
                    </tr>
                </table>
            </td></tr>

            <tr><td style="padding:14px 28px 22px;">
                <p style="margin:0; color:#A99FB3; font-size:11px;">
                    Sent automatically by CEO Dashboard · daily check at 08:00 · alert window: {{ $windowDays }} days
                </p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
