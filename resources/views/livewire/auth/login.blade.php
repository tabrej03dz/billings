<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
{
    #[Validate('required|string')]
    public string $phone = '';

    public string $otp = '';

    public bool $remember = true;

    public bool $otpSent = false;

    /*
    |--------------------------------------------------------------------------
    | OTP SKIP LOGIN
    |--------------------------------------------------------------------------
    */

    public function sendOtp(): void
    {
        $this->validate([
            'phone' => ['required', 'digits:10'],
        ]);

        $this->ensureIsNotRateLimited();

        $user = \App\Models\User::where('phone', $this->phone)->first();

        if (! $user) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'phone' => 'This phone number is not registered.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | TEMPORARY OTP DISABLED
        |--------------------------------------------------------------------------
        |
        | Phone number valid hai to direct login kara rahe hain.
        | Baad me OTP enable karna ho to ye block hata dena aur old OTP code
        | wapas rakhna.
        |
        */

        Auth::login($user, $this->remember);

        Session::regenerate();

        RateLimiter::clear($this->throttleKey());

        $this->redirectIntended(
            default: route('dashboard', absolute: false),
            navigate: true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ORIGINAL OTP SEND FUNCTION
    |--------------------------------------------------------------------------
    */

    // public function sendOtp(): void
    // {
    //     $this->validate([
    //         'phone' => ['required', 'digits:10'],
    //     ]);

    //     $this->ensureIsNotRateLimited();

    //     $user = \App\Models\User::where('phone', $this->phone)->first();

    //     if (! $user) {
    //         RateLimiter::hit($this->throttleKey());

    //         throw ValidationException::withMessages([
    //             'phone' => 'This phone number is not registered.',
    //         ]);
    //     }

    //     $otp = rand(100000, 999999);

    //     session([
    //         'login_otp_user_id' => $user->id,
    //         'login_otp_remember' => $this->remember,
    //         'login_otp' => $otp,
    //         'login_otp_expires_at' => now()->addMinutes(5)->timestamp,
    //     ]);

    //     $msg = "Dear Customer, {$otp} this is your login verification OTP. Please do not share with anyone. Best Regards, Real Victory Groups https://myvictory.in/";

    //     Http::get(env('KUTILITY_URL'), [
    //         'key' => env('KUTILITY_KEY'),
    //         'campaign' => '12754',
    //         'routeid' => '7',
    //         'type' => 'text',
    //         'contacts' => $this->phone,
    //         'senderid' => 'RVGRPS',
    //         'msg' => $msg,
    //         'template_id' => '1707178057481157648',
    //         'pe_id' => '1701164032595209992',
    //     ]);

    //     RateLimiter::clear($this->throttleKey());

    //     $this->otpSent = true;

    //     session()->flash('status', 'OTP sent successfully.');
    // }

    public function verifyOtp(): void
    {
        $this->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        if (
            ! session('login_otp') ||
            ! session('login_otp_user_id') ||
            now()->timestamp > session('login_otp_expires_at')
        ) {
            throw ValidationException::withMessages([
                'otp' => 'OTP expired. Please request a new OTP.',
            ]);
        }

        if ($this->otp != session('login_otp')) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid OTP.',
            ]);
        }

        $user = \App\Models\User::find(
            session('login_otp_user_id')
        );

        if (! $user) {
            throw ValidationException::withMessages([
                'otp' => 'User not found.',
            ]);
        }

        Auth::login(
            $user,
            session('login_otp_remember', true)
        );

        Session::regenerate();

        session()->forget([
            'login_otp_user_id',
            'login_otp_remember',
            'login_otp',
            'login_otp_expires_at',
        ]);

        $this->redirectIntended(
            default: route('dashboard', absolute: false),
            navigate: true
        );
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts(
            $this->throttleKey(),
            5
        )) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn(
            $this->throttleKey()
        );

        throw ValidationException::withMessages([
            'phone' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->phone)
            .'|'.
            request()->ip()
        );
    }
};

?>

<div class="flex flex-col gap-6">

    {{-- Logo and heading --}}
    <div class="flex flex-col items-center text-center">

        <a
            href="{{ url('/') }}"
            class="mb-5 inline-flex items-center justify-center"
        >
            <img
                src="{{ asset('asset/img/MY VICTORY LOGO 2.png') }}"
                alt="MyVictory Billing Logo"
                class="h-16 w-auto max-w-[240px] object-contain sm:h-20"
            >
        </a>

        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
            Login with OTP
        </h1>

        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
            Enter your phone number to receive OTP
        </p>
    </div>

    <x-auth-session-status
        class="text-center"
        :status="session('status')"
    />

    @if (! $otpSent)

        <form
            wire:submit="sendOtp"
            class="flex flex-col gap-6"
        >
            <flux:input
                wire:model="phone"
                label="Phone Number"
                type="text"
                inputmode="numeric"
                required
                autofocus
                maxlength="10"
                placeholder="Enter 10 digit mobile number"
            />

            <flux:checkbox
                wire:model="remember"
                label="Remember me"
            />

            <div class="flex items-center justify-end">
                <flux:button
                    variant="primary"
                    type="submit"
                    class="w-full"
                    wire:loading.attr="disabled"
                    wire:target="sendOtp"
                >
                    <span
                        wire:loading.remove
                        wire:target="sendOtp"
                    >
                        Send OTP
                    </span>

                    <span
                        wire:loading
                        wire:target="sendOtp"
                    >
                        Please wait...
                    </span>
                </flux:button>
            </div>
        </form>

    @else

        <form
            wire:submit="verifyOtp"
            class="flex flex-col gap-6"
        >
            <flux:input
                wire:model="otp"
                label="Enter OTP"
                type="text"
                inputmode="numeric"
                required
                autofocus
                maxlength="6"
                placeholder="Enter 6 digit OTP"
            />

            <div class="flex items-center justify-end">
                <flux:button
                    variant="primary"
                    type="submit"
                    class="w-full"
                    wire:loading.attr="disabled"
                    wire:target="verifyOtp"
                >
                    <span
                        wire:loading.remove
                        wire:target="verifyOtp"
                    >
                        Verify OTP & Login
                    </span>

                    <span
                        wire:loading
                        wire:target="verifyOtp"
                    >
                        Verifying...
                    </span>
                </flux:button>
            </div>

            <button
                type="button"
                wire:click="sendOtp"
                class="text-sm font-medium text-blue-600 hover:underline"
            >
                Resend OTP
            </button>
        </form>

    @endif
</div>