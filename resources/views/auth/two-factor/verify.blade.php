<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Двофакторна автентифікація</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex items-center justify-center p-4">
<div class="w-full max-w-md">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-500/20 border border-indigo-500/30 rounded-2xl mb-4">
            <i class="fa fa-shield-halved text-indigo-400 text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-white">Двофакторна автентифікація</h1>
        <p class="text-slate-400 text-sm mt-2">Введіть код із вашого додатку-аутентифікатора</p>
    </div>

    <div class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-8 shadow-2xl">

        @if($errors->any())
        <div class="mb-5 bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-sm text-red-300">
            @foreach($errors->all() as $e)
            <p><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('two-factor.verify.submit') }}" id="verifyForm">
            @csrf
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-300 mb-2">
                    Код аутентифікації
                </label>
                <input
                    type="text"
                    name="code"
                    id="code"
                    maxlength="10"
                    autofocus
                    autocomplete="one-time-code"
                    placeholder="000 000"
                    class="w-full bg-white/10 border border-white/20 text-white text-center text-2xl tracking-widest font-mono rounded-xl px-4 py-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-white/20"
                >
                <p class="text-xs text-slate-500 mt-2 text-center">Або введіть 10-символьний код відновлення</p>
            </div>

            <button type="submit"
                class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-indigo-500/25 text-sm">
                <i class="fa fa-check mr-2"></i> Підтвердити
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-slate-400 hover:text-slate-200 transition">
                <i class="fa fa-arrow-left mr-1"></i> Повернутися до входу
            </a>
        </div>
    </div>

    <p class="text-center text-xs text-slate-600 mt-6">
        Відкрийте Google Authenticator, Authy або інший TOTP-додаток
    </p>
</div>

<script>
    document.getElementById('code').addEventListener('input', function (e) {
        this.value = this.value.replace(/\s/g, '');
        if (this.value.length === 6 && /^\d{6}$/.test(this.value)) {
            document.getElementById('verifyForm').submit();
        }
    });
</script>
</body>
</html>
