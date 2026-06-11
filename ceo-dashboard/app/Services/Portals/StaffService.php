<?php

namespace App\Services\Portals;

use App\Support\Snapshot;

/**
 * Staff Portal integration (staff.bespokegardenroomsballycastle.co.uk).
 * Uses the consolidated /dashboard plus the summary endpoints.
 */
class StaffService
{
    /** Normalise the roles field (array of strings or of {name}/{role}) to a label. */
    private static function roleNames(mixed $roles): string
    {
        if (is_string($roles)) {
            return $roles;
        }
        if (! is_array($roles)) {
            return '—';
        }
        $names = array_map(function ($r) {
            if (is_string($r)) {
                return $r;
            }
            return $r['name'] ?? $r['role'] ?? $r['title'] ?? '';
        }, $roles);

        $names = array_filter($names);
        return $names ? implode(', ', $names) : '—';
    }

    /** Flatten a value that may be a plain string or a nested object to a label. */
    private static function flatName(mixed $value, array $keys = ['name', 'title']): ?string
    {
        if (is_string($value) || is_numeric($value)) {
            return (string) $value;
        }
        if (is_array($value)) {
            foreach ($keys as $k) {
                if (! empty($value[$k])) {
                    return (string) $value[$k];
                }
            }
        }
        return null;
    }

    public function overview(): array
    {
        return Snapshot::remember('staff:overview', 5, function () {
            $client = PortalClient::for('staff');

            $roster = $client->get('staff', ['per_page' => 100])->json();
            $roster = $roster['data'] ?? $roster ?? [];

            $jobs = $client->get('jobs', ['per_page' => 100])->json();
            $jobs = $jobs['data'] ?? $jobs ?? [];

            return [
                'dashboard'  => $client->get('dashboard')->json() ?? [],
                'staff'      => $client->get('staff/summary')->json() ?? [],
                'attendance' => $client->get('attendance/summary')->json() ?? [],
                'payroll'    => $client->get('payroll/summary')->json() ?? [],
                'jobs'       => $client->get('jobs/summary')->json() ?? [],
                'roster'     => collect($roster)->map(fn ($s) => [
                    'employee_id' => $s['employee_id'] ?? '',
                    'name'        => $s['name'] ?? '—',
                    'email'       => $s['email'] ?? '',
                    'roles'       => self::roleNames($s['roles'] ?? []),
                    'is_active'   => (bool) ($s['is_active'] ?? false),
                    'hire_date'   => $s['hire_date'] ?? null,
                    'contracted'  => $s['contracted_hours'] ?? null,
                ])->all(),
                'jobs_list'  => collect($jobs)->map(fn ($j) => [
                    'title'   => $j['title'] ?? '',
                    'status'  => $j['status'] ?? '',
                    'date'    => $j['date'] ?? null,
                    'start'   => $j['start_time'] ?? null,
                    'end'     => $j['end_time'] ?? null,
                    'project' => self::flatName($j['project'] ?? null),
                    'van'     => self::flatName($j['van'] ?? null, ['registration', 'reg', 'name']),
                    'staff'   => collect($j['staff'] ?? [])->pluck('name')->filter()->implode(', '),
                ])->sortBy('date')->values()->all(),
            ];
        });
    }
}
