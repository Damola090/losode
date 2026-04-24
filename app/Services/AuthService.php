<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        $vendor = Vendor::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'], // cast hashes automatically
        ]);

        $token = $vendor->createToken('api-token')->plainTextToken;

        return compact('vendor', 'token');
    }

    public function login(array $credentials): array
    {
        $vendor = Vendor::where('email', $credentials['email'])->first();

        if (! $vendor || ! Hash::check($credentials['password'], $vendor->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke old tokens to enforce single-session by default
        $vendor->tokens()->delete();

        $token = $vendor->createToken('api-token')->plainTextToken;

        return compact('vendor', 'token');
    }

    public function logout(Vendor $vendor): void
    {
        $vendor->currentAccessToken()->delete();
    }
}
