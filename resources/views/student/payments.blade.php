@extends('layouts.app')
@section('title', 'Оплата')
@section('header', 'Оплата')

@section('content')
<div class="max-w-3xl">
    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    @if(session('success'))
    <div class="mb-5 bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-sm text-emerald-700 flex items-center gap-2">
        <i class="fa fa-circle-check flex-shrink-0"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700 flex items-center gap-2">
        <i class="fa fa-circle-xmark flex-shrink-0"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Money balance card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-4 mb-4 flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center flex-shrink-0
                    {{ $moneyBalance > 0 ? 'bg-emerald-500' : 'bg-slate-300' }}">
            <i class="fa fa-wallet text-white text-lg"></i>
        </div>
        <div class="flex-1">
            <p class="text-xs text-slate-400">Грошовий баланс</p>
            <p class="text-xl font-bold text-slate-800">{{ number_format($moneyBalance, 2) }} ₴</p>
            @if($moneyBalance > 0)
            <p class="text-xs text-emerald-600 mt-0.5">Списується автоматично якщо уроків немає</p>
            @endif
        </div>
        <button onclick="document.getElementById('topup-money-form').classList.toggle('hidden')"
                class="flex-shrink-0 text-xs bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-2 rounded-xl transition">
            <i class="fa fa-plus mr-1"></i>Поповнити
        </button>
    </div>

    {{-- Money topup form --}}
    <div id="topup-money-form" class="hidden bg-emerald-50 border border-emerald-100 rounded-2xl p-5 mb-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">
            <i class="fa fa-wallet text-emerald-500 mr-1.5"></i>Поповнення грошового балансу
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Сума (₴) *</label>
                <input type="number" id="money-amount" min="10" step="1" placeholder="Наприклад: 500"
                       value="{{ old('amount') }}"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Метод оплати *</label>
                <select id="money-method"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-white"
                        onchange="toggleMoneyMethod()">
                    <option value="cash">Готівка</option>
                    <option value="card">Картка</option>
                    <option value="bank_transfer">Банківський переказ</option>
                    @if($wfpConfigured)<option value="online">Онлайн (WayForPay)</option>@endif
                </select>
            </div>
        </div>

        {{-- Manual form (cash/card/transfer) --}}
        <form id="money-manual-form" method="POST" action="{{ route('student.payments.topup') }}">
            @csrf
            <input type="hidden" id="money-manual-amount" name="amount">
            <input type="hidden" id="money-manual-method" name="method">
            <button type="submit"
                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2.5 rounded-xl text-sm transition shadow-sm">
                <i class="fa fa-paper-plane mr-1.5 text-xs"></i>Надіслати заявку
            </button>
        </form>

        {{-- WayForPay form (online only) --}}
        @if($wfpConfigured)
        <form id="money-wfp-form" class="hidden" method="POST" action="{{ route('wayforpay.money') }}">
            @csrf
            <input type="hidden" id="money-wfp-amount" name="amount">
            <button type="submit"
                    class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2.5 rounded-xl text-sm transition shadow-sm">
                <i class="fa fa-credit-card mr-1.5 text-xs"></i>Оплатити онлайн через WayForPay
            </button>
        </form>
        @endif

        <p id="money-hint-manual" class="text-xs text-slate-400 mt-2 text-center">Після підтвердження адміністратором кошти зараховуються на баланс</p>
        <p id="money-hint-online" class="hidden text-xs text-slate-400 mt-2 text-center">Ви будете перенаправлені на сторінку оплати WayForPay</p>
    </div>

    {{-- Balance cards --}}
    @if($enrollments->count())
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
        @foreach($enrollments as $enr)
        <div class="bg-white rounded-2xl shadow-sm border {{ $enr->lessons_balance > 0 ? 'border-indigo-100' : 'border-red-100' }} p-4 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center flex-shrink-0
                        {{ $enr->lessons_balance > 0 ? 'bg-indigo-500' : 'bg-red-500' }}">
                <span class="text-2xl font-bold text-white leading-none">{{ $enr->lessons_balance }}</span>
                <span class="text-white text-xs opacity-80">уроків</span>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-400 truncate">{{ $enr->group->course->title ?? '—' }}</p>
                <p class="text-sm font-semibold text-slate-800 truncate">{{ $enr->group->name ?? '—' }}</p>
                @if($enr->lessons_balance <= 2 && $enr->lessons_balance > 0)
                <p class="text-xs text-amber-600 mt-0.5"><i class="fa fa-triangle-exclamation mr-1"></i>Скоро закінчаться — поповніть</p>
                @elseif($enr->lessons_balance === 0)
                <p class="text-xs text-red-500 mt-0.5 font-medium"><i class="fa fa-circle-xmark mr-1"></i>Баланс вичерпано</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Top-up form --}}
    @if($enrollments->count() && $tariffPlans->count())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">
            <i class="fa fa-plus-circle text-indigo-400 mr-1.5"></i>Поповнити баланс уроків
        </h3>

        <div class="mb-4">
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Курс / Зарахування</label>
            <select id="sel-enrollment" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                @foreach($enrollments as $e)
                <option value="{{ $e->id }}">{{ $e->group->course->title ?? '—' }} — {{ $e->group->name ?? '—' }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-5">
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Оберіть тариф</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($tariffPlans as $tariff)
                <label class="cursor-pointer select-none">
                    <input type="radio" name="_tariff" value="{{ $tariff->id }}" class="sr-only peer" {{ $loop->first ? 'checked' : '' }}>
                    <div class="border-2 border-slate-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50
                                rounded-xl p-3 transition">
                        <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $tariff->name }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            <i class="fa fa-graduation-cap mr-1 text-indigo-400"></i>{{ $tariff->count_lessons }} уроків
                        </p>
                        @if($tariff->description)
                        <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $tariff->description }}</p>
                        @endif
                        <p class="text-base font-bold text-indigo-600 mt-2">{{ number_format($tariff->amount, 0) }} ₴</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
            @if($wfpConfigured)
            <form method="POST" action="{{ route('wayforpay.initiate') }}" id="wfp-form" class="flex-1">
                @csrf
                <input type="hidden" name="enrollment_id" id="wfp-enrollment">
                <input type="hidden" name="tariff_plan_id" id="wfp-tariff">
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-3 rounded-xl text-sm transition shadow-sm">
                    <i class="fa fa-credit-card text-xs"></i> Оплатити онлайн
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('student.payments.pay') }}" id="manual-form" class="{{ $wfpConfigured ? 'flex-1' : 'w-full' }}">
                @csrf
                <input type="hidden" name="enrollment_id" id="manual-enrollment">
                <input type="hidden" name="tariff_plan_id" id="manual-tariff">
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium py-3 rounded-xl text-sm transition">
                    <i class="fa fa-file-invoice text-xs"></i> Заявка (готівка / переказ)
                </button>
            </form>
        </div>
    </div>
    @elseif(!$enrollments->count())
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-700 mb-6 flex items-center gap-2">
        <i class="fa fa-circle-info flex-shrink-0"></i> Для поповнення потрібне активне зарахування на курс.
    </div>
    @elseif(!$tariffPlans->count())
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-700 mb-6 flex items-center gap-2">
        <i class="fa fa-circle-info flex-shrink-0"></i> Тарифні плани ще не налаштовані. Зверніться до адміністратора.
    </div>
    @endif

    {{-- Payment history --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Історія платежів</h3>
            <span class="text-xs text-slate-400">{{ $payments->count() }} записів</span>
        </div>
        @forelse($payments as $payment)
        @php
            $sc = ['pending'=>'bg-amber-100 text-amber-700','paid'=>'bg-emerald-100 text-emerald-700',
                   'cancelled'=>'bg-red-100 text-red-600','refunded'=>'bg-slate-100 text-slate-500'];
            $sl = ['pending'=>'Очікує','paid'=>'Оплачено','cancelled'=>'Скасовано','refunded'=>'Повернено'];
            $ml = ['cash'=>'Готівка','card'=>'Картка','bank_transfer'=>'Переказ','online'=>'Онлайн'];
        @endphp
        <div class="px-5 py-3.5 border-b border-slate-50 last:border-0 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                        {{ $payment->status === 'paid' ? 'bg-emerald-100' : 'bg-slate-100' }}">
                <i class="fa fa-graduation-cap text-sm {{ $payment->status === 'paid' ? 'text-emerald-600' : 'text-slate-400' }}"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800 truncate">
                    {{ $payment->tariffPlan->name ?? ($payment->lessons_count > 0 ? $payment->lessons_count . ' уроків' : ($payment->enrollment->group->course->title ?? '—')) }}
                </p>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ $payment->payment_date->format('d.m.Y') }} · {{ $ml[$payment->method] ?? $payment->method }}
                    @if($payment->tariffPlan) · {{ $payment->tariffPlan->count_lessons }} уроків @endif
                </p>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="font-bold text-slate-800 text-sm">{{ number_format($payment->amount, 0) }} ₴</p>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$payment->status] ?? '' }}">
                    {{ $sl[$payment->status] ?? $payment->status }}
                </span>
            </div>
        </div>
        @empty
        <div class="px-6 py-10 text-center text-slate-400 text-sm">Платежів ще немає</div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
function toggleMoneyMethod() {
    const method   = document.getElementById('money-method')?.value;
    const isOnline = method === 'online';
    const manualForm = document.getElementById('money-manual-form');
    const wfpForm    = document.getElementById('money-wfp-form');
    const hintManual = document.getElementById('money-hint-manual');
    const hintOnline = document.getElementById('money-hint-online');
    if (manualForm) manualForm.classList.toggle('hidden', isOnline);
    if (wfpForm)    wfpForm.classList.toggle('hidden', !isOnline);
    if (hintManual) hintManual.classList.toggle('hidden', isOnline);
    if (hintOnline) hintOnline.classList.toggle('hidden', !isOnline);
}

document.getElementById('topup-money-form')?.addEventListener('submit', function(e) {
    const amount = document.getElementById('money-amount')?.value;
    document.getElementById('money-manual-amount') && (document.getElementById('money-manual-amount').value = amount);
    document.getElementById('money-wfp-amount')    && (document.getElementById('money-wfp-amount').value    = amount);
    document.getElementById('money-manual-method') && (document.getElementById('money-manual-method').value = document.getElementById('money-method')?.value);
}, true);

document.addEventListener('DOMContentLoaded', function() {
    toggleMoneyMethod();
    document.querySelectorAll('#money-manual-form, #money-wfp-form').forEach(form => {
        form.addEventListener('submit', function() {
            const amount = document.getElementById('money-amount')?.value;
            document.getElementById('money-manual-amount') && (document.getElementById('money-manual-amount').value = amount);
            document.getElementById('money-wfp-amount')    && (document.getElementById('money-wfp-amount').value    = amount);
            document.getElementById('money-manual-method') && (document.getElementById('money-manual-method').value = document.getElementById('money-method')?.value);
        });
    });
});

function syncForms() {
    const eid = document.getElementById('sel-enrollment')?.value;
    const tid = document.querySelector('input[name="_tariff"]:checked')?.value;
    document.getElementById('wfp-enrollment')  && (document.getElementById('wfp-enrollment').value = eid);
    document.getElementById('manual-enrollment') && (document.getElementById('manual-enrollment').value = eid);
    document.getElementById('wfp-tariff')      && (document.getElementById('wfp-tariff').value = tid);
    document.getElementById('manual-tariff')   && (document.getElementById('manual-tariff').value = tid);
}
document.addEventListener('DOMContentLoaded', () => {
    syncForms();
    document.getElementById('sel-enrollment')?.addEventListener('change', syncForms);
    document.querySelectorAll('input[name="_tariff"]').forEach(r => r.addEventListener('change', syncForms));
});
</script>
@endpush
@endsection
