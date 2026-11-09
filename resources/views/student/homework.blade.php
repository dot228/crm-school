@extends('layouts.app')
@section('title', 'Домашні завдання')
@section('header', 'Домашні завдання')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-slate-800">Мої домашні завдання</h2>
        <span class="text-sm text-slate-400">{{ $submissions->count() }} завдань</span>
    </div>


    @php
        $psc = [
            'pending'   => ['bg-amber-100 text-amber-700',    'Треба здати'],
            'submitted' => ['bg-blue-100 text-blue-700',      'На перевірці'],
            'reviewed'  => ['bg-emerald-100 text-emerald-700','Перевірено'],
        ];
    @endphp

    @forelse($submissions as $sub)
    @php [$cls, $label] = $psc[$sub->status] ?? ['bg-slate-100 text-slate-500', $sub->status]; @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-4">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-slate-400 mb-1">
                    {{ $sub->lesson->course->title ?? '—' }}
                    @if($sub->assigned_at)
                    · <i class="fa fa-clock text-xs"></i> {{ $sub->assigned_at->format('d.m.Y') }}
                    @endif
                </p>
                <p class="font-semibold text-slate-800">{{ $sub->lesson->title ?? '—' }}</p>
                <p class="text-sm text-slate-600 mt-1.5 line-clamp-2">{{ $sub->assignment_text }}</p>
            </div>
            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $cls }}">{{ $label }}</span>
                @if($sub->grade)
                <span class="text-xl font-bold text-indigo-600">{{ $sub->grade }}</span>
                @endif
            </div>
        </div>

        @if($sub->teacher_comment)
        <div class="mt-3 pt-3 border-t border-slate-50 flex items-start gap-2 text-sm text-slate-600">
            <i class="fa fa-comment text-xs text-indigo-400 mt-0.5 flex-shrink-0"></i>
            <p class="italic">{{ $sub->teacher_comment }}</p>
        </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            @if($sub->submitted_at)
            <p class="text-xs text-slate-400">
                <i class="fa fa-clock mr-1"></i>Здано: {{ $sub->submitted_at->format('d.m.Y H:i') }}
            </p>
            @else
            <span></span>
            @endif
            <a href="{{ route('student.homework.show', $sub->id) }}"
               class="inline-flex items-center gap-2 text-sm font-medium
               {{ $sub->status === 'pending' ? 'bg-indigo-500 hover:bg-indigo-600 text-white shadow-sm' : 'border border-slate-200 text-slate-600 hover:bg-slate-50' }}
               px-4 py-2 rounded-xl transition">
                <i class="fa fa-{{ $sub->status === 'pending' ? 'upload' : 'eye' }} text-xs"></i>
                {{ $sub->status === 'pending' ? 'Здати' : 'Переглянути' }}
            </a>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
        <i class="fa fa-pen-to-square text-3xl text-slate-200 mb-3 block"></i>
        <p class="text-slate-400 text-sm">Домашніх завдань ще не призначено</p>
    </div>
    @endforelse
</div>
@endsection
