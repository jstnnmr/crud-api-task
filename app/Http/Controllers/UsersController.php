<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Validator;


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
    public function index(Request $request)
    {
        $users = $this->userService->getAllUsers(); 
            if(request()->is('api/*') || request()->wantsJson()){
                return response()->json([
                    'success' => true,
                    'data' => $users
                ]);
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
        if(request()->is('api/*') || request()->wantsJson()){
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        }
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }}

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
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = $this->userService->createUser($validator->validated());

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
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
        $validator = Validator::make($request->all(),[
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' .$id,
        ]);

        if ($validator->fails()){

            if ($request->is('api/*') || $request->expectsJson()){
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        $user = $this->userService->updateUser($id, $validator->validated());

        if ($request->is('api/*') || $request->expectsJson()){
            return response()->json([
                'success' => true,
                'message' => 'User updated succesfully',
                'data' => $user
            ]);
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
    public function destroy(Request $request, $id)
    {
        $user = $this->userService->deleteUser($id);
        if (!$user) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            return redirect()->route('users.index')->with('error', 'User not found');
        }
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        }
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
}