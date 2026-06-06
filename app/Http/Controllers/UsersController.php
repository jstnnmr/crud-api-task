<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UsersController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *   path="/api/users",
     *   summary="Get all users",
     *   operationId="getAllUsers",
     *   tags={"Users"},
     *   @OA\Response(response=200, description="Success")
     * )
     */
    public function index(Request $request)
    {
        $users = User::with('tasks')
            ->where('created_by', auth()->id())
            ->get();

        $subjects = auth()->user()->subjects()->get();

        if ($request->is('api/*') || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        }
        return view('users.data', compact('users', 'subjects'));
    }

    /**
     * @OA\Get(
     *   path="/api/users/{id}",
     *   summary="Get user by ID",
     *   operationId="getUserById",
     *   tags={"Users"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=404, description="User not found")
     * )
     */
    public function show(Request $request, $id)
    {
        $user = User::with('tasks')
            ->where('id', $id)
            ->where('created_by', auth()->id())
            ->first();

        if (!$user) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            return redirect()->route('users.data')->with('error', 'User not found');
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        }

        return redirect()->route('users.data');
    }

    /**
     * @OA\Post(
     *   path="/api/users",
     *   summary="Create a user",
     *   operationId="createUser",
     *   tags={"Users"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name","email"},
     *       @OA\Property(property="name", type="string", example="John Doe"),
     *       @OA\Property(property="email", type="string", example="john@example.com")
     *     )
     *   ),
     *   @OA\Response(response=201, description="User created successfully"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
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
                    'errors'  => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['created_by'] = auth()->id();

        $user = $this->userService->createUser($data);

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data'    => $user
            ], 201);
        }

        return redirect()->route('users.data')->with('success', 'User created successfully');
    }

    /**
     * @OA\Put(
     *   path="/api/users/{id}",
     *   summary="Update a user",
     *   operationId="updateUser",
     *   tags={"Users"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="name", type="string", example="John Doe"),
     *       @OA\Property(property="email", type="string", example="john-updated@example.com")
     *     )
     *   ),
     *   @OA\Response(response=200, description="User updated successfully"),
     *   @OA\Response(response=404, description="User not found"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        $user = User::where('id', $id)
            ->where('created_by', auth()->id())
            ->first();

        if (!$user) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or unauthorized'
                ], 404);
            }
            return redirect()->route('users.data')->with('error', 'User not found or unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
        ]);

        if ($validator->fails()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = $this->userService->updateUser($id, $validator->validated());

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data'    => $user
            ]);
        }

        return redirect()->route('users.data')->with('success', 'User updated successfully');
    }

    /**
     * @OA\Delete(
     *   path="/api/users/{id}",
     *   summary="Delete a user",
     *   operationId="deleteUser",
     *   tags={"Users"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="User deleted successfully"),
     *   @OA\Response(response=404, description="User not found")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $user = User::where('id', $id)
            ->where('created_by', auth()->id())
            ->first();

        if (!$user) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or unauthorized'
                ], 404);
            }
            return redirect()->route('users.data')->with('error', 'User not found or unauthorized');
        }

        $this->userService->deleteUser($id);

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        }

        return redirect()->route('users.data')->with('success', 'User deleted successfully');
    }

    public function dataView()
    {
        $users = User::with('tasks')
            ->where('created_by', auth()->id())
            ->get();
        return view('users.data', compact('users'));
    }
}