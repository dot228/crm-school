@extends('layouts.app')
@section('title', $student->name)
@section('header', 'Мої учні')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('teacher.students') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">{{ $student->name }}</h2>
    </div>

    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <div class="flex items-start gap-5">
            <img src="{{ $student->avatar_url }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0" alt="">
            <div class="flex-1">
                <p class="font-semibold text-slate-800 text-lg">{{ $student->name }}</p>
                <a href="mailto:{{ $student->email }}" class="text-slate-500 text-sm hover:text-indigo-600 transition">{{ $student->email }}</a>
                @if($student->phone)<p class="text-slate-500 text-sm"><i class="fa fa-phone mr-1 text-xs text-slate-400"></i><a href="tel:{{ $student->phone }}" class="hover:text-indigo-600 transition">{{ $student->phone }}</a></p>@endif
                @if($student->bio)<p class="text-slate-600 text-sm mt-2">{{ $student->bio }}</p>@endif
            </div>
            @php
                $debt = $student->payments->where('status','pending')->sum('amount');
                $paid = $student->payments->where('status','paid')->sum('amount');
            @endphp
            <div class="text-right flex-shrink-0">
                <p class="text-xs text-slate-400 mb-0.5">Статус оплати</p>
                <span class="text-sm font-bold {{ $debt > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                    {{ $debt > 0 ? 'Є заборгованість' : 'Оплачено' }}
                </span>
                @if($debt > 0)<p class="text-xs text-red-400 mt-0.5">{{ number_format($debt,0) }} ₴</p>@endif
            </div>
        </div>
    </div>

    {{-- Stats --}}
    @php $totalBalance = $student->enrollments->sum('lessons_balance'); @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3 text-center">
            <p class="text-xl font-bold text-blue-600">{{ $student->enrollments->count() }}</p>
            <p class="text-xs text-slate-400 mt-1">Зарахувань</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3 text-center">
            <p class="text-lg font-bold text-emerald-600 leading-tight">{{ number_format($paid,0) }} ₴</p>
            <p class="text-xs text-slate-400 mt-1">Оплачено</p>
        </div>
        <div class="bg-white rounded-2xl border {{ $totalBalance > 0 ? 'border-indigo-100' : 'border-red-100' }} shadow-sm p-3 text-center">
            <p class="text-xl font-bold {{ $totalBalance > 0 ? 'text-indigo-600' : 'text-red-500' }}">{{ $totalBalance }}</p>
            <p class="text-xs text-slate-400 mt-1">Баланс ур.</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3 text-center">
            <p class="text-xl font-bold text-violet-600">{{ $student->homeworkSubmissions->count() }}</p>
            <p class="text-xs text-slate-400 mt-1">Д/З здано</p>
        </div>
    </div>

    {{-- Guardian info --}}
    @if($student->guardian_name || $student->date_of_birth)
    <div class="bg-white rounded-2xl shadow-sm border {{ !$student->isAdult() ? 'border-amber-100' : 'border-slate-100' }} p-6 mb-5">
        <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <i class="fa fa-cake-candles text-slate-400"></i> Особисті дані
        </h4>
        @if($student->date_of_birth)
        <p class="text-sm text-slate-600 mb-2">
            Дата народження:
            <span class="font-medium text-slate-800">{{ $student->date_of_birth->format('d.m.Y') }}</span>
            <span class="text-slate-400 text-xs ml-1">({{ $student->age }} р.)</span>
            @if(!$student->isAdult())
            <span class="ml-1 px-1.5 py-0.5 bg-amber-100 text-amber-700 text-xs rounded-full">неповнолітній</span>
            @endif
        </p>
        @endif
        @if($student->guardian_name)
        <div class="flex items-start gap-2 mt-1">
            <i class="fa fa-user-shield text-amber-500 mt-0.5"></i>
            <div>
                <p class="text-sm font-medium text-slate-800">
                    {{ $student->guardian_name }}
                    @if($student->guardian_relation)<span class="text-xs text-slate-400 font-normal ml-1">({{ $student->guardian_relation }})</span>@endif
                </p>
                @if($student->guardian_phone)<p class="text-xs text-slate-500"><i class="fa fa-phone mr-1"></i>{{ $student->guardian_phone }}</p>@endif
                @if($student->guardian_email)<p class="text-xs text-slate-500"><i class="fa fa-envelope mr-1"></i>{{ $student->guardian_email }}</p>@endif
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Enrollments --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Зарахування</h4>
        @forelse($student->enrollments as $e)
        <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0 gap-2">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-slate-800 truncate">{{ $e->group->course->title ?? '—' }}</p>
                <p class="text-xs text-slate-400">Група: {{ $e->group->name ?? '—' }} · {{ $e->enrolled_at->format('d.m.Y') }}</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-sm font-bold {{ $e->lessons_balance > 0 ? 'text-indigo-600' : 'text-red-500' }}">{{ $e->lessons_balance }} ур.</span>
                <span class="px-2 py-0.5 rounded-full text-xs {{ $e->status==='active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $e->status }}</span>
            </div>
        </div>
        @empty
        <p class="text-sm text-slate-400 text-center py-4">Немає зарахувань</p>
        @endforelse
    </div>

    {{-- Homework submissions --}}
    @if($student->homeworkSubmissions->count())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Домашні завдання</h4>
        @php $psc=['pending'=>'bg-slate-100 text-slate-500','submitted'=>'bg-amber-100 text-amber-700','reviewed'=>'bg-emerald-100 text-emerald-700']; @endphp
        @foreach($student->homeworkSubmissions as $hw)
        <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
            <div>
                <p class="text-sm font-medium text-slate-800">{{ $hw->lesson->title ?? '—' }}</p>
                <p class="text-xs text-slate-400">{{ $hw->submitted_at ? $hw->submitted_at->format('d.m.Y H:i') : '—' }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($hw->grade)<span class="text-sm font-bold text-indigo-600">{{ $hw->grade }}</span>@endif
                <span class="px-2 py-0.5 rounded-full text-xs {{ $psc[$hw->status] ?? '' }}">{{ ucfirst($hw->status) }}</span>
                @if($hw->status === 'submitted')
                <a href="{{ route('teacher.homework.show', $hw->id) }}" class="text-xs text-indigo-600 hover:underline ml-1">Перевірити</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
