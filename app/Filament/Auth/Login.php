<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;

/**
 * Login screen with "Remember me" moved below the sign-in button.
 *
 * The checkbox stays inside the form schema so its state still binds; only the
 * submit action is relocated, by placing it in the schema and emptying the
 * footer that would otherwise render it underneath everything.
 */
class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),

            Actions::make($this->getAuthenticateFormActions())
                ->fullWidth()
                ->key('form-actions'),

            $this->getRememberFormComponent(),
        ]);
    }

    /** @return array<\Filament\Actions\Action> */
    protected function getAuthenticateFormActions(): array
    {
        return [$this->getAuthenticateFormAction()];
    }

    /** Emptied because the action is rendered inside the form schema instead. */
    protected function getFormActions(): array
    {
        return [];
    }
}
