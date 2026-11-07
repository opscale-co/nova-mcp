<?php

declare(strict_types=1);

namespace Workbench\App\Services\Actions;

use Opscale\Actions\Action;
use Workbench\App\Models\User;
use Workbench\App\Notifications\WelcomeNotification;

class SendWelcomeEmail extends Action
{
    public function identifier(): string
    {
        return 'send-welcome-email';
    }

    public function name(): string
    {
        return 'Send Welcome Email';
    }

    public function description(): string
    {
        return 'Sends a welcome email to a user';
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
        ];
    }

    public function outputs(): array
    {
        return [
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

        $user->notify(new WelcomeNotification);

        return $this->succeed([
            'message' => 'Welcome email sent successfully.',
        ]);
    }
}
