<?php

use App\Notifications\SendOtpCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Devdojo\Auth\Traits\HasConfigs;
use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;

middleware(['auth', 'phone.not.verified', 'throttle:6,1']);
name('verification.phone');

new class extends Component {
    use HasConfigs;

    public $otp = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->loadConfigs();

        // Send OTP if not sent recently
        if (!session()->has('otp_sent_at') || session('otp_sent_at')->diffInMinutes(now()) > 2) {
            $this->sendOTP();
        }
    }

    public function sendOTP()
    {
        $user = auth()->user();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in session with hash
        session([
            'otp_hash' => hash('sha256', $otp),
            'otp_sent_at' => now(),
        ]);

        $user->notify(new SendOtpCode($otp));

        $this->dispatch('otp-sent');
        session()->flash('resent');
    }

    public function verifyOTP()
    {
        $this->validate([
            'otp' => 'required|digits:6',
        ]);

        if (!session()->has('otp_hash')) {
            $this->errorMessage = 'OTP has expired. Please request a new one.';
            return;
        }

        if (hash('sha256', $this->otp) !== session('otp_hash')) {
            $this->errorMessage = 'Invalid OTP. Please try again.';
            return;
        }

        $user = auth()->user();
        $user->phone_verified_at = now();
        $user->save();

        session()->forget(['otp_hash', 'otp_sent_at']);

        return redirect()->intended(route('auth.login'));
    }

    public function resend()
    {
        $this->errorMessage = ''; // Clear any previous error message
        if (session()->has('otp_sent_at') && session('otp_sent_at')->diffInMinutes(now()) < 2) {
            $this->errorMessage = 'Please wait 2 minutes before requesting a new OTP.';
            return;
        }

        $this->sendOTP();
        session()->flash('resent');
    }
};

?>

<x-auth::layouts.app title="{{ config('devdojo.auth.language.verify.page_title') }}">

    @volt('auth.phone')
        <x-auth::elements.container>

            <x-auth::elements.heading text="Verify Your Phone Number" description="Please enter the OTP sent to your phone" />

            @if (session('resent'))
                <div class="flex items-start px-4 py-3 mb-5 text-sm text-white bg-green-500 rounded shadow" role="alert">
                    <svg class="w-5 h-5 mr-2 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <p>A new OTP has been sent to your phone number.</p>
                </div>
            @endif

            @if ($errorMessage)
                <div class="mb-4 text-sm text-red-600">
                    {{ $errorMessage }}
                </div>
            @endif

            <form wire:submit="verifyOTP">
                <div class="mb-2">
                    <label for="otp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Enter
                        OTP</label>
                    <input wire:model="otp" type="text" maxlength="6" id="otp"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600"
                        placeholder="Enter 6-digit OTP">
                    @error('otp')
                        <span class="text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <x-auth::elements.button type="primary" data-auth="submit-button" rounded="md" size="md"
                    submit="true">
                    Verify OTP
                </x-auth::elements.button>
            </form>

            <div class="mt-4 text-sm text-center">
                <button wire:click="resend" class="text-indigo-600 hover:text-indigo-500">
                    Resend OTP
                </button>
            </div>



            <div class="mt-2 space-x-0.5 text-sm leading-5 text-center text-gray-600 translate-y-4 dark:text-gray-400">
                <span>{{ config('devdojo.auth.language.verify.or') }}</span>
                <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="text-gray-500 underline cursor-pointer dark:text-gray-400 dark:hover:text-gray-300 hover:text-gray-800">
                    {{ config('devdojo.auth.language.verify.logout') }}
                </button>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>

        </x-auth::elements.container>
    @endvolt

</x-auth::layouts.app>
