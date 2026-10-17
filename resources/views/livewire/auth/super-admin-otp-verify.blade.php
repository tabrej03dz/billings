<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|digits:6')]
    public string $otp = '';

    public function mount(): void
    {
        if (! session()->has('super_admin_otp_user_id')) {
            $this->redirect(route('login', absolute: false), navigate: true);
        }
    }

    public function verify(): void
    {
        $sessionOtp = session('super_admin_otp');
        $expiresAt = session('super_admin_otp_expires_at');

        if (! $sessionOtp || ! $expiresAt || now()->timestamp > $expiresAt) {
            session()->forget([
                'super_admin_otp_user_id',
                'super_admin_otp_remember',
                'super_admin_otp',
                'super_admin_otp_expires_at',
            ]);

            throw ValidationException::withMessages([
                'otp' => 'OTP expired. Please login again.',
            ]);
        }

        if ($this->otp != $sessionOtp) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid OTP.',
            ]);
        }

        $user = \App\Models\User::find(session('super_admin_otp_user_id'));

        // if (! $user || ! $user->hasRole('super admin')) {
        //     throw ValidationException::withMessages([
        //         'otp' => 'Invalid login request.',
        //     ]);
        // }

        Auth::login($user, session('super_admin_otp_remember', true));

        session()->forget([
            'super_admin_otp_user_id',
            'super_admin_otp_remember',
            'super_admin_otp',
            'super_admin_otp_expires_at',
        ]);

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Verify Super Admin OTP')"
        :description="__('Enter the OTP sent to your email address')"
    />

    <form wire:submit="verify" class="flex flex-col gap-6">
        <flux:input
            wire:model="otp"
            :label="__('OTP')"
            type="text"
            required
            maxlength="6"
            placeholder="Enter 6 digit OTP"
        />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Verify OTP') }}
            </flux:button>
        </div>
    </form>
</div>