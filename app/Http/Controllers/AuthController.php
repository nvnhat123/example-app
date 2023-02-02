<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $email = $request->get('email');
            $password = $request->get('password');
            $checkAuth = $this->authService->checkAccount($email, $password);

            if (!$checkAuth) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Unauthorized'
                ]);
            }

            /** @var UserRepository $userRepository */
            $userRepository = app(UserRepository::class);
            $user = $userRepository->findByEmail($email);

            // if (!Hash::check($request->password, $user->password, [])) {
            //     throw new \Exception('Error in Login');
            // }

            $tokenResult = $this->authService->createToken($user);

            return response()->json([
                'status' => 200,
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
            ]);
        } catch (Exception $error) {
            return responder()->getErr();
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->removeCurrentAccessToken($request->user());

        return responder()->getSuccess();
    }
}