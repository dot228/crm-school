@extends('layouts.app')
@section('title', 'Тарифні плани')
@section('header', 'Тарифні плани')

@section('content')
<div class="flex items-start sm:items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Тарифні плани</h2>
        <p class="text-sm text-slate-500 mt-0.5 hidden sm:block">Управління пакетами уроків</p>
    </div>
    <a href="{{ route('tariff-plans.create') }}" class="flex-shrink-0 inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
        <i class="fa fa-plus text-xs"></i><span class="hidden sm:inline">Новий план</span>
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($plans as $plan)
    <div class="bg-white rounded-2xl shadow-sm border {{ $plan->status === 'active' ? 'border-indigo-100' : 'border-slate-100' }} p-6 flex flex-col">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h3 class="font-semibold text-slate-800 text-base">{{ $plan->name }}</h3>
                @if($plan->description)
                <p class="text-xs text-slate-500 mt-1">{{ Str::limit($plan->description, 80) }}</p>
                @endif
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $plan->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                {{ $plan->status === 'active' ? 'Активний' : 'Неактивний' }}
            </span>
        </div>

        <div class="flex items-end gap-4 mt-auto pt-4 border-t border-slate-100">
            <div>
                <p class="text-xs text-slate-400">Ціна</p>
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($plan->amount, 0) }} ₴</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Уроків</p>
                <p class="text-2xl font-bold text-slate-700">{{ $plan->count_lessons }}</p>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('tariff-plans.edit', $plan) }}" class="text-slate-400 hover:text-amber-500 transition"><i class="fa fa-pen"></i></a>
                <form method="POST" action="{{ route('tariff-plans.destroy', $plan) }}" onsubmit="return confirm('Видалити план?')">
                    @csrf @method('DELETE')
                    <button class="text-slate-400 hover:text-red-500 transition"><i class="fa fa-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-16 text-slate-400">
        <i class="fa fa-receipt text-3xl mb-3 block"></i>
        Тарифних планів ще немає
    </div>
    @endforelse
</div>

@if($plans->hasPages())
<div class="mt-6">{{ $plans->links() }}</div>
@endif
@endsection
