@extends('layouts.app')
@section('title', $course->title)
@section('header', 'Курси')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('courses.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">{{ $course->title }}</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-5">
        <div class="h-48 bg-gradient-to-br from-indigo-500 to-violet-600 relative">
            @if($course->image)
            <img src="{{ $course->image_url }}" class="w-full h-full object-cover" alt="">
            @else
            <div class="absolute inset-0 flex items-center justify-center">
                <i class="fa fa-book text-white/20 text-7xl"></i>
            </div>
            @endif
        </div>
        <div class="p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-2xl font-bold text-slate-800">{{ $course->title }}</h3>
                    @if($course->description)<p class="text-slate-500 mt-2">{{ $course->description }}</p>@endif
                </div>
                <a href="{{ route('courses.edit', $course) }}" class="flex-shrink-0 inline-flex items-center gap-2 border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition">
                    <i class="fa fa-pen text-xs"></i> Редагувати
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4 border-t border-slate-100">
                <div class="text-center">
                    <p class="text-2xl font-bold text-indigo-600">{{ number_format($course->price, 0) }} ₴</p>
                    <p class="text-xs text-slate-400 mt-1">Ціна</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-slate-800">{{ $course->duration_hours }}</p>
                    <p class="text-xs text-slate-400 mt-1">Годин</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-slate-800">{{ $course->groups->count() }}</p>
                    <p class="text-xs text-slate-400 mt-1">Груп</p>
                </div>
                <div class="text-center">
                    @php $lc = ['beginner'=>'text-emerald-600','intermediate'=>'text-amber-600','advanced'=>'text-red-600']; @endphp
                    <p class="text-2xl font-bold {{ $lc[$course->level] ?? '' }}">{{ ucfirst($course->level) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Рівень</p>
                </div>
            </div>

            @if($course->teacher)
            <div class="flex items-center gap-3 mt-4 p-4 bg-slate-50 rounded-xl">
                <img src="{{ $course->teacher->avatar_url }}" class="w-10 h-10 rounded-full object-cover" alt="">
                <div>
                    <p class="text-sm font-medium text-slate-800">{{ $course->teacher->name }}</p>
                    <p class="text-xs text-slate-400">Викладач курсу</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-sm font-semibold text-slate-700">Групи курсу</h4>
            <a href="{{ route('groups.create') }}" class="text-xs text-indigo-500 hover:underline">+ Додати групу</a>
        </div>
        @forelse($course->groups()->with('teacher')->get() as $group)
        <div class="flex items-center justify-between py-3 border-b border-slate-50 last:border-0">
            <div>
                <a href="{{ route('groups.show', $group) }}">
                    <p class="text-sm font-medium text-slate-800">{{ $group->name }}</p>
                </a>
                <p class="text-xs text-slate-400">
                    {{ $group->teacher->name ?? 'Без викладача' }} ·
                    {{ $group->start_date?->format('d.m.Y') ?? '?' }} — {{ $group->end_date?->format('d.m.Y') ?? '?' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500">{{ $group->students_count }} / {{ $group->max_students }}</span>
                @php $sc = ['active'=>'bg-emerald-100 text-emerald-700','completed'=>'bg-blue-100 text-blue-700','cancelled'=>'bg-red-100 text-red-600']; @endphp
                <span class="px-2 py-0.5 rounded-full text-xs {{ $sc[$group->status] ?? '' }}">{{ $group->status }}</span>
            </div>
        </div>
        @empty
        <p class="text-sm text-slate-400">Немає груп для цього курсу</p>
        @endforelse
    </div>
</div>
@endsection
