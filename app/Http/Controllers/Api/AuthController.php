<?php

namespace App\Http\Controllers\Api;

use App\Handlers\Auth\AuthHandler;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\APIController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
class AuthController extends APIController
{
    // register
    public function register(Request $request)
    {
        $input = $request->only('name', 'email', 'password', 'role');

        $validator = Validator::make($input, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],
            'role' => 'required|in:user,admin'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        if ($user) {
            $authHandler = new AuthHandler;
            $token = $authHandler->generateToken($user);

            $success = [
                'user' => $user,
                'token' => $token,
            ];
    
            return $this->sendResponse($success, 'user registered successfully', 201);
        }
        
    }

    public function login(Request $request)
    {
        $input = $request->only('email', 'password');

        $validator = Validator::make($input, [
            'email' => 'required|email|string|max:255',
            'password' => ['required',
                            'string',
                            'min:8'
                        ],
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $remember = $request->remember;

        if(Auth::attempt($input, $remember)){
            $user = Auth::user();

            $authHandler = new AuthHandler;
            $token = $authHandler->generateToken($user);

            $success = [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role
                        ],
                        'token' => $token
                    ];

            return $this->sendResponse($success, 'Logged In');
        }
        else{
            return $this->sendError('Unauthorized', ['error' => "Invalid Login credentials"], 401);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        if ($token) {

            $authHandler = new AuthHandler();
            $authHandler->blacklistToken($token);
        }

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
