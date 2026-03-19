<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    public function index(Request $request)
{
    $users = User::all();

    if ($request->expectsJson()) {
        return response()->json($users, 200);
    }

    return view('users.data', compact('users'));
}

    //======== READ FUNCTION ===========
    public function show(Request $request, $id)
    {
        $user = User::find($id); //select * from users where id = $id

        if (!$user) {           //if user not found
            return response()->json(['message' => 'User not found'], 404);
        }
                //return response as JSON
        if ($request->expectsJson()) {
        return response()->json($user);
    }

    return view('users.data', compact('user'));
}

    //======== CREATE FUNCTION ===========
    public function store(Request $request)
    {
        $user = User::create([
        'name'  => $request->name,
        'email' => $request->email,
    ]);
        return response()->json(
            [
                'message' => 'User created successfully',
                'data' => $user,
            ], 201);
    }

    // ======== UPDATE FUNCTION ===========
    public function update(Request $request, $id)
    {
        $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $user->update($request->all());

    return response()->json([
        'message' => 'User updated successfully',
        'data'    => $user,
    ], 200);
    }

    //========= DELETE FUNCTION ===========
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();

        return response()->json(
            [
                'message' => 'User deleted successfully',
            ], 200);
    }
}
