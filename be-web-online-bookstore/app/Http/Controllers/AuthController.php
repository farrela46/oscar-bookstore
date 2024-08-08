<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // public function register(Request $request)
    // {
    //     $messages = [
    //         'email.unique' => 'Akun sudah terdaftar',
    //     ];

    //     $validatedData = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:8',
    //     ], $messages);

    //     $user = User::create([
    //         'name' => $validatedData['name'],
    //         'email' => $validatedData['email'],
    //         'password' => bcrypt($validatedData['password']),
    //     ]);

    //     return response()->json([
    //         'message' => 'Akun telah berhasil dibuat'
    //     ], 201);
    // }

    public function register(Request $request)
    {
        $messages = [
            'email.unique' => 'Akun sudah terdaftar',
        ];

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ], $messages);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Akun telah berhasil dibuat'
        ], 201);
    }

    // public function login(Request $request)
    // {
    //     if (!Auth::attempt($request->only('email', 'password'))) {
    //         return response()->json([
    //             'message' => 'Email atau Password salah!'
    //         ], 401);
    //     }
    //     $user = User::where('email', $request['email'])->firstOrFail();
    //     $token = $user->createToken('auth_token')->plainTextToken;
    //     return response()->json([
    //         'access_token' => $token,
    //         'token_type' => 'Bearer',
    //         'message' => 'Login Berhasil',
    //         'user' => $user
    //     ]);
    // }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email atau Password salah!'
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Verifikasi email terlebih dahulu.'
            ], 403); 
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Login Berhasil',
            'user' => $user
        ]);
    }


    function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Successfully logged out!'
        ], 200);
    }

    function getUser(Request $request)
    {
        return response()->json([
            'user' => auth()->user()
        ], 200);
    }

    public function updateUser(Request $request)
    {
        $user = auth()->user();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'no_telp' => 'required|numeric|min:0',
        ]);

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->no_telp = $validatedData['no_telp'] ?? $user->no_telp;

        if ($request->filled('password')) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save();

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    public function updateUserAdmin(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'no_telp' => 'nullable|string|max:20',
        ]);

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->no_telp = $validatedData['no_telp'] ?? $user->no_telp;

        if ($request->filled('password')) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save();

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }
}
