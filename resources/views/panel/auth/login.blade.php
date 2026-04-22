@extends('panel.layouts.guest')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4"
     style="background: linear-gradient(135deg, #2a4365 0%, #1a365d 50%, #2a4365 100%);">

    <div class="w-full max-w-sm">
        <div class="bg-white rounded-lg shadow-2xl overflow-hidden">

            {{-- Header banner --}}
            <div class="relative h-24 flex items-center justify-center"
                 style="background: linear-gradient(135deg, #3a8bd4 0%, #2094f3 100%);">
                <div class="text-center">
                    <div class="text-white text-xl font-bold tracking-widest" style="text-shadow: 0 1px 2px rgba(0,0,0,.3)">PERFECT WORLD</div>
                    <div class="text-white/80 text-[10px] tracking-[0.3em] mt-0.5">ADMIN PANEL</div>
                </div>
            </div>

            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-4 p-3 border border-red-300 bg-red-50 rounded text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('panel.login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="pw-label">Username</label>
                        <input type="text" name="username" value="{{ old('username') }}" class="pw-input" required autofocus placeholder="Enter your login account">
                    </div>

                    <div>
                        <label class="pw-label">Password</label>
                        <input type="password" name="password" class="pw-input" required placeholder="Enter password">
                    </div>

                    <label class="flex items-center gap-2 text-sm text-[var(--color-text-soft)]">
                        <input type="checkbox" name="remember"> Remember me
                    </label>

                    <button type="submit" class="pw-btn pw-btn-primary w-full !py-2.5">
                        Log in
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-xs text-white/60 mt-6">
            &copy; {{ date('Y') }} PW Admin Panel
        </p>
    </div>
</div>
@endsection
