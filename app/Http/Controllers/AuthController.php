<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Get user for email verification check (only if verification is enabled)
        $user = User::where('email', $request->email)->first();
        
        // Check if email verification is enabled and method exists
        if ($user && config('verify_email.enabled', false) && method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Please verify your email address before logging in.'],
            ]);
        }

        $credentials = $request->only('email', 'password');
        $credentials['is_active'] = true;

        if (Auth::guard('web')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('dashboard'))->with('success', 'Welcome back!');
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    /**
     * Show registration form
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if email verification is enabled
        $verificationEnabled = config('verify_email.enabled', false);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => !$verificationEnabled, // If verification is enabled, user is not active until verified
        ]);

        // Assign default role (agent)
        $defaultRole = Role::where('is_default', true)->first();
        if ($defaultRole) {
            $user->assignRole($defaultRole);
        }

        // If verification is enabled, send verification email and show notice
        if ($verificationEnabled && method_exists($user, 'generateVerificationToken')) {
            // Generate verification token and send email
            $token = $user->generateVerificationToken();
            Mail::to($user->email)->send(new \App\Mail\VerifyEmail($user, $token));

            // Logout the user since they need to verify email first
            Auth::guard('web')->logout();
            $request->session()->flash('verified_email', $user->email);

            return redirect()->route('login')->with('verification_notice', 'We have sent a verification link to your email. Please check your inbox and click the link to activate your account.');
        }

        // If verification is disabled, login directly
        Auth::guard('web')->login($user);

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }

    /**
     * Show profile form
     */
    public function profile()
    {
        $user = Auth::user();
        return view('auth.profile', compact('user'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            // Delete old avatar if exists
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $avatarPath;
        }

        $user->update($updateData);

        if ($request->password) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Create API token for authenticated user
     */
    public function createApiToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account is inactive.'
            ], 403);
        }

        // Revoke existing tokens (optional - remove if you want multiple tokens)
        // $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Token created successfully',
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Verify user's email address
     */
    public function verifyEmail(Request $request)
    {
        $token = $request->route('token');
        
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Invalid verification token.');
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        return redirect()->route('login')->with('success', 'Your email has been verified! You can now login to your account.');
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        $user = Auth::user();

        if (method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        // Generate new verification token and send email
        if (method_exists($user, 'generateVerificationToken')) {
            $token = $user->generateVerificationToken();
            Mail::to($user->email)->send(new \App\Mail\VerifyEmail($user, $token));
        }

        return back()->with('resent', true);
    }
}
