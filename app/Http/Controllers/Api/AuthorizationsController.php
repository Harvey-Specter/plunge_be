<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use App\Http\Requests\Api\AuthorizationRequest;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use Illuminate\Support\Facades\Log;
class AuthorizationsController extends Controller
{
    public function store(AuthorizationRequest $request)
    {
        $email = $request->email;

        //filter_var($email, FILTER_VALIDATE_EMAIL) ?
            // $credentials['email'] = $username :
            //$credentials['phone'] = $username;

        $credentials['email'] = $email ;
        $credentials['password'] = $request->password;

        // Log::debug("email========",$email);

        if (!$token = \Auth::guard('api')->attempt($credentials)) {
            throw new AuthenticationException('用户名或密码错误');
        }

        return $this->respondWithToken($token)->setStatusCode(201);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'code'=> '0000',
            'data'=> [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60]
        ]);
    }
    public function update()
    {
        $token = auth('api')->refresh();
        return $this->respondWithToken($token);
    }

    public function destroy()
    {
        auth('api')->logout();
        Log::debug("logout========logout");
        return parent::success(204);
    }
}
