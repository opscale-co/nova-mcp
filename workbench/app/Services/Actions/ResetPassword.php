<?php

declare(strict_types=1);

namespace Workbench\App\Services\Actions;

use Illuminate\Support\Facades\Hash;
use Opscale\Actions\Action;
use Workbench\App\Models\User;

class ResetPassword extends Action
{
    public function identifier(): string
    {
        return 'reset-password';
    }

    public function name(): string
    {
        return 'Reset Password';
    }

    public function description(): string
    {
        return 'Resets a user\'s password';
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'email',
                'description' => 'The email address of the user',
                'type' => 'string',
                'rules' => ['required', 'email', 'exists:users,email'],
            ],
            [
                'name' => 'password',
                'description' => 'The new password',
                'type' => 'string',
                'rules' => ['required', 'string', 'min:8'],
            ],
            [
                'name' => 'password_confirmation',
                'description' => 'Confirm the new password',
                'type' => 'string',
                'rules' => ['required', 'string', 'same:password'],
            ],
        ];
    }

    public function outputs(): array
    {
        return [
            [
                'name' => 'user_id',
                'description' => 'ID of the user whose password was reset.',
                'type' => 'integer',
                'rules' => ['required', 'integer'],
            ],
            [
                'name' => 'message',
                'description' => 'Human-readable summary.',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
        ];
    }

    public function handle(array $inputs = []): array
    {
        $user = User::where('email', $inputs['email'])->first();

        if ($user === null) {
            return $this->fail('User not found.');
        }

        $user->update([
            'password' => Hash::make($inputs['password']),
        ]);

        return $this->succeed([
            'user_id' => $user->getKey(),
            'message' => 'Password reset successfully.',
        ]);
    }
}
