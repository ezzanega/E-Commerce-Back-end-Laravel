<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function RegisterWithGoogle(Request $request)
    {
        $token = $request->input('token');
        $client = new \GuzzleHttp\Client();
        $response = $client->get('https://oauth2.googleapis.com/tokeninfo?id_token=' . $token);

        if ($response->getStatusCode() == 200) {
            $googleUser = json_decode($response->getBody()->getContents());
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                return response()->json(['error' => 'This user already exists'], 409);
            }

            $user = User::create([
                'first_name' => $googleUser->given_name,
                'last_name' => $googleUser->family_name,
                'email' => $googleUser->email,
            ]);

            Auth::login($user);
            $token = JWTAuth::fromUser($user);

            return $this->respondWithToken($token);
        } else {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }


    public function LoginWithGoogle(Request $request)
    {
        $token = $request->input('token');
        $client = new \GuzzleHttp\Client();
        $response = $client->get('https://oauth2.googleapis.com/tokeninfo?id_token=' . $token);

        if ($response->getStatusCode() == 200) {
            $googleUser = json_decode($response->getBody()->getContents());
            $user = User::where('email', $googleUser->email)->first();

            if(!$user)
            {
                $user = User::create([
                    'first_name' => $googleUser->given_name,
                    'last_name' => $googleUser->family_name,
                    'email' => $googleUser->email,
                ]);
            }

            Auth::login($user);
            $token = JWTAuth::fromUser($user);

            return $this->respondWithToken($token);
        } else {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = auth()->login($user);

        return response()->json([
            'user' => $user,
            //'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only(['email', 'password']);

        $user=User::where('email',$credentials['email'])->first();
        if(!$user)
        {
            return response()->json(['error' => 'User does not exist'], 404);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Incorrect password'], 401);
        }

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);

    }


    protected function respondWithToken($token)
    {
        $customClaims = ['exp' => now()->addSeconds(15)->timestamp];

        $payload = JWTAuth::setToken($token)->getPayload();
        $token = JWTAuth::claims($customClaims)->fromUser(auth()->user());

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user_id' => auth()->user()->id
        ]);
    }

    public function getUserById(Request $request)
    {
        $id = $request->query('id');
        $user = User::find($id);
        return response()->json($user);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
}
