<?php

namespace App\Http\Controllers;

use Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Authentication
 *
 * APIs for login, registration, logout.
 */

class AuthController extends Controller
{

	/**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', [
        	'except' => [ 'login', 'register']
        ]);
    }

    /**
     * Login user.
     * 
     * @return \Illuminate\Http\JsonResponse
     */

    public function login(Request $request) {
    	$validator = Validator::make($request->all(), [
            'email' => [ 'required', 'email'],
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
	    		'errors' => $validator->errors(),
	    		'message' => 'Invaid params passed'
            ], 422);
        }

        $credentials = [
            'password' => $request->password,
            'email' => $request->email   
        ];

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json([
            	'error' => 'Invalid email or password'
            ], 400);
        }
         
        return response()->json([
            "message" => "User logged in successfully",
            "access_token" => $token ,
            "token_type" => "bearer",
            "expires_at" =>  $this->tokenExpiryAsTimestamp()
        ]);
    }
 
    /**
     * Register a User . 
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:3,100',
            'last_name' => 'required|string|between:3,100',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
            	'message' => 'Invalid params passed',
            	'errors' => $validator->errors()
            ], 422);
        }

        try {
        	DB::beginTransaction();

	        $user  = new User; 
			$user->first_name = $request->first_name;
			$user->last_name = $request->last_name;
			$user->password = bcrypt($request->password);
			$user->email = $request->email;
		  	$user->save();
 
        	DB::commit();

	        return response()->json([
	            'message' => 'User successfully registered',
	            'user' => [
	            	'id' => $user->id
	            ]
	        ], 201);
	    } catch (\Throwable $e) {
        	DB::rollback();
throw $e;
	        report($e);

	        return response()->json([
                'message' => 'An Unexpected error occured.. Please try again',
                
	        ], 400); 
	    }    
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /** 
     * Returns tokens expiry as timestamp
     *
     * @return int
     */
    private function tokenExpiryAsTimestamp() {
    	$seconds = auth('api')->factory()->getTTL() * 60 ;
    	
    	return strtotime('now') + $seconds ;
    }
}