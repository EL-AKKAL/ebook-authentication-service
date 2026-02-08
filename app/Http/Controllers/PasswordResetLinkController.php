<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{

    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        $status = Password::sendResetLink(
            $request->only('email')
        );
        return $status === Password::ResetLinkSent ?
            response()->json(['status' => __($status)]) :
            response()->json(['email' => __($status)]);
    }
}
