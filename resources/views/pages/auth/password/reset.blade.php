<?php

use Devdojo\Auth\Traits\HasConfigs;
use Illuminate\Support\Facades\Password;
use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

name('auth.password.request');

new class extends Component
{
    use HasConfigs;

    #[Validate('required')]
    public $email = null;
    private $isEmail = false;
    public $emailSentMessage = false;

    public function rules()
    {
        if ($this->isEmail = str($this->email)->contains('@')) {
            return ['email' => 'email'];
        }
        if (str($this->email)->startsWith('01')) {
            $this->email = '+88' . $this->email;
        }
        
        return ['email' => 'regex:/^\+8801\d{9}$/'];
    }

    public function mount(){
        $this->loadConfigs();
    }

    public function sendResetPasswordLink()
    {
        $this->validate();

        if ($this->isEmail) {
            $response = Password::broker()->sendResetLink(['email' => $this->email]);
        } else {
            $response = Password::broker('phone')->sendResetLink(['phone' => $this->email]);
        }

        if ($response == Password::RESET_LINK_SENT) {
            if (! $this->isEmail) {
                return to_route('password.reset', [
                    'phone' => $this->email,
                    'token' => 'otp',
                ]);
            }

            $this->emailSentMessage = trans($response);

            return;
        }

        $this->addError('email', trans($response));
    }
};

?>

<x-auth::layouts.app>

    @volt('auth.password.reset')
        <x-auth::elements.container>

        <x-auth::elements.heading
            :text="($language->passwordResetRequest->headline ?? 'No Heading')"
            :description="($language->passwordResetRequest->subheadline ?? 'No Description')"
            :show_subheadline="($language->passwordResetRequest->show_subheadline ?? false)"
        />

        @if ($emailSentMessage)
            <div class="p-4 mb-2 rounded-md bg-green-50 dark:bg-green-600">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-400 dark:text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>

                    <div class="ml-3">
                        <p class="text-sm font-medium leading-5 text-green-800 dark:text-green-200">
                            {{ $emailSentMessage }}
                        </p>
                    </div>
                </div>
            </div>
        @else
            <form wire:submit="sendResetPasswordLink" class="space-y-5">
                <x-auth::elements.input :label="config('devdojo.auth.language.passwordResetRequest.email')" type="text" id="email" name="email" data-auth="email-input" wire:model="email" autofocus="true" autocomplete="email" />
                <x-auth::elements.button type="primary" data-auth="submit-button" rounded="md" submit="true">{{config('devdojo.auth.language.passwordResetRequest.button')}}</x-auth::elements.button>
            </form>
        @endif
        <div class="mt-3 space-x-0.5 text-sm leading-5 text-center" style="color:{{ config('devdojo.auth.appearance.color.text') }}">
            <span class="opacity-[47%]">{{config('devdojo.auth.language.passwordResetRequest.or')}}</span>
            <x-auth::elements.text-link data-auth="login-link" href="{{ route('auth.login') }}">{{config('devdojo.auth.language.passwordResetRequest.return_to_login')}}</x-auth::elements.text-link>
        </div>
    </x-auth::elements.container>
    @endvolt

</x-auth::layouts.app>
