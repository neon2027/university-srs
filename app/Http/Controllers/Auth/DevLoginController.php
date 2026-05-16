<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DevLoginController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        $user = User::findOrFail($request->integer('user_id'));

        Auth::login($user, remember: true);

        if ($user->hasAnyRole(['super_admin', 'office_admin', 'staff'])) {
            return redirect('/admin');
        }

        if ($user->onboarding_completed_at === null) {
            return redirect()->route('portal.onboarding');
        }

        return redirect()->route('portal.tickets.index');
    }
}
