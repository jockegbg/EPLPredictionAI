<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LogAuthentication
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(Login $event)
    {
        $method = 'unknown';

        // Determine method based on route or request input
        if ($this->request->routeIs('passkeys.login') || $this->request->has('passkey')) {
            $method = 'passkey';
        } elseif ($this->request->is('login') && $this->request->isMethod('post')) {
            $method = 'password';
        }

        ActivityLog::create([
            'user_id' => $event->user->getAuthIdentifier(),
            'action' => 'login',
            'method' => $method,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'details' => ['url' => $this->request->fullUrl()],
        ]);
    }
}
