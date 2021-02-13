<?php

namespace App\Http\Controllers;

use Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Authentication
 *
 * APIs for login, registration, logout.
 */

class AuthController extends Controller
{
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
	    		'message' => 'invaid params passed'
            ], 422);
        }

        $credentials = [
            'password' => $request->password,
            'email' => $request->email   
        ];

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json([
            	'error' => 'invalid email or password'
            ], 400);
        }
         
        return response()->json([
            "message" => "user logged in successfully",
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
            'firstName' => 'required|string|between:3,100',
            'lastName' => 'required|string|between:3,100',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
            	'message' => 'invalid params passed',
            	'errors' => $validator->errors()
            ], 422);
        }

        try {
	        $user  = new User; 
			$user->first_name = $request->firstName;
			$user->last_name = $request->lastName;
			$user->password = bcrypt($request->password);
			$user->email = $request->email;
		  	$user->save();

	        return response()->json([
	            'message' => 'user successfully registered.',
	            'user' => [
	            	'id' => $user->id
	            ]
	        ], 201);
	    } catch (\Throwable $e) {
            report($e);

	        return response()->json([
                'message' => 'Failed registering user.',
	        ], 400); 
	    }    
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