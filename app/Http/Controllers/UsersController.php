<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use OpenApi\Attributes as OA;

class UsersController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[OA\Get(path: '/api/users', summary: 'Get all users', tags: ['Users'],
        responses: [new OA\Response(response: 200, description: 'Success')]
    )]
    public function index()
    {
        $users = $this->userService->getAllUsers();
        if (request()->is('api/*') || request()->wantsJson()) {
            return response()->json($users);
        }
        return view('users.data', compact('users'));
    }

    #[OA\Get(path: '/api/users/{id}', summary: 'Get user by ID', tags: ['Users'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 404, description: 'User not found')
        ]
    )]
    public function show($id)
    {
        $user = $this->userService->getUserById($id);
        if (request()->is('api/*') || request()->wantsJson()) {
            return response()->json($user);
        }
        return view('users.data', compact('user'));
    }

    #[OA\Post(path: '/api/users', summary: 'Create a user', tags: ['Users'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'User created successfully'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);
        $user = $this->userService->createUser($validated);
        if (request()->is('api/*') || request()->wantsJson()) {
            return response()->json(['message' => 'User created successfully', 'data' => $user], 201);
        }
        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    #[OA\Put(path: '/api/users/{id}', summary: 'Update a user', tags: ['Users'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'User updated successfully'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
        ]);
        $user = $this->userService->updateUser($id, $validated);
        if (request()->is('api/*') || request()->wantsJson()) {
            return response()->json(['message' => 'User updated successfully', 'data' => $user], 200);
        }
        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    #[OA\Delete(path: '/api/users/{id}', summary: 'Delete a user', tags: ['Users'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'User deleted successfully'),
            new OA\Response(response: 404, description: 'User not found')
        ]
    )]
    public function destroy($id)
    {
        $this->userService->deleteUser($id);
        if (request()->is('api/*') || request()->wantsJson()) {
            return response()->json(['message' => 'User deleted successfully'], 200);
        }
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
}