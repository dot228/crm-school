@extends('layouts.app')
@section('title', 'Двофакторна автентифікація')
@section('header', 'Безпека')

@section('content')
<div class="max-w-xl">
    <h2 class="text-lg font-semibold text-slate-800 mb-6">Двофакторна автентифікація (2FA)</h2>


    {{-- Recovery codes shown only right after enabling or regenerating --}}
    @if(session('recovery_codes'))
    <div class="mb-5 bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex items-start gap-3 mb-4">
            <i class="fa fa-triangle-exclamation text-amber-500 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="text-sm font-semibold text-amber-800">Збережіть коди відновлення</p>
                <p class="text-xs text-amber-600 mt-0.5">Ці коди показуються лише один раз. Зберігайте їх у безпечному місці — вони потрібні, якщо ви втратите доступ до додатку-аутентифікатора.</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 mb-4">
            @foreach(session('recovery_codes') as $code)
            <code class="bg-white border border-amber-200 rounded-lg px-3 py-2 text-sm font-mono font-medium text-slate-700 text-center tracking-wider">
                {{ $code }}
            </code>
            @endforeach
        </div>
        <button onclick="copyRecoveryCodes()"
                class="w-full text-center text-xs text-amber-700 border border-amber-300 rounded-lg py-2 hover:bg-amber-100 transition">
            <i class="fa fa-copy mr-1"></i> Копіювати всі коди
        </button>
    </div>
    <script>
        function copyRecoveryCodes() {
            const codes = @json(session('recovery_codes'));
            navigator.clipboard.writeText(codes.join('\n'));
            event.target.innerHTML = '<i class="fa fa-check mr-1"></i> Скопійовано!';
        }
    </script>
    @endif

    {{-- Status card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $user->hasTwoFactorEnabled() ? 'bg-emerald-100' : 'bg-slate-100' }}">
                    <i class="fa fa-shield-halved text-xl {{ $user->hasTwoFactorEnabled() ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800">Статус 2FA</p>
                    @if($user->hasTwoFactorEnabled())
                    <p class="text-sm text-emerald-600 font-medium">
                        <i class="fa fa-circle-check mr-1"></i> Увімкнено
                        <span class="text-slate-400 font-normal">· з {{ $user->two_factor_confirmed_at->format('d.m.Y') }}</span>
                    </p>
                    @else
                    <p class="text-sm text-slate-500"><i class="fa fa-circle-xmark mr-1 text-slate-400"></i> Вимкнено</p>
                    @endif
                </div>
            </div>
            @if(!$user->hasTwoFactorEnabled())
            <a href="{{ route('two-factor.setup') }}"
               class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                <i class="fa fa-plus text-xs"></i> Увімкнути
            </a>
            @endif
        </div>
    </div>

    @if($user->hasTwoFactorEnabled())
    {{-- Regenerate recovery codes --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-1">Коди відновлення</h3>
        <p class="text-xs text-slate-500 mb-4">Якщо ви втратили попередні коди відновлення, згенеруйте нові. Старі коди стануть недійсними.</p>

        @if($errors->hasBag('regenerate'))
        <div class="mb-3 bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700">
            @foreach($errors->getBag('regenerate')->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('two-factor.regenerate') }}" class="flex gap-3">
            @csrf
            <input type="password" name="password" placeholder="Введіть пароль для підтвердження"
                   class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <button type="submit"
                    class="flex-shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-4 py-2.5 rounded-xl text-sm transition">
                <i class="fa fa-rotate mr-1"></i> Оновити
            </button>
        </form>
    </div>

    {{-- Disable 2FA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-6">
        <h3 class="text-sm font-semibold text-red-700 mb-1">Вимкнути 2FA</h3>
        <p class="text-xs text-slate-500 mb-4">Після вимкнення ваш акаунт буде захищений лише паролем.</p>

        @error('password')
        <div class="mb-3 bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('two-factor.disable') }}" class="flex gap-3"
              onsubmit="return confirm('Ви впевнені, що хочете вимкнути 2FA?')">
            @csrf
            <input type="password" name="password" placeholder="Введіть пароль для підтвердження"
                   class="flex-1 border border-red-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            <button type="submit"
                    class="flex-shrink-0 bg-red-500 hover:bg-red-600 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition">
                <i class="fa fa-shield-xmark mr-1"></i> Вимкнути
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
