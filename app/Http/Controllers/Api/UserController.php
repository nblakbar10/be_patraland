<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Auth as FirebaseAuth;

class UserController extends Controller
{

    protected $firebaseAuth;

    public function __construct()
    {
        $this->firebaseAuth = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials'))
            ->createAuth();
    }

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
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'nik' => $request->input('nik'),
            'fcm_token' => $request->input('fcm_token'),
            'password' => Hash::make($request->input('password')),
            'role' => 'customer',
            
        ]);

        // Generate a token
        $token = $user->createToken('API Token')->plainTextToken;

        // Generate an FCM token for the user
        try {
            $fireBaseUser = $this->firebaseAuth->createUser([
                'email' => $user->email,
                'emailVerified' => false,
                'password' => $request->input('password'),
                'displayName' => $user->name,
            ]);
            // Assuming the token is part of user creation response, otherwise, generate it separately
            $fcmToken = $this->firebaseAuth->createCustomToken($fireBaseUser->uid)->toString();
            $user->fcm_token = $fcmToken;
            $user->save();
        } catch (FirebaseException $e) {
            return response()->json(['error' => 'FCM token generation failed', 'details' => $e->getMessage()], 500);
        }

        // // Send a push notification to the new user
        // try {
        //     $this->sendPushNotification($fcmToken);
        // } catch (FirebaseException $e) {
        //     return response()->json(['error' => 'Push notification failed', 'details' => $e->getMessage()], 500);
        // }

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

            // Set the FCM token from the request
            $user->fcm_token = $request->input('fcm_token');
            $user->save();

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

    // public function updateFcmToken(Request $request){
    //     try{
    //         $request->user()->update(['fcm_token'=>$request->token]);
    //         return response()->json([
    //             'success'=>true
    //         ]);
    //     }catch(\Exception $e){
    //         report($e);
    //         return response()->json([
    //             'success'=>false
    //         ],500);
    //     }
    // }
}