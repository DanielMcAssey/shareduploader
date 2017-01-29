<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RESTActions;
use App\Models\User;
use App\Models\EmailChange;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class UsersController extends Controller {

    const MODEL = 'App\User';

    use RESTActions;

    /**
     * Get the current user
     *
     * @return Response
     */
    public function getCurrentUser()
    {
        if(!Auth::check())
            return response()->json(['no_auth'], Response::HTTP_FORBIDDEN);

        return response()->json(['current_user' => Auth::user()], Response::HTTP_OK);
    }

    /**
     * Change the user profile details
     *
     * @return Response
     */
    public function changeEmail(Request $request)
    {
        if(!Auth::check())
            return response()->json(['no_auth'], Response::HTTP_FORBIDDEN);

        $this->validate($request, [
            'email' => 'required|email|unique:users',
        ]);

        Auth::user()->emailChanges()->generateToken();
        $email_change = Auth::user()->emailChanges();
        $email_change->new_email = $request->only('email');
        $email_change->save();
        Auth::user()->save();

        // TODO: Send email with token

        return response()->json(['email_change_requested'], Response::HTTP_OK);
    }

    /**
     * Confirm email change
     *
     * @return Redirect
     */
    public function confirmEmailChange(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
        ]);

        $emailChangeData = EmailChange::where('token', $request->only('token'))->first();

        if(is_null($emailChangeData))
            return response()->json(['token_invalid'], Response::HTTP_BAD_REQUEST);

        $expiry_minutes = config('auth.passwords.users.expire', 60);
        $expiry_time = $emailChangeData->updated_at->timestamp + ($expiry_minutes * 60);

        if($expiry_time < time())
            return response()->json(['token_expired'], Response::HTTP_BAD_REQUEST);

        $new_email = $emailChangeData->new_email;
        $emailChangeData->user->email = $new_email;
        $emailChangeData->user->save();
        $emailChangeData->delete();

        return response()->json(['email_changed'], Response::HTTP_OK);
    }

    /**
     * Change the user password
     *
     * @return Response
     */
    public function changePassword(Request $request)
    {
        if(!Auth::check())
            return response()->json(['no_auth'], Response::HTTP_FORBIDDEN);

        $this->validate($request, [
            'old_password' => 'required',
            'new_password' => 'required|min:8',
            'new_password_confirm' => 'required|same:new_password'
        ]);

        $current_password = $request->only('old_password');
        $new_password = $request->only('new_password');

        if(!Hash::check($current_password, Auth::user()->password))
            return response()->json(['mismatched_passwords'], Response::HTTP_BAD_REQUEST);

        Auth::user()->password = Hash::make($new_password);
        Auth::user()->save();

        // TODO: Send Mail on password change

        return response()->json(['password_changed'], Response::HTTP_OK);
    }

    /**
     * Generate a new API Key for current logged in user
     *
     * @return Response
     */
    public function generateApiKey()
    {
        if(!Auth::check())
            return response()->json(['no_auth'], Response::HTTP_FORBIDDEN);

        Auth::user()->generateApiKey();
        Auth::user()->save();

        return response()->json(['api_key' => Auth::user()->api_key], Response::HTTP_OK);
    }

    /**
     * Return API key from current logged in user
     *
     * @return Response
     */
    public function getApiKey()
    {
        if(!Auth::check())
            return response()->json(['no_auth'], Response::HTTP_FORBIDDEN);

        return response()->json(['api_key' => Auth::user()->api_key], Response::HTTP_OK);
    }
}
