<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $token = Auth::guard('api')->attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nome de usuário ou senha incorreto.',
            ], 401);
        }

        $user = Auth::guard('api')->user();

        if ($user->payment_id === 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pagamento pendente. Por favor, renove o plano.',
            ], 403);
        }
        $expiration = JWTAuth::factory()->getTTL() * 60; 
        $expirationDate = now()->addSeconds($expiration);

        $response = response()->json([
            'status' => 'success',
            'user' => $user,
            'accessToken' => $token,
            'type' => 'bearer',
            'expiresIn' => $expiration,
            'expiresAt' => $expirationDate,
        ]);

        return $response;
    }

    public function index()
    {
        $user = auth()->user();
        if ($user->role_id === 1) {
            $users = User::all();
            return UserResource::collection($users);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Nível de acesso para acessar o recurso insuficiente.',
            ], 403);
        }
    }


    public function store(StoreUpdateUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($request->password);

        $user = User::create($data);
        $token = Auth::guard('api')->login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        return new UserResource($user);
    }


    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $data = $request->all();

        if ($request->has('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);
        return new UserResource($user);
    }


    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json([], 204);
    }

    public function renewal()
    {
        return response()->json(['message' => 'bora se alimpar']);
    }
}
