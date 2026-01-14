<?php

namespace App\Http\Controllers\Auth;

use App\Facades\HandleResponseFacade as Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Mail\EmailVerificationMail;
use App\Mail\ForgotPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */

    public function register(UserRequest $request)
    {
        try {
            $clientDomainUrl = $request->headers->get('referer');
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            if ($user) {
                $token = Str::random(60);
                $user->remember_token = $token;
                $user->save();
                $mailData = [
                    'email' => $user->email,
                    'name' => $user->name,
                    'link' => $clientDomainUrl . "verification-email" . "?token=" . $token,
                ];

                Mail::to($user->email)->queue(new EmailVerificationMail($mailData));

                $data['access_token'] =  $user->createToken("access_token")->plainTextToken;
                $data['user'] =  $user;
                return Response::sendResponse("User registered successfully", $data);
            }else{
                return Response::sendError('Error', ['error'=>'User not created']);
            }

        } catch (\Exception $e) {
            return Response::sendError('Error', $e->getMessage(), $e->getCode());
        }
    }

        // resend email auth user
        public function resendEmailVerification(Request $request)
        {
            try {
                $user = auth()->user();
                $clientDomainUrl = $request->headers->get('referer');
                $user = User::where('email', $user->email)->first();
                if ($user) {
                    $token = Str::random(60);
                    $user->remember_token = $token;
                    $user->save();
                    $mailData = [
                        'email' => $user->email,
                        'name' => $user->name,
                        'link' => $clientDomainUrl. "verification-email". "?token=". $token,
                    ];

                    Mail::to($user->email)->queue(new EmailVerificationMail($mailData));

                    return Response::sendResponse("Email sent successfully");
                }else{
                    return Response::sendError('Error', ['error'=>'User not found']);
                }

            } catch (\Exception $e) {
                return Response::sendError('Error', $e->getMessage(), $e->getCode());

            }
        }


    // Email verification
    public function emailVerification(Request $request)
    {
        try {
            $token = $request->token;
            $user = User::where('remember_token', $token)->first();

            if ($user) {
                $user->remember_token = null;
                $user->email_verified_at = now();
                $user->save();
                // Email verification successful
                return Response::sendResponse("Email verification successfully");
            }else{
                return Response::sendError('Email not verified', ['error'=>'The provided token are incorrect']);
            }

        } catch (\Exception $e) {
            return Response::sendError('Error', $e->getMessage(), $e->getCode());
        }
    }


    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // request required email and password
        $request->validate([
            'email' =>'required|email',
            'password' => 'required|min:6',
        ]);
        try {
            $user = User::with('owner:id,user_id')->where('email', $request->email)->first();
            if (! $user || ! Hash::check($request->password, $user->password)) {
                return Response::sendError('Invalid credentials.', ['error'=>'The provided credentials are incorrect']);
            }
            $data['access_token'] =  $user->createToken("access_token")->plainTextToken;
            $data['user'] =  $user;

            return Response::sendResponse('User login successfully.', $data);

        } catch (\Exception $e) {
            return Response::sendError('Error', $e->getMessage(), $e->getCode());
        }
    }


    // Logout user
    public function logout(Request $request)
    {
        try {
            // Retrieve the token ID from the request
            $tokenId = $request->user()->currentAccessToken()->id;

            // Revoke the specific token
            $request->user()->tokens()->where('id', $tokenId)->delete();

            // $user = $request->user();
            // $user->tokens()->delete(); // Invalidate all tokens associated with the user
            return Response::sendResponse("Successfully logged out");
        } catch (\Exception $e) {
            return Response::sendError('Error', $e->getMessage(), $e->getCode());
        }
    }


    // User forgot password
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $token = Str::random(6);

            // Check if a token already exists for the email
            $existingToken = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if ($existingToken) {
                // Update the existing token
                DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->update(['token' => $token, 'created_at' => now()]);
            } else {
                // Insert a new token
                DB::table('password_reset_tokens')->insert([
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => now(),
                ]);
            }

            Mail::to($request->email)->send(new ForgotPasswordMail($token));

            return response()->json(["status" => true, 'message' => 'Password reset email sent.']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Password reset failed. ' . $e->getMessage()], 500);
        }
    }



    // User reset password
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'otp' => 'required|string|min:6',
                'password' => 'required|string|min:6',
            ]);

            $resetToken = DB::table('password_reset_tokens')
                ->where('token', $request->otp)
                ->first();

            if (!$resetToken) {
                return response()->json(['message' => 'Invalid password reset token.'], 400);
            }

            $user = User::where('email', $resetToken->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the used token
            DB::table('password_reset_tokens')
                ->where('token', $request->otp)
                ->delete();

            return response()->json(['message' => 'Password updated successfully.']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Password update failed. ' . $e->getMessage()], 500);
        }
    }

    // User change password
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string|min:6',
                'new_password' => 'required|string|min:6',
            ]);
            $user = auth()->user();
            // check old password
            if (!Hash::check($request->password, $user->password)) {
                return Response::sendError('Invalid old password', ['error'=>'Invalid old password']);
            }
            // // change new password 12 character long with at least one uppercase letter, one lowercase letter, one number, and one special character
            // $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/';
            // if (!preg_match($pattern, $request->password)) {
            //     return Response::sendError('Invalid new password', ['error'=>'Invalid new password']);
            // }
            // update new password to user table 12 character long with at least one uppercase letter, one lowercase letter, one number, and one special character 12 character long with at least one uppercase letter, one
            $user->password =  Hash::make($request->new_password);
            $user->save();
            return Response::sendResponse("Password changed successfully");
        } catch (\Exception $e) {
            return Response::sendError('Error', $e->getMessage(), $e->getCode());
        }
    }

    // Refresh token refresh
    public function refreshToken(Request $request)
    {
        try {
            $user = $request->user();
            $data['access_token'] =  $user->createToken("access_token")->plainTextToken;
            $data['user'] =  $user;
            return Response::sendResponse('User login successfully.', $data);
        } catch (\Exception $e) {
            return Response::sendError('Error', $e->getMessage(), $e->getCode());
        }
    }



}
