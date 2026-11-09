@extends('layouts.app')
@section('title', 'Мої учні')
@section('header', 'Мої учні')

@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-slate-800">Мої учні</h2>
        <span class="text-sm text-slate-400">{{ $students->count() }} учнів</span>
    </div>

    @if($students->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
        <i class="fa fa-users text-3xl text-slate-200 mb-3 block"></i>
        <p class="text-slate-400 text-sm">Учнів ще не призначено</p>
    </div>
    @else
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[640px]">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-4 py-3.5 font-medium text-slate-500 whitespace-nowrap">Учень</th>
                    <th class="text-left px-4 py-3.5 font-medium text-slate-500 whitespace-nowrap">Контакт</th>
                    <th class="text-left px-4 py-3.5 font-medium text-slate-500 whitespace-nowrap">Курс / Група</th>
                    <th class="text-left px-4 py-3.5 font-medium text-slate-500 whitespace-nowrap">Баланс</th>
                    <th class="text-left px-4 py-3.5 font-medium text-slate-500 whitespace-nowrap">Оплата</th>
                    <th class="text-right px-4 py-3.5"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                @php
                    $debt    = $student->payments->where('status','pending')->sum('amount');
                    $balance = $student->enrollments->sum('lessons_balance');
                @endphp
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-3">
                            <img src="{{ $student->avatar_url }}" class="w-8 h-8 rounded-lg object-cover flex-shrink-0" alt="">
                            <a href="{{ route('teacher.students.show', $student->id) }}">
                                <p class="font-medium text-slate-800 whitespace-nowrap">{{ $student->name }}</p>
                            </a>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-slate-500">
                        <p class="whitespace-nowrap">{{ $student->email }}</p>
                        @if($student->phone)<p class="text-xs text-slate-400">{{ $student->phone }}</p>@endif
                    </td>
                    <td class="px-4 py-3.5">
                        @foreach($student->enrollments->take(2) as $e)
                        <p class="text-xs text-slate-600 whitespace-nowrap">{{ $e->group->course->title ?? '—' }}
                            <span class="text-slate-400">/ {{ $e->group->name ?? '—' }}</span></p>
                        @endforeach
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="text-sm font-bold {{ $balance > 0 ? 'text-indigo-600' : 'text-red-500' }}">
                            {{ $balance }} ур.
                        </span>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full
                            {{ $debt > 0 ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $debt > 0 ? 'Борг: '.number_format($debt,0).' ₴' : 'Оплачено' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif
</div>
@endsection
