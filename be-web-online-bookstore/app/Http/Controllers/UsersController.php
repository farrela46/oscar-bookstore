<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function getAllUsers()
    {
        $users = User::where('role', 'USER')->get();
        return response()->json($users);
    }
    public function deleteUser($id)
    {
        $users = User::where('id', $id);
        $users->delete();
        return response()->json([
            'success' => true,
            'message' => 'User Deleted',
            'data' => $users
        ], 200);
    }
}
