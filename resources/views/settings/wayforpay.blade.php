@extends('layouts.app')
@section('title', 'Налаштування WayForPay')
@section('header', 'Налаштування')

@section('content')
<div class="max-w-xl">
    <h2 class="text-lg font-semibold text-slate-800 mb-6">WayForPay — платіжна система</h2>


    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center gap-3 p-3 bg-indigo-50 rounded-xl mb-6">
            <div class="w-9 h-9 bg-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa fa-credit-card text-white text-sm"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-indigo-800">WayForPay</p>
                <p class="text-xs text-indigo-500">
                    Merchant Account та Secret Key знаходяться в особистому кабінеті
                    <a href="https://secure.wayforpay.com/" target="_blank" class="underline">wayforpay.com</a>
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.wayforpay.update') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Merchant Account</label>
                    <input type="text" name="wfp_merchant_account"
                           value="{{ old('wfp_merchant_account', $settings['wfp_merchant_account'] ?? '') }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="test_merch_n1">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Merchant Secret Key
                        <span class="text-xs text-slate-400 font-normal ml-1">зберігається в зашифрованому вигляді</span>
                    </label>
                    <input type="password" name="wfp_merchant_secret"
                           value="{{ old('wfp_merchant_secret', $settings['wfp_merchant_secret'] ?? '') }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="••••••••••••••••">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Merchant Domain
                        <span class="text-xs text-slate-400 font-normal ml-1">домен сайту (наприклад: myschool.com)</span>
                    </label>
                    <input type="text" name="wfp_merchant_domain"
                           value="{{ old('wfp_merchant_domain', $settings['wfp_merchant_domain'] ?? parse_url(config('app.url'), PHP_URL_HOST)) }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="myschool.com">
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition shadow-sm">
                    <i class="fa fa-floppy-disk mr-1.5 text-xs"></i> Зберегти
                </button>

                @php $isConfigured = !empty($settings['wfp_merchant_account'] ?? null) && !empty($settings['wfp_merchant_secret'] ?? null); @endphp
                @if($isConfigured)
                <span class="flex items-center gap-1.5 text-sm text-emerald-600">
                    <i class="fa fa-circle-check"></i> Налаштовано та активно
                </span>
                @else
                <span class="flex items-center gap-1.5 text-sm text-slate-400">
                    <i class="fa fa-circle-xmark"></i> Не налаштовано
                </span>
                @endif
            </div>
        </form>
    </div>

    <div class="mt-4 bg-amber-50 border border-amber-100 rounded-xl p-4 text-xs text-amber-700">
        <p class="font-semibold mb-1"><i class="fa fa-triangle-exclamation mr-1"></i>Webhook URL (Service URL)</p>
        <code class="bg-amber-100 px-2 py-1 rounded text-xs break-all">{{ route('wayforpay.callback') }}</code>
        <p class="mt-2">Вкажіть цю адресу в налаштуваннях WayForPay як Service URL для отримання підтверджень оплати.</p>
    </div>
</div>
@endsection
