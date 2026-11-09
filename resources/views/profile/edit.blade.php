@extends('layouts.app')
@section('title', 'Редагувати профіль')
@section('header', 'Профіль')

@section('content')
<div class="max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('profile.show') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">Редагувати профіль</h2>
    </div>

    {{-- ── Profile form ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        @if($errors->hasBag('default') || ($errors->any() && !$errors->hasBag('regenerate')))
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div class="flex items-center gap-4">
                <img src="{{ $user->avatar_url }}" class="w-16 h-16 rounded-xl object-cover" alt="">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">Фото профілю</label>
                    <input type="file" name="avatar_file" accept="image/*"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:text-xs">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Ім'я <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <input type="email" value="{{ $user->email }}" disabled
                           class="w-full border border-slate-100 rounded-xl px-4 py-2.5 text-sm bg-slate-50 text-slate-400 cursor-not-allowed">
                    <p class="text-xs text-slate-400 mt-1">Email змінити не можна</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Телефон</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Про себе</label>
                <textarea name="bio" rows="3"
                          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('bio', $user->bio) }}</textarea>
            </div>

            @if($user->isStudent())
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Дата народження</label>
                <input type="date" name="date_of_birth" id="dob_profile"
                       value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                       max="{{ date('Y-m-d') }}"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div id="guardian_profile"
                 class="{{ !$user->isAdult() ? '' : 'hidden' }} border border-amber-200 bg-amber-50 rounded-2xl p-5 space-y-4">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa fa-user-shield text-amber-600"></i>
                    <h4 class="text-sm font-semibold text-amber-800">Відповідальна особа (батьки / опікун)</h4>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Ім'я <span class="text-red-500">*</span></label>
                        <input type="text" name="guardian_name" value="{{ old('guardian_name', $user->guardian_name) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        @error('guardian_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Ступінь</label>
                        <input type="text" name="guardian_relation" value="{{ old('guardian_relation', $user->guardian_relation) }}"
                               placeholder="напр. Мати"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Телефон</label>
                        <input type="text" name="guardian_phone" value="{{ old('guardian_phone', $user->guardian_phone) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Email</label>
                        <input type="email" name="guardian_email" value="{{ old('guardian_email', $user->guardian_email) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                </div>
            </div>
            @endif

            <div class="border-t border-slate-100 pt-5">
                <p class="text-sm font-medium text-slate-700 mb-3">Змінити пароль <span class="text-slate-400 font-normal text-xs">(залиште порожнім, щоб не змінювати)</span></p>
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Новий пароль</label>
                        <input type="password" name="password"
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Підтвердити пароль</label>
                        <input type="password" name="password_confirmation"
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition">
                    Зберегти
                </button>
                <a href="{{ route('profile.show') }}" class="px-6 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Скасувати</a>
            </div>
        </form>
    </div>

    {{-- ── 2FA Section ──────────────────────────────────────────── --}}

    {{-- Recovery codes banner (shown right after enabling or regenerating) --}}
    @if(session('recovery_codes'))
    <div class="mb-5 bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex items-start gap-3 mb-4">
            <i class="fa fa-triangle-exclamation text-amber-500 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="text-sm font-semibold text-amber-800">Збережіть коди відновлення</p>
                <p class="text-xs text-amber-600 mt-0.5">Ці коди показуються лише один раз. Зберігайте їх у безпечному місці — вони потрібні, якщо ви втратите доступ до додатку-аутентифікатора.</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 mb-4" id="recoveryCodes">
            @foreach(session('recovery_codes') as $code)
            <code class="bg-white border border-amber-200 rounded-lg px-3 py-2 text-sm font-mono font-medium text-slate-700 text-center tracking-wider">{{ $code }}</code>
            @endforeach
        </div>
        <button onclick="copyRecoveryCodes()"
                class="w-full text-center text-xs text-amber-700 border border-amber-300 rounded-lg py-2 hover:bg-amber-100 transition">
            <i class="fa fa-copy mr-1"></i> Копіювати всі коди
        </button>
    </div>
    @push('scripts')
    <script>
        function copyRecoveryCodes() {
            const codes = @json(session('recovery_codes'));
            navigator.clipboard.writeText(codes.join('\n'));
            event.currentTarget.innerHTML = '<i class="fa fa-check mr-1"></i> Скопійовано!';
        }
    </script>
    @endpush
    @endif

    {{-- 2FA status card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $user->hasTwoFactorEnabled() ? 'bg-emerald-100' : 'bg-slate-100' }}">
                    <i class="fa fa-shield-halved text-xl {{ $user->hasTwoFactorEnabled() ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                </div>
                <div>
                    <p class="font-semibold text-slate-800">Двофакторна аутентифікація</p>
                    @if($user->hasTwoFactorEnabled())
                    <p class="text-sm text-emerald-600 font-medium mt-0.5">
                        <i class="fa fa-circle-check mr-1"></i> Увімкнено
                        <span class="text-slate-400 font-normal">· з {{ $user->two_factor_confirmed_at->format('d.m.Y') }}</span>
                    </p>
                    @else
                    <p class="text-sm text-slate-500 mt-0.5"><i class="fa fa-circle-xmark mr-1 text-slate-400"></i> Вимкнено</p>
                    @endif
                </div>
            </div>
            @if(!$user->hasTwoFactorEnabled())
            <a href="{{ route('two-factor.setup') }}"
               class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
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
@push('scripts')
<script>
(function() {
    const dob     = document.getElementById('dob_profile');
    const section = document.getElementById('guardian_profile');
    if (!dob || !section) return;

    dob.addEventListener('change', function() {
        if (!this.value) { section.classList.add('hidden'); return; }
        const d = new Date(this.value), t = new Date();
        let age = t.getFullYear() - d.getFullYear();
        const m = t.getMonth() - d.getMonth();
        if (m < 0 || (m === 0 && t.getDate() < d.getDate())) age--;
        section.classList.toggle('hidden', age >= 18);
    });
})();
</script>
@endpush
@endsection
