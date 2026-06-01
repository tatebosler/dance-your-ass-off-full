<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMagicLink
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('rsvp') && ! Auth::check()) {
            $invitationId = $request->query('invitationId');

            if (is_string($invitationId) && $invitationId !== '') {
                $user = User::where('magic_link_token', $invitationId)->first();

                if ($user !== null) {
                    Auth::login($user, remember: true);
                }
            }
        }

        return $next($request);
    }
}
