<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserAccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthReq
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->header('x-authorization');
        if ($authorization !== null) {
            $access_token = trim(str_replace('Bearer', '', $authorization));
            $user_token = UserAccessToken::where('access_token', $access_token)->first();
            if ($user_token !== null) {
                $user = User::find($user_token->user_id);
                if($user !== null) {
                    $request->merge(['user' => $user]);
                    return $next($request);
                } else {
                    return response()->json(['message' => 'Unauthorized Request'], 401);
                }
            } else {
                return response()->json(['message' => 'Unauthorized Request'], 401);
            }
        } else {
            return response()->json(['message' => 'Unauthorized Request'], 401);
        }
    }
}
