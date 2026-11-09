@extends('layouts.app')
@section('title', 'Налаштування Zoom')
@section('header', 'Налаштування')

@section('content')
<div class="max-w-xl">
    <h2 class="text-lg font-semibold text-slate-800 mb-6">Інтеграція з Zoom</h2>

    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 mb-5 flex items-start gap-3">
        <i class="fa fa-circle-info text-indigo-500 flex-shrink-0 mt-0.5"></i>
        <div class="text-sm text-indigo-700">
            <p class="font-medium mb-1">Server-to-Server OAuth (рекомендовано)</p>
            <p class="text-indigo-600">Створіть додаток типу <strong>Server-to-Server OAuth</strong> на
            <a href="https://marketplace.zoom.us/develop/create" target="_blank" class="underline">marketplace.zoom.us</a>
            та скопіюйте облікові дані нижче.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
        <ul class="space-y-1">@foreach($errors->all() as $e)<li><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <form method="POST" action="{{ route('settings.zoom.update') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Account ID</label>
                <input type="text" name="zoom_account_id"
                       value="{{ old('zoom_account_id', $settings['zoom_account_id'] ?? '') }}"
                       placeholder="xxxxxxxxxxxxxxxxxxxxxxx"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Client ID</label>
                <input type="text" name="zoom_client_id"
                       value="{{ old('zoom_client_id', $settings['zoom_client_id'] ?? '') }}"
                       placeholder="xxxxxxxxxxxxxxxxxxxxxxx"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Client Secret</label>
                <div class="relative">
                    <input type="password" name="zoom_client_secret" id="zoom_secret"
                           value="{{ old('zoom_client_secret', $settings['zoom_client_secret'] ?? '') }}"
                           placeholder="••••••••••••••••••••••"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400 pr-10">
                    <button type="button" onclick="toggleSecret()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <i class="fa fa-eye text-xs" id="secret-eye"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Webhook Token (необов'язково)</label>
                <input type="text" name="zoom_webhook_token"
                       value="{{ old('zoom_webhook_token', $settings['zoom_webhook_token'] ?? '') }}"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email хоста за замовчуванням</label>
                <input type="email" name="zoom_default_host"
                       value="{{ old('zoom_default_host', $settings['zoom_default_host'] ?? '') }}"
                       placeholder="teacher@school.com"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition">
                    <i class="fa fa-floppy-disk mr-2"></i> Зберегти налаштування
                </button>
                @php $hasCredentials = !empty($settings['zoom_account_id']) && !empty($settings['zoom_client_id']); @endphp
                <span class="text-sm {{ $hasCredentials ? 'text-emerald-600' : 'text-slate-400' }}">
                    <i class="fa {{ $hasCredentials ? 'fa-circle-check' : 'fa-circle-xmark' }} mr-1"></i>
                    {{ $hasCredentials ? 'Налаштовано' : 'Не налаштовано' }}
                </span>
            </div>
        </form>
    </div>
</div>

<script>
function toggleSecret() {
    const field = document.getElementById('zoom_secret');
    const eye = document.getElementById('secret-eye');
    if (field.type === 'password') {
        field.type = 'text';
        eye.className = 'fa fa-eye-slash text-xs';
    } else {
        field.type = 'password';
        eye.className = 'fa fa-eye text-xs';
    }
}
</script>
@endsection
