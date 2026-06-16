<!doctype html>
<html lang="en">
<head><meta charset="utf-8"></head>
<body style="margin:0; padding:0; background:#F4F2F7; font-family:'Segoe UI',Roboto,Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;"><tr><td align="center">
<table role="presentation" width="640" cellpadding="0" cellspacing="0" style="background:#fff; border-radius:14px; overflow:hidden; border:1px solid #E8E4EE;">
    <tr><td style="background:#3B2A4A; padding:22px 28px;">
        <span style="color:#fff; font-size:19px; font-weight:800;">CEO <span style="color:#C8A24B;">Dashboard</span></span>
        <span style="color:#B8AECB; font-size:11px; letter-spacing:2px; text-transform:uppercase;">&nbsp;&nbsp;Calendar Reminder</span>
    </td></tr>
    <tr><td style="padding:24px 28px 6px;">

    @php
        $row = function ($e) {
            $due = $e->dueDate();
            return '<tr>'
                . '<td style="padding:9px 10px; font-size:14px; font-weight:600; border-bottom:1px solid #F3F0F7;">' . e($e->client_name) . ($e->is_birthday ? ' 🎂' : '') . '</td>'
                . '<td style="padding:9px 10px; font-size:13px; border-bottom:1px solid #F3F0F7;">' . $due->format('D, d M Y') . '</td>'
                . '<td style="padding:9px 10px; font-size:13px; color:#7A7080; border-bottom:1px solid #F3F0F7;">' . e($e->assigned_to ?: 'Unassigned') . '</td>'
                . '<td style="padding:9px 10px; font-size:13px; color:#7A7080; border-bottom:1px solid #F3F0F7;">' . e(\Illuminate\Support\Str::limit($e->address, 40)) . '</td>'
                . '</tr>';
        };
    @endphp

    @if(count($overdue))
        <h2 style="margin:0 0 4px; color:#B5495B; font-size:16px;">⚠️ Overdue — action needed ({{ count($overdue) }})</h2>
        <table role="presentation" width="100%" style="border-collapse:collapse; margin-bottom:18px;">
            {!! collect($overdue)->map($row)->implode('') !!}
        </table>
    @endif

    @if(count($dueSoon))
        <h2 style="margin:0 0 4px; color:#2A2230; font-size:16px;">🔜 Coming up ({{ count($dueSoon) }})</h2>
        <table role="presentation" width="100%" style="border-collapse:collapse; margin-bottom:14px;">
            {!! collect($dueSoon)->map($row)->implode('') !!}
        </table>
    @endif

        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:14px 0 6px;"><tr>
            <td style="background:#3B2A4A; border-radius:9px;">
                <a href="{{ config('app.url') }}/calendar" target="_blank" style="display:inline-block; padding:11px 22px; color:#fff; font-size:13px; font-weight:700; text-decoration:none;">Open the Calendar →</a>
            </td>
        </tr></table>
    </td></tr>
    <tr><td style="padding:10px 28px 22px;">
        <p style="margin:0; color:#A99FB3; font-size:11px;">Sent automatically by CEO Dashboard · daily check at 07:30.</p>
    </td></tr>
</table>
</td></tr></table>
</body>
</html>
