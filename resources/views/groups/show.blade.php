@extends('layouts.app')
@section('title', $group->name)
@section('header', 'Групи')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('groups.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">{{ $group->name }}</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <div class="flex items-start justify-between gap-4 mb-5">
            <div>
                <h3 class="text-xl font-bold text-slate-800">{{ $group->name }}</h3>
                <p class="text-slate-500 text-sm mt-1">{{ $group->course->title ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-2">
                @php $sc = ['active'=>'bg-emerald-100 text-emerald-700','completed'=>'bg-blue-100 text-blue-700','cancelled'=>'bg-red-100 text-red-600']; @endphp
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $sc[$group->status] ?? '' }}">{{ ucfirst($group->status) }}</span>
                <a href="{{ route('groups.edit', $group) }}" class="inline-flex items-center gap-2 border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition">
                    <i class="fa fa-pen text-xs"></i> Редагувати
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4 border-y border-slate-100 mb-5">
            <div>
                <p class="text-xs text-slate-400 mb-1">Студенти</p>
                <p class="text-lg font-bold text-slate-800">{{ $group->students_count }} / {{ $group->max_students }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-1">Початок</p>
                <p class="text-sm font-medium text-slate-700">{{ $group->start_date?->format('d.m.Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-1">Закінчення</p>
                <p class="text-sm font-medium text-slate-700">{{ $group->end_date?->format('d.m.Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-1">Викладач</p>
                @if($group->teacher)
                <div class="flex items-center gap-1.5">
                    <img src="{{ $group->teacher->avatar_url }}" class="w-5 h-5 rounded-full object-cover" alt="">
                    <span class="text-sm text-slate-700">{{ $group->teacher->name }}</span>
                </div>
                @else
                <p class="text-sm text-slate-400">—</p>
                @endif
            </div>
        </div>

        @if($group->notes)
        <p class="text-sm text-slate-500 bg-slate-50 rounded-xl p-3">{{ $group->notes }}</p>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-sm font-semibold text-slate-700">Студенти групи ({{ $group->students_count }})</h4>
            <a href="{{ route('enrollments.create') }}" class="text-xs text-indigo-500 hover:underline">+ Додати студента</a>
        </div>
        @forelse($group->enrollments()->with('student')->get() as $enrollment)
        <div class="flex items-center justify-between py-3 border-b border-slate-50 last:border-0">
            <div class="flex items-center gap-3">
                <img src="{{ $enrollment->student->avatar_url }}" class="w-8 h-8 rounded-full object-cover" alt="">
                <div>
                    <a href="{{ route('enrollments.show', $enrollment) }}">
                        <p class="text-sm font-medium text-slate-800">{{ $enrollment->student->name }}</p>
                    </a>
                    <p class="text-xs text-slate-400">{{ $enrollment->student->email }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @php $sc = ['active'=>'bg-emerald-100 text-emerald-700','completed'=>'bg-blue-100 text-blue-700','cancelled'=>'bg-red-100 text-red-600','suspended'=>'bg-orange-100 text-orange-600']; @endphp
                <span class="px-2 py-0.5 rounded-full text-xs {{ $sc[$enrollment->status] ?? '' }}">{{ $enrollment->status }}</span>
                <span class="text-xs text-slate-400">{{ $enrollment->enrolled_at->format('d.m.Y') }}</span>
            </div>
        </div>
        @empty
        <p class="text-sm text-slate-400">Немає студентів у цій групі</p>
        @endforelse
    </div>
</div>
@endsection
