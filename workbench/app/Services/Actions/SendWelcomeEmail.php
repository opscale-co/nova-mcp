<?php

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

    public function handle(array $attributes = []): array
    {
        $this->fill($attributes);
        $validatedData = $this->validateAttributes();

        $user = User::where('email', $validatedData['email'])->firstOrFail();

        $user->notify(new WelcomeNotification);

        return [
            'success' => true,
            'message' => 'Welcome email sent successfully',
        ];
    }
}
