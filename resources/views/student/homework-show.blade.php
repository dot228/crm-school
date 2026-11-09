@extends('layouts.app')
@section('title', 'Здати завдання')
@section('header', 'Домашні завдання')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('student.homework') }}" class="text-slate-400 hover:text-slate-600 transition">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-lg font-semibold text-slate-800">{{ $submission->lesson->title ?? '—' }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ $submission->lesson->course->title ?? '' }}</p>
        </div>
    </div>

    {{-- Assignment block --}}
    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 mb-5">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-indigo-700">
                <i class="fa fa-list-check mr-1"></i>Ваше завдання
            </p>
            @if($submission->assigned_at)
            <span class="text-xs text-indigo-400">Видано {{ $submission->assigned_at->format('d.m.Y') }}</span>
            @endif
        </div>
        @if($submission->assignment_text)
        <p class="text-sm text-indigo-900 whitespace-pre-wrap">{{ $submission->assignment_text }}</p>
        @endif
        @if($submission->assignment_file_path)
        <div class="mt-3 pt-3 border-t border-indigo-200 flex items-center gap-2">
            <i class="fa fa-paperclip text-indigo-400 flex-shrink-0"></i>
            <a href="{{ Storage::url($submission->assignment_file_path) }}" target="_blank"
               class="text-sm text-indigo-700 hover:text-indigo-900 hover:underline font-medium truncate">
                {{ basename($submission->assignment_file_path) }}
            </a>
            <span class="text-xs text-indigo-400 flex-shrink-0">Файл від викладача</span>
        </div>
        @endif
    </div>

    {{-- Teacher feedback --}}
    @if($submission->status === 'reviewed')
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 mb-5">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-semibold text-emerald-800">
                <i class="fa fa-circle-check mr-1.5"></i>Перевірено
            </p>
            @if($submission->grade)
            <span class="text-2xl font-bold text-indigo-600">{{ $submission->grade }}</span>
            @endif
        </div>
        @if($submission->teacher_comment)
        <p class="text-sm text-emerald-700 italic">{{ $submission->teacher_comment }}</p>
        @endif
        <p class="text-xs text-emerald-500 mt-2">{{ $submission->reviewed_at?->format('d.m.Y H:i') }}</p>
    </div>
    @endif

    {{-- Submission form --}}
    @if($submission->status !== 'reviewed')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">
            {{ $submission->status === 'submitted' ? 'Оновити відповідь' : 'Здати домашнє завдання' }}
        </h3>

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        <form method="POST"
              action="{{ route('student.homework.submit', $submission->id) }}"
              enctype="multipart/form-data">
            @csrf

            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2">Ваша відповідь</label>
                <textarea name="content" rows="6"
                          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                          placeholder="Напишіть відповідь або пояснення...">{{ old('content', $submission->content) }}</textarea>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Прикріпити файл
                    <span class="text-xs text-slate-400 font-normal ml-1">PDF, DOC, DOCX, TXT, ZIP, PNG, JPG — до 10 МБ</span>
                </label>

                @if($submission->file_path)
                <div class="mb-2 p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2 text-sm">
                    <i class="fa fa-paperclip text-slate-400"></i>
                    <a href="{{ asset('storage/'.$submission->file_path) }}" target="_blank"
                       class="text-indigo-600 hover:underline truncate">{{ basename($submission->file_path) }}</a>
                    <span class="text-slate-400 text-xs ml-auto">Поточний файл</span>
                </div>
                @endif

                <label class="flex items-center justify-center gap-2 border-2 border-dashed border-slate-200
                              hover:border-indigo-300 rounded-xl p-5 cursor-pointer transition group">
                    <i class="fa fa-cloud-arrow-up text-xl text-slate-300 group-hover:text-indigo-400 transition"></i>
                    <span class="text-sm text-slate-400 group-hover:text-indigo-500 transition">
                        Натисніть або перетягніть файл
                    </span>
                    <input type="file" name="file" class="sr-only"
                           onchange="document.getElementById('fname').textContent = this.files[0]?.name ?? ''">
                </label>
                <p id="fname" class="text-xs text-indigo-600 mt-1 text-center"></p>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition shadow-sm">
                    <i class="fa fa-upload mr-1.5 text-xs"></i>
                    {{ $submission->status === 'submitted' ? 'Оновити' : 'Здати завдання' }}
                </button>
                <a href="{{ route('student.homework') }}"
                   class="px-5 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Назад</a>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
