@extends('layouts.app')
@section('title', 'Деталі зарахування')
@section('header', 'Зарахування')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('enrollments.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">Зарахування #{{ $enrollment->id }}</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <div class="flex items-start justify-between gap-4 mb-5">
            <div class="flex items-center gap-4">
                <img src="{{ $enrollment->student->avatar_url }}" class="w-14 h-14 rounded-2xl object-cover" alt="">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">{{ $enrollment->student->name }}</h3>
                    <p class="text-slate-500 text-sm">{{ $enrollment->student->email }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @php $sc = ['active'=>'bg-emerald-100 text-emerald-700','completed'=>'bg-blue-100 text-blue-700','cancelled'=>'bg-red-100 text-red-600','suspended'=>'bg-orange-100 text-orange-600']; @endphp
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $sc[$enrollment->status] ?? '' }}">{{ ucfirst($enrollment->status) }}</span>
                <a href="{{ route('enrollments.edit', $enrollment) }}" class="inline-flex items-center gap-2 border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition">
                    <i class="fa fa-pen text-xs"></i> Редагувати
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 py-4 border-y border-slate-100">
            <div>
                <p class="text-xs text-slate-400 mb-1">Курс</p>
                <p class="text-sm font-medium text-slate-700">{{ $enrollment->group->course->title ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-1">Група</p>
                <p class="text-sm font-medium text-slate-700">{{ $enrollment->group->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-1">Дата зарахування</p>
                <p class="text-sm font-medium text-slate-700">{{ $enrollment->enrolled_at->format('d.m.Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-1">Оплачено</p>
                <p class="text-lg font-bold text-emerald-600">{{ number_format($enrollment->total_paid, 0) }} ₴</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-1">Платежів</p>
                <p class="text-sm font-medium text-slate-700">{{ $enrollment->payments->count() }}</p>
            </div>
        </div>

        @if($enrollment->notes)
        <div class="mt-4 bg-slate-50 rounded-xl p-3">
            <p class="text-xs font-medium text-slate-500 mb-1">Примітки</p>
            <p class="text-sm text-slate-700">{{ $enrollment->notes }}</p>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-sm font-semibold text-slate-700">Платежі</h4>
            <a href="{{ route('payments.create') }}" class="text-xs text-indigo-500 hover:underline">+ Додати платіж</a>
        </div>
        @forelse($enrollment->payments as $payment)
        <div class="flex items-center justify-between py-3 border-b border-slate-50 last:border-0">
            <div>
                <p class="text-sm font-medium text-slate-800">{{ number_format($payment->amount, 0) }} ₴</p>
                <p class="text-xs text-slate-400">{{ ucfirst($payment->method) }} · {{ $payment->payment_date->format('d.m.Y') }}</p>
            </div>
            @php $sc = ['paid'=>'bg-emerald-100 text-emerald-700','pending'=>'bg-orange-100 text-orange-600','cancelled'=>'bg-red-100 text-red-600','refunded'=>'bg-slate-100 text-slate-500']; @endphp
            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$payment->status] ?? '' }}">{{ ucfirst($payment->status) }}</span>
        </div>
        @empty
        <p class="text-sm text-slate-400">Немає платежів</p>
        @endforelse
    </div>
</div>
@endsection
