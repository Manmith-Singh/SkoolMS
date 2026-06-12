<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\AuditLog;
use App\Models\Master\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    /**
     * Default seed settings that get auto-created the first time the
     * SuperAdmin visits the page so the UI is never empty.
     */
    private const DEFAULTS = [
        'general' => [
            'app_name'             => ['string',  'SchoolMS'],
            'support_email'        => ['string',  'support@schoolms.test'],
            'default_currency'     => ['string',  'USD'],
            'default_timezone'     => ['string',  'UTC'],
            'allow_registration'   => ['boolean', true],
            'maintenance_mode'     => ['boolean', false],
        ],
        'email' => [
            'mail_driver'          => ['string',  'log'],
            'mail_host'            => ['string',  'smtp.mailgun.org'],
            'mail_port'            => ['integer', 587],
            'mail_username'        => ['string',  ''],
            'mail_password'        => ['string',  ''],
            'mail_encryption'      => ['string',  'tls'],
            'mail_from_address'    => ['string',  'no-reply@schoolms.test'],
            'mail_from_name'       => ['string',  'SchoolMS'],
        ],
        'sms' => [
            'sms_provider'         => ['string',  'log'],
            'sms_api_key'          => ['string',  ''],
            'sms_sender_id'        => ['string',  'SCHOOLMS'],
        ],
        'branding' => [
            'primary_color'        => ['string',  '#3b6db5'],
            'logo_url'             => ['string',  ''],
        ],
    ];

    public function index(): View
    {
        $this->ensureDefaults();

        $settings = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('master.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->ensureDefaults();

        $payload = $request->input('settings', []);
        foreach ($payload as $key => $value) {
            $row = SystemSetting::where('key', $key)->first();
            if (! $row) continue;
            // Coerce value to its declared type
            $stored = match ($row->type) {
                'boolean' => $value ? '1' : '0',
                'integer' => (string) (int) $value,
                'json'    => is_string($value) ? $value : json_encode($value),
                default   => (string) $value,
            };
            $row->update(['value' => $stored]);
        }

        AuditLog::record('settings.updated', null, ['keys' => array_keys($payload)]);

        return back()->with('success', 'Settings saved.');
    }

    private function ensureDefaults(): void
    {
        foreach (self::DEFAULTS as $group => $rows) {
            foreach ($rows as $key => [$type, $value]) {
                SystemSetting::firstOrCreate(
                    ['key' => $key],
                    [
                        'group'       => $group,
                        'value'       => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                        'type'        => $type,
                        'description' => null,
                    ]
                );
            }
        }
    }
}
