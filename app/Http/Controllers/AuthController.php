<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Utils\ApiResponseUtil;
use App\Utils\PasswordValidatorUtil;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    new PasswordValidatorUtil()
                ],
            ]);

            $user = User::create($validatedData);

            $token = $user->createToken('auth_token')->plainTextToken;

            return ApiResponseUtil::success(
                'User Registered Successfully',
                [
                    'user' => $user,
                    'token' => $token
                ],
                201
            );

        } catch (ValidationException $e) {
            return ApiResponseUtil::error(
                'Validation Error',
                $e->errors(),
                422
            );
        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Server Error',
                ['error' => $e->getMessage()],
                500
            );
        }
    }    
}
