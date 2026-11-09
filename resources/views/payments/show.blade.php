@extends('layouts.app')
@section('title', 'Платіж #' . $payment->id)
@section('header', 'Платежі')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('payments.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">Платіж #{{ $payment->id }}</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ $payment->student->avatar_url }}" class="w-14 h-14 rounded-2xl object-cover" alt="">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">{{ $payment->student->name }}</h3>
                    <p class="text-slate-500 text-sm">{{ $payment->student->email }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @php $sc = ['paid'=>'bg-emerald-100 text-emerald-700','pending'=>'bg-orange-100 text-orange-600','cancelled'=>'bg-red-100 text-red-600','refunded'=>'bg-slate-100 text-slate-500']; @endphp
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $sc[$payment->status] ?? '' }}">{{ ucfirst($payment->status) }}</span>
                <a href="{{ route('payments.edit', $payment) }}" class="inline-flex items-center gap-2 border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition">
                    <i class="fa fa-pen text-xs"></i> Редагувати
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 py-5 border-y border-slate-100 mb-5">
            <div class="text-center">
                <p class="text-3xl font-bold text-indigo-600">{{ number_format($payment->amount, 0) }} ₴</p>
                <p class="text-xs text-slate-400 mt-1">Сума</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-semibold text-slate-700 capitalize">{{ str_replace('_', ' ', $payment->method) }}</p>
                <p class="text-xs text-slate-400 mt-1">Метод оплати</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-semibold text-slate-700">{{ $payment->payment_date->format('d.m.Y') }}</p>
                <p class="text-xs text-slate-400 mt-1">Дата платежу</p>
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between py-2 border-b border-slate-50">
                <span class="text-sm text-slate-500">Курс</span>
                <span class="text-sm font-medium text-slate-800">{{ $payment->enrollment->group->course->title ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-slate-50">
                <span class="text-sm text-slate-500">Група</span>
                <span class="text-sm font-medium text-slate-800">{{ $payment->enrollment->group->name ?? '—' }}</span>
            </div>
            @if($payment->enrollment_id)
            <div class="flex items-center justify-between py-2 border-b border-slate-50">
                <span class="text-sm text-slate-500">Зарахування #</span>
                <a href="{{ route('enrollments.show', $payment->enrollment_id) }}" class="text-sm font-medium text-indigo-600 hover:underline">
                    #{{ $payment->enrollment_id }}
                </a>
            </div>
            @endif
            @if($payment->notes)
            <div class="py-2">
                <p class="text-sm text-slate-500 mb-1">Примітки</p>
                <p class="text-sm text-slate-700 bg-slate-50 rounded-xl p-3">{{ $payment->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
