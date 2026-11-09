@extends('layouts.app')
@section('title', 'Мої курси та уроки')
@section('header', 'Мої курси')

@section('content')
<div class="max-w-4xl">
    <h2 class="text-lg font-semibold text-slate-800 mb-6">Курси та уроки</h2>

    @forelse($courses as $course)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-5">
        <div class="flex items-center gap-4 px-6 py-4 border-b border-slate-100">
            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa fa-book text-indigo-500"></i>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-slate-800">{{ $course->title }}</p>
                <p class="text-xs text-slate-400">{{ $course->level_label }} · {{ $course->lessons->count() }} уроків</p>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs {{ $course->status==='active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                {{ ucfirst($course->status) }}
            </span>
        </div>

        @forelse($course->lessons as $lesson)
        <div class="flex items-start gap-4 px-6 py-3.5 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition">
            <div class="w-7 h-7 bg-slate-100 rounded-lg flex items-center justify-center text-xs font-bold text-slate-500 flex-shrink-0 mt-0.5">
                {{ $lesson->order ?? $loop->iteration }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800">{{ $lesson->title }}</p>
                @if($lesson->description)
                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $lesson->description }}</p>
                @endif
                @if($lesson->homework)
                <p class="text-xs text-amber-600 mt-0.5"><i class="fa fa-pen-to-square mr-1"></i>Є домашнє завдання</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center justify-end gap-1.5 min-w-0">
                @if($lesson->contents->count())
                <span class="text-xs text-slate-400 whitespace-nowrap"><i class="fa fa-paperclip mr-0.5"></i>{{ $lesson->contents->count() }}</span>
                @endif
                @php $sc=['scheduled'=>'bg-blue-100 text-blue-600','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-600']; @endphp
                <span class="px-2 py-0.5 rounded-full text-xs whitespace-nowrap {{ $sc[$lesson->status] ?? 'bg-slate-100 text-slate-500' }}">
                    {{ ucfirst($lesson->status) }}
                </span>
                @php
                    $pendingHw = \App\Models\HomeworkSubmission::where('lesson_id', $lesson->id)
                        ->where('status', 'submitted')->count();
                @endphp
                @if($pendingHw > 0)
                <a href="{{ route('teacher.homework.index', ['lesson_id' => $lesson->id]) }}"
                   class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-medium hover:bg-amber-200 transition whitespace-nowrap">
                    <i class="fa fa-clock text-xs"></i> {{ $pendingHw }}
                </a>
                @endif
                <a href="{{ route('teacher.lessons.homework.edit', $lesson) }}"
                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition whitespace-nowrap
                   {{ $lesson->homework ? 'bg-indigo-100 text-indigo-600 hover:bg-indigo-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                    <i class="fa fa-pen text-xs"></i>
                    {{ $lesson->homework ? 'Д/З' : 'Видати Д/З' }}
                </a>
            </div>
        </div>
        @empty
        <div class="px-6 py-4 text-sm text-slate-400 text-center">Уроків ще немає</div>
        @endforelse
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
        <i class="fa fa-book text-3xl text-slate-200 mb-3 block"></i>
        <p class="text-slate-400 text-sm">Курсів ще не призначено</p>
    </div>
    @endforelse
</div>
@endsection
