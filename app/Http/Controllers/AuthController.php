<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

use App\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Req uest
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function jwt(User $user)
    {

        $payload = [
            'iss' => "lumen-jwt",//Emissor do TOKEN
            'sub' => $user->id,//Sujeito do token
            'iat' => time(),//Hora que o JWT foi emitido
            'exp' => time() + 60 * 60, //Tempo de expiração
        ];

        //JWT_SECRET é usado para decodificar o token no futuro
        return JWT::encode($payload, env('JWT_SECRET'));

    }

    public function authenticate(User $user)
    {
        $this->validate($this->request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $this->request->input('email'))->first();

        if (!$user) {
            return response()->json([
                'error' => 'Email does not exist.'
            ], 400);
        }

        // Verify the password and generate the token
        if (Hash::check($this->request->input('password'), $user->password)) {
            return response()->json([
                'token' => $this->jwt($user)
            ], 200);
        }
        
        // Bad Request response
        return response()->json([
            'error' => 'Email or password is wrong.'
        ], 400);
    }


}
