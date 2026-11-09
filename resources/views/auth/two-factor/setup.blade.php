@extends('layouts.app')
@section('title', 'Налаштування 2FA')
@section('header', 'Безпека')

@section('content')
<div class="max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('profile.edit') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">Налаштування двофакторної автентифікації</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <div class="flex items-start gap-3 mb-5 p-4 bg-indigo-50 border border-indigo-100 rounded-xl">
            <i class="fa fa-circle-info text-indigo-500 mt-0.5 flex-shrink-0"></i>
            <div class="text-sm text-indigo-700">
                <p class="font-medium mb-1">Як налаштувати</p>
                <ol class="space-y-1 list-decimal list-inside text-indigo-600">
                    <li>Встановіть додаток: <strong>Google Authenticator</strong>, <strong>Authy</strong> або інший TOTP-додаток</li>
                    <li>Відскануйте QR-код нижче або введіть ключ вручну</li>
                    <li>Введіть 6-значний код для підтвердження</li>
                </ol>
            </div>
        </div>

        <div class="flex flex-col items-center py-6">
            <div class="bg-white border-2 border-slate-200 rounded-2xl p-4 shadow-sm mb-4">
                {!! $qrCodeSvg !!}
            </div>
            <p class="text-xs text-slate-500 mb-2">Або введіть ключ вручну:</p>
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5">
                <code class="text-sm font-mono font-semibold text-slate-700 tracking-widest select-all">{{ $secret }}</code>
                <button onclick="navigator.clipboard.writeText('{{ $secret }}').then(()=>this.innerHTML='<i class=\'fa fa-check text-emerald-500\'></i>')"
                        class="text-slate-400 hover:text-indigo-500 transition ml-2">
                    <i class="fa fa-copy text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Підтвердити налаштування</h3>

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            @foreach($errors->all() as $e)
            <p><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('two-factor.enable') }}" id="enableForm">
            @csrf
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2">6-значний код з додатку</label>
                <input
                    type="text"
                    name="code"
                    id="code"
                    maxlength="6"
                    autofocus
                    autocomplete="off"
                    placeholder="000000"
                    class="w-full border border-slate-200 rounded-xl px-4 py-3 text-center text-2xl font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-400"
                >
            </div>
            <button type="submit"
                class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-3 rounded-xl transition text-sm">
                <i class="fa fa-shield-halved mr-2"></i> Увімкнути 2FA
            </button>
        </form>
    </div>
</div>

<script>
    document.getElementById('code').addEventListener('input', function () {
        if (this.value.length === 6 && /^\d{6}$/.test(this.value)) {
            document.getElementById('enableForm').submit();
        }
    });
</script>
@endsection
