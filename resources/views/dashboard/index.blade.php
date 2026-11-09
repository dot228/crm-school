@extends('layouts.app')
@section('title', 'Дашборд')
@section('header', 'Дашборд')

@section('content')
{{-- Stats Grid --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-500 font-medium">Студенти</span>
            <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center">
                <i class="fa fa-user-graduate text-indigo-500 text-sm"></i>
            </div>
        </div>
        <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ $stats['total_students'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Активних зарахувань: {{ $stats['active_enrollments'] }}</p>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-500 font-medium">Викладачі</span>
            <div class="w-9 h-9 bg-violet-100 rounded-xl flex items-center justify-center">
                <i class="fa fa-chalkboard-teacher text-violet-500 text-sm"></i>
            </div>
        </div>
        <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ $stats['total_teachers'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Групи: {{ $stats['total_groups'] }}</p>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-500 font-medium">Курси</span>
            <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center">
                <i class="fa fa-book text-emerald-500 text-sm"></i>
            </div>
        </div>
        <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ $stats['total_courses'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Нових цього місяця: {{ $stats['new_enrollments'] }}</p>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-500 font-medium">Дохід</span>
            <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center">
                <i class="fa fa-dollar-sign text-amber-500 text-sm"></i>
            </div>
        </div>
        <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ number_format($stats['total_revenue'], 0, '.', ' ') }} ₴</p>
        <p class="text-xs text-orange-500 mt-1">Очікується: {{ $stats['pending_payments'] }} платежів</p>
    </div>
</div>

{{-- Revenue Chart --}}
<div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6">
    <h3 class="text-sm font-semibold text-slate-700 mb-4">Дохід за 12 місяців</h3>
    <div class="flex items-end gap-1.5 h-32">
        @php $maxAmount = collect($monthlyRevenue)->max('amount') ?: 1; @endphp
        @foreach($monthlyRevenue as $item)
        <div class="flex-1 flex flex-col items-center gap-1 group">
            <span class="text-xs text-slate-400 opacity-0 group-hover:opacity-100 transition whitespace-nowrap">
                {{ number_format($item['amount'], 0) }}
            </span>
            <div class="w-full bg-indigo-500 rounded-t-md transition-all hover:bg-indigo-400"
                 style="height: {{ max(4, ($item['amount'] / $maxAmount) * 100) }}%"
                 title="{{ $item['month'] }}: {{ number_format($item['amount'], 0) }} ₴"></div>
            <span class="hidden sm:block text-[9px] text-slate-400 rotate-45 origin-left mt-1 whitespace-nowrap">{{ substr($item['month'], 0, 3) }}</span>
        </div>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Recent Enrollments --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Останні зарахування</h3>
            <a href="{{ route('enrollments.index') }}" class="text-xs text-indigo-500 hover:underline">Всі</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($recentEnrollments as $e)
            <div class="flex items-center gap-3 px-6 py-3">
                <img src="{{ $e->student->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-800 truncate">{{ $e->student->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ $e->group->course->title ?? '—' }} / {{ $e->group->name }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $e->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $e->status }}
                    </span>
                    <p class="text-xs text-slate-400 mt-1">{{ $e->enrolled_at->format('d.m.Y') }}</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-slate-400 px-6 py-4">Немає зарахувань</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Payments --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Останні платежі</h3>
            <a href="{{ route('payments.index') }}" class="text-xs text-indigo-500 hover:underline">Всі</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($recentPayments as $p)
            <div class="flex items-center gap-3 px-6 py-3">
                <img src="{{ $p->student->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-800 truncate">{{ $p->student->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ $p->enrollment->group->course->title ?? '—' }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-sm font-semibold {{ $p->status === 'paid' ? 'text-emerald-600' : 'text-orange-500' }}">
                        {{ number_format($p->amount, 0) }} ₴
                    </p>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $p->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' }}">
                        {{ $p->status }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-slate-400 px-6 py-4">Немає платежів</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
