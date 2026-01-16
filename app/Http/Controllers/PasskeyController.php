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
            'passkey' => 'required',
            'passkey_name' => 'nullable|string',
        ]);

        $passkey = $storePasskey->execute($request->user(), $request->all());

        if ($request->filled('passkey_name')) {
            $passkey->update(['name' => $request->passkey_name]);
        }

        return back()->with('status', 'passkey-created');
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
