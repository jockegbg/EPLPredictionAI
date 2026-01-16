<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Spatie\LaravelPasskeys\Models\Passkey;
use Illuminate\Support\Facades\Auth;

class PasskeyController extends Controller
{
    /**
     * Get the options to register a new passkey.
     */
    public function registerOptions(Request $request, \Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction $generateOptions)
    {
        return $generateOptions->execute($request->user());
    }

    /**
     * Store a new passkey.
     */
    public function store(Request $request, \Spatie\LaravelPasskeys\Actions\StorePasskeyAction $storePasskey)
    {
        $request->validate([
            'passkey' => 'required|string',
            'passkey_options' => 'required|string',
        ]);

        // Store the passkey with all required parameters
        $passkey = $storePasskey->execute(
            authenticatable: $request->user(),
            passkeyJson: $request->input('passkey'),
            passkeyOptionsJson: $request->input('passkey_options'),
            hostName: $request->getHost(),
        );

        // Auto-generate a descriptive name
        $userAgent = $request->userAgent();
        $browser = $this->detectBrowser($userAgent);
        $date = now()->format('Y-m-d');
        $autoName = "{$date} - {$browser}";

        $passkey->update(['name' => $autoName]);

        return back()->with('status', 'passkey-created');
    }

    /**
     * Detect browser/device from user agent
     */
    private function detectBrowser(string $userAgent): string
    {
        if (stripos($userAgent, 'Edg') !== false)
            return 'Edge';
        if (stripos($userAgent, 'Chrome') !== false)
            return 'Chrome';
        if (stripos($userAgent, 'Safari') !== false)
            return 'Safari';
        if (stripos($userAgent, 'Firefox') !== false)
            return 'Firefox';
        if (stripos($userAgent, 'Opera') !== false || stripos($userAgent, 'OPR') !== false)
            return 'Opera';

        // Check for mobile devices
        if (stripos($userAgent, 'iPhone') !== false)
            return 'iPhone';
        if (stripos($userAgent, 'iPad') !== false)
            return 'iPad';
        if (stripos($userAgent, 'Android') !== false)
            return 'Android';

        return 'Desktop';
    }

    /**
     * Remove the specified passkey.
     */
    public function destroy(Passkey $passkey): RedirectResponse
    {
        if ($passkey->authenticatable_id !== Auth::id()) {
            abort(403);
        }

        $passkey->delete();

        return back()->with('status', 'passkey-deleted');
    }
}
