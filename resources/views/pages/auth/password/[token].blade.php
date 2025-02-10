<?php

use Devdojo\Auth\Traits\HasConfigs;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

name('password.reset');

new class extends Component
{
    use HasConfigs;

    #[Validate('required')]
    public $token;
    #[Validate('required')]
    public $email;
    public $isEmail = false;
    public $otp;
    #[Validate('required|min:8|same:passwordConfirmation')]
    public $password;
    public $passwordConfirmation;

    public function rules()
    {
        if ($this->isEmail) {
            return ['email' => 'email'];
        }

        return ['otp' => 'integer|digits:6'];
    }

    public function mount($token)
    {
        $this->loadConfigs();
        if (request()->has('email')) {
            $this->isEmail = true;
            $this->email = request()->query('email');
        } else {
            $this->email = request()->query('phone');
        }
        $this->token = $token;
    }

    public function resetPassword()
    {
        $this->validate();

        $response = Password::broker($this->isEmail ? null : 'phone')->reset(
            [
                'token' => $this->isEmail ? $this->token : $this->otp,
                $this->isEmail ? 'email' : 'phone' => $this->email,
                'password' => $this->password,
            ],
            function ($user, $password) {
                $user->password = Hash::make($password);

                $user->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

                Auth::guard()->login($user);
            },
        );


        if ($response == Password::PASSWORD_RESET) {
            session()->flash(trans($response));

            return redirect('/');
        }

        $this->addError($this->isEmail ? 'email' : 'otp', trans($response));
    }
};

?>

<x-auth::layouts.app>
    @volt('auth.password.token')
        <x-auth::elements.container>
            <x-auth::elements.heading
                :text="($language->passwordReset->headline ?? 'No Heading')"
                :description="($language->passwordReset->subheadline ?? 'No Description')"
                :show_subheadline="($language->passwordReset->show_subheadline ?? false)"
            />

            <form wire:submit="resetPassword" class="space-y-5">
                @if($isEmail)
                <x-auth::elements.input :label="config('devdojo.auth.language.passwordReset.email')" type="email" id="email" name="email" data-auth="email-input" wire:model="email" autofocus="true" />
                @else
                <x-auth::elements.input label="Enter OTP" type="text" id="otp" name="otp" data-auth="otp-input" wire:model="otp" autofocus="true" />
                @endif
                <x-auth::elements.input :label="config('devdojo.auth.language.passwordReset.password')" type="password" id="password" name="password" data-auth="password-input" wire:model="password" autocomplete="new-password" />
                <x-auth::elements.input :label="config('devdojo.auth.language.passwordReset.password_confirm')" type="password" id="password_confirmation" name="password_confirmation" data-auth="password-confirm-input" wire:model="passwordConfirmation" autocomplete="new-password" />
                <x-auth::elements.button type="primary" data-auth="submit-button" rounded="md" submit="true">{{config('devdojo.auth.language.passwordReset.button')}}</x-auth::elements.button>
            </form>
        </x-auth::elements.container>
    @endvolt
</x-auth::layouts.app>
