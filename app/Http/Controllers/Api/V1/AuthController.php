<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'timezone' => $data['timezone'] ?? 'Asia/Jakarta',
                'experience_level' => $data['experience_level'] ?? null,
                'daily_available_minutes' => $data['daily_available_minutes'] ?? null,
                'primary_goal' => $data['primary_goal'] ?? null,
                'status' => 'active',
                'last_login_at' => now(),
            ]);

            $user->preferences()->create([
                'language' => 'id',
                'theme' => 'system',
            ]);

            $freePlan = Plan::query()
                ->where('is_free', true)
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->first();

            if ($freePlan) {
                $user->subscriptions()->create([
                    'plan_id' => $freePlan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                ]);
            }

            $user->productEvents()->create([
                'event_name' => 'registration_completed',
                'properties' => ['plan' => $freePlan?->code],
                'occurred_at' => now(),
            ]);

            return $user;
        });

        return $this->ok([
            'token' => $user->createToken('api')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $this->presentUser($user),
        ], 'Registrasi berhasil.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return $this->fail('Email atau kata sandi tidak cocok.', 401);
        }

        if ($user->status !== 'active') {
            return $this->fail('Akun tidak aktif.', 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $this->ok([
            'token' => $user->createToken('api')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $this->presentUser($user),
        ], 'Berhasil masuk.');
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return $this->ok(null, 'Berhasil keluar.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->ok($this->presentUser($request->user()));
    }

    public function updateMe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'timezone' => ['sometimes', 'timezone'],
            'experience_level' => ['nullable', 'string', 'max:50'],
            'daily_available_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'primary_goal' => ['nullable', 'string', 'max:50'],
            'preferences' => ['sometimes', 'array'],
            'preferences.language' => ['sometimes', 'string', 'max:10'],
            'preferences.theme' => ['sometimes', 'in:system,light,dark'],
            'preferences.reminder_time' => ['nullable', 'date_format:H:i'],
            'preferences.email_notifications' => ['sometimes', 'boolean'],
            'preferences.push_notifications' => ['sometimes', 'boolean'],
            'preferences.offline_photo_quality' => ['sometimes', 'in:low,medium,high'],
        ]);

        $user = $request->user();
        $preferences = $data['preferences'] ?? null;
        unset($data['preferences']);

        if ($data !== []) {
            $user->fill($data)->save();
        }

        if (is_array($preferences)) {
            $user->preferences()->updateOrCreate(
                ['user_id' => $user->id],
                $preferences
            );
        }

        return $this->ok($this->presentUser($user->refresh()), 'Profil diperbarui.');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        return $this->ok(null, 'Jika email terdaftar, tautan reset akan dikirim.');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        return $this->ok([
            'reset' => false,
        ], 'Permintaan diterima. Pengiriman email reset belum diaktifkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function presentUser(User $user): array
    {
        $user->load(['preferences', 'subscriptions.plan']);
        $subscription = $user->subscriptions->firstWhere('status', 'active');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar_path' => $user->avatar_path,
            'timezone' => $user->timezone,
            'experience_level' => $user->experience_level,
            'daily_available_minutes' => $user->daily_available_minutes,
            'primary_goal' => $user->primary_goal,
            'status' => $user->status,
            'last_login_at' => $user->last_login_at,
            'preferences' => $user->preferences,
            'subscription' => $subscription,
        ];
    }
}
