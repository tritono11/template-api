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
use App\model\UserPasswordReset;

class AuthenticateController extends Controller
{
    public function __construct()
    {
        // Apply the jwt.auth middleware to all methods in this controller
        // except for the authenticate method. We don't want to prevent
        // the user from retrieving their token if they don't already have it
        $this->middleware('jwt.auth', ['except' => ['authenticate','signup','verifyUser','resetPassword','beginPasswordReset','passwordReset','storePasswordReset','logout']]);
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
        //TODO aggiornare login event per log 
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
    
    /* WEB
     * 
     */
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
                            $data["message"] = "";
                        } else {
                            $data['success'] = false;
                            $data["message"] = "Utente già verificato";
                        }
                    } else {
                        
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
    
    /* 
     * Invia una mail con un token che permette di accedere al form di reset password
     */
    public function beginPasswordReset(Request $request)
    {
        $validateData = $request->validate([
                'email' => ['required', 'string', 'email', 'max:255']
        ]);
        $email = $request->only('email');
        $data =['success' => false];
        $user = User::where('email', $email)->first();
        if ($user){
            $verification_code = str_random(config('iblea.i_lunghezza_token_verifica'));
            $userPasswordReset = UserPasswordReset::create([
                'i_user_id' => $user->id,
                't_ip'      => $request->ip(),
                'remember_token'    => $verification_code
            ]);
            // invio email
            $subject = "E' stato richiesto un cambio password";
            try{
                Mail::send('email.beginResetPassword', ['name' => $user->name, 'email' => $user->email, 'verification_code' => $userPasswordReset->remember_token],
                    function($mail) use ($email, $name, $subject){
                        $mail->from(getenv('FROM_EMAIL_ADDRESS'), "From User/Company Name Goes Here");
                        $mail->to($email, $name);
                        $mail->subject($subject);
                    });
            } catch(\Exception $e){
                $data["message"] = "No reset email sended";
            }
            $data['success'] = true;
        } else{
            $data['message'] = 'Utente non presente';
        }
        return response()->json($data);
    }
    
    /* WEB
     * Visualizza il modulo di cambio password
     */
    public function passwordReset(Request $request)
    {
        $token = $request->token;
        $data = ['success' => false];
        if( $token && $token != "" && strlen($token) == config('iblea.i_lunghezza_token_verifica') ){ 
            $userPasswordReset = UserPasswordReset::where('remember_token', $token)->first();
            if ($userPasswordReset){
                $data['success'] = true;
                $data['token'] = $token;
            } else {
                // Nessun token di reset 
                $data['token'] = '';
            }
        }else {
            // token inviato non valido nel formato
            $data['token'] = '';
        }
        return view('user/password-reset', $data);
    }
    
    /* WEB
     * Finalizza il cambio password
     */
    public function storePasswordReset(Request $request)
    {
        $validateData = $request->validate([
                'password' => ['required', 'string', 'min:6', 'confirmed'],
                'token' => ['required']
        ]);
        
        $data = ['success' => false,
                 'message' => ''];
        try{
            $userPasswordReset = UserPasswordReset::where('remember_token', $request->token)
                                    ->whereNull('b_used')
                                    ->first();
            if($userPasswordReset){
                $user = User::find($userPasswordReset->i_user_id);
                if ($user){
                    $user->password = Hash::make($request->password);
                    $user->save();
                    $userPasswordReset->b_used = 'Y';
                    $userPasswordReset->save();
                    $data['success'] = true;
                }
            }else{
                $data['message'] = "Token invalido o scaduto";
            }
        } catch(\Exception $e){
            $data['success'] = false;
        }
        return view('user/give-thanks', $data);
        
    }
}
