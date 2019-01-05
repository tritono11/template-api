<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use AppHttpRequests;
use AppHttpControllersController;
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use App\User;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\UserVerification;
use Mail; 
use Carbon\Carbon;

class AuthenticateController extends Controller
{
    public function __construct()
    {
        // Apply the jwt.auth middleware to all methods in this controller
        // except for the authenticate method. We don't want to prevent
        // the user from retrieving their token if they don't already have it
        $this->middleware('jwt.auth', ['except' => ['authenticate','signup','verifyUser']]);
    }
    
    public function index()
    {
        // Retrieve all the users in the database and return them
        $users = User::all();
        return $users;
    }    

    // http://test.app-default.local/server/public/api/authenticate
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            // verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // if no errors are encountered we can return a JWT
        //return response()->json(compact('token'));
        $temp = ["status" => "1",
                "user_id" => "",
                "username" => auth()->user()->name,
                "level" => "",
                "role" => "",
                "email" => auth()->user()->email,
                "token" => $token];
        return response()->json($temp);
    }
    
    public function logout(Request $request)
    {
        auth()->logout();
        return response()->json(["status" => 1]);
    }
    
    // http://test.app-default.local/server/public/api/signup
    public function signup(Request $request)
    {        
        $validateData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        
        $user = User::create([
                'name'      => $request['name'],
                'email'     => $request['email'],
                'password'  => Hash::make($request['password']),
            ]);
        
        if (config('iblea.b_attivazione')){
            
            $verification_code = str_random(config('iblea.i_lunghezza_token_verifica')); //Generate verification code
            $userVerification = UserVerification::create([
                'i_user_id' => $user->id,
                't_ip'      => $request->ip(),
                'remember_token'    => $verification_code
            ]);
                        
            $subject = "Please verify your email address.";
            try{
                Mail::send('email.verifyUser', ['name' => $user->name, 'email' => $user->email, 'verification_code' => $userVerification->remember_token],
                    function($mail) use ($email, $name, $subject){
                        $mail->from(getenv('FROM_EMAIL_ADDRESS'), "From User/Company Name Goes Here");
                        $mail->to($email, $name);
                        $mail->subject($subject);
                    });
            } catch(\Exception $e){
                return response()->json(['success' => true, "message" => "No activation email sended"]);
            }
        }
        
        return response()->json(['success' => true, "message" => "OK"]);
    }
    
    // WEB
    // http://test.app-default.local/server/public/api/authenticate
    public function verifyUser(Request $request)
    {
        $token = $request->token;
        $data =['success' => false,
                'b_attivazione' => config('iblea.b_attivazione')];
        if(config('iblea.b_attivazione')){
            if( $token && $token != "" && strlen($token) == config('iblea.i_lunghezza_token_verifica') ){
                try{
                    $userVerification = UserVerification::where('remember_token', $token)->first();
                    if($userVerification){
                        $user = User::find($userVerification->i_user_id);
                        if (!$user->email_verified_at){
                            $user->email_verified_at = Carbon::now()->toDateTimeString();
                            $user->save();
                            $data['success'] = true;
                        }
                    }
                }catch(\Exception $e){
                    $data["message"] = "SqlEx";
                }
            } else {
                $data["message"] = "Token non valido";
            }
        } else {
            $data["message"] = "Il sistema non necessita la verifica utente";
        }
        return view('verifica-email-utente', $data);
    }
    
}
