<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $this->authorize('manage', User::class);
        return response()->json(User::paginate(15));
    }

    public function show(User $user)
    {
        $this->authorize('manage', User::class);
        return response()->json($user);
    }

    public function store(UserStoreRequest $request)
    {
        $this->authorize('manage', User::class);

        $data = $request->only(['name','email','role','phone']);
        $data['password'] = Hash::make($request->password);

        $user = User::create($data);

        return response()->json($user, 201);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $this->authorize('manage', User::class);

        $data = $request->only(['name','email','role','phone']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $this->authorize('manage', User::class);
        $user->delete();
        return response()->json(null, 204);
    }

    public function promote(Request $request, User $user)
    {
        $this->authorize('manage', User::class);
        $role = $request->input('role');
        if (!in_array($role, ['admin','operator','parent','student'])) {
            return response()->json(['message' => 'Invalid role'], 422);
        }
        $user->role = $role;
        $user->save();
        return response()->json($user);
    }
}
