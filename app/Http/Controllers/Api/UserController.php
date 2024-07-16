<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    // public function register(Request $request)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|max:100',
    //         'email' => 'required|email|unique:users',
    //         'nik' => 'required',
    //         'password' => 'required|confirmed'
    //     ]);

    //     $data['password'] = Hash::make($data['password']);
    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'nik' => $request->nik,
    //         'password' => $request->password,
    //         'role' => 'customer',
    //     ]);

    //     $success['token'] =  $user->createToken('auth_token')->plainTextToken;
    //     // $success['type'] =  'Bearer';
    //     $success['name'] =  $user->name;
    //     $success['email'] =  $user->email;
    //     $success['nik'] =  $user->nik;
    //     $success['role'] =  $user->role;
    //     return response()->json(['success' => $success]);
    // }

    public function register(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'nik' => 'required',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nik' => $request->nik,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            
        ]);

        // Generate a token
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'stat_code' => 200,
            'message' => 'Berhasil Register',
            'user' => $user,
            'access_token' => $token,
        ], 201);
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('API Token')->plainTextToken;
            return response()->json([
                'stat_code' => 200,
                'message' => 'Berhasil Login',
                'user' => $user,
                'access_token' => $token,
            ], 201);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out'], 200);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
