@extends('layouts.app')
@section('title', 'Перевірка Д/З')
@section('header', 'Домашні завдання')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('teacher.homework.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">Перевірка домашнього завдання</h2>
    </div>

    {{-- Submission info --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <div class="flex items-start gap-4 mb-5">
            <img src="{{ $homeworkSubmission->student->avatar_url }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0" alt="">
            <div class="flex-1">
                <p class="font-semibold text-slate-800">{{ $homeworkSubmission->student->name }}</p>
                <p class="text-sm text-slate-500">{{ $homeworkSubmission->lesson->course->title ?? '' }} — {{ $homeworkSubmission->lesson->title ?? '—' }}</p>
                <p class="text-xs text-slate-400 mt-0.5">
                    Здано: {{ $homeworkSubmission->submitted_at ? $homeworkSubmission->submitted_at->format('d.m.Y H:i') : 'Не здано' }}
                </p>
            </div>
            @php $psc=['pending'=>'bg-slate-100 text-slate-500','submitted'=>'bg-amber-100 text-amber-700','reviewed'=>'bg-emerald-100 text-emerald-700']; @endphp
            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $psc[$homeworkSubmission->status] ?? '' }}">
                {{ ['pending'=>'Не здано','submitted'=>'Очікує перевірки','reviewed'=>'Перевірено'][$homeworkSubmission->status] ?? $homeworkSubmission->status }}
            </span>
        </div>

        @if($homeworkSubmission->lesson->homework)
        <div class="mb-5 p-4 bg-indigo-50 border border-indigo-100 rounded-xl">
            <p class="text-xs font-semibold text-indigo-700 mb-1.5"><i class="fa fa-list-check mr-1"></i>Завдання:</p>
            <p class="text-sm text-indigo-800 whitespace-pre-wrap">{{ $homeworkSubmission->lesson->homework }}</p>
        </div>
        @endif

        @if($homeworkSubmission->content)
        <div class="mb-4">
            <p class="text-xs font-semibold text-slate-600 mb-2">Відповідь учня:</p>
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 whitespace-pre-wrap">{{ $homeworkSubmission->content }}</div>
        </div>
        @endif

        @if($homeworkSubmission->file_path)
        <div class="mb-4">
            <p class="text-xs font-semibold text-slate-600 mb-2">Прикріплений файл:</p>
            <a href="{{ asset('storage/'.$homeworkSubmission->file_path) }}" target="_blank"
               class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:underline">
                <i class="fa fa-paperclip text-xs"></i>
                {{ basename($homeworkSubmission->file_path) }}
            </a>
        </div>
        @endif

        @if(!$homeworkSubmission->content && !$homeworkSubmission->file_path && $homeworkSubmission->status === 'pending')
        <div class="p-4 bg-slate-50 rounded-xl text-sm text-slate-400 text-center">
            Учень ще не здав домашнє завдання
        </div>
        @endif
    </div>

    {{-- Review form --}}
    @if($homeworkSubmission->status !== 'pending')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">
            {{ $homeworkSubmission->status === 'reviewed' ? 'Оцінка та коментар' : 'Оцінити роботу' }}
        </h3>

        <form method="POST" action="{{ route('teacher.homework.review', $homeworkSubmission->id) }}"
              enctype="multipart/form-data">
            @csrf

            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2">Оцінка</label>
                <div class="flex gap-2 flex-wrap">
                    @foreach(\App\Models\HomeworkSubmission::GRADES as $grade)
                    <label class="cursor-pointer">
                        <input type="radio" name="grade" value="{{ $grade }}" class="sr-only peer"
                               {{ old('grade', $homeworkSubmission->grade) === $grade ? 'checked' : '' }}>
                        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl border-2 text-sm font-bold transition
                            peer-checked:bg-indigo-500 peer-checked:border-indigo-500 peer-checked:text-white
                            border-slate-200 text-slate-500 hover:border-indigo-300">
                            {{ $grade }}
                        </span>
                    </label>
                    @endforeach
                    <label class="cursor-pointer">
                        <input type="radio" name="grade" value="" class="sr-only peer"
                               {{ old('grade', $homeworkSubmission->grade) === null ? 'checked' : '' }}>
                        <span class="inline-flex items-center justify-center px-3 h-11 rounded-xl border-2 text-sm font-medium transition
                            peer-checked:bg-slate-200 peer-checked:border-slate-300
                            border-slate-200 text-slate-400 hover:border-slate-300">
                            Без оцінки
                        </span>
                    </label>
                </div>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2">Коментар для учня</label>
                <textarea name="teacher_comment" rows="4"
                          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                          placeholder="Напишіть відгук або рекомендації...">{{ old('teacher_comment', $homeworkSubmission->teacher_comment) }}</textarea>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2">Прикріпити файл <span class="text-xs text-slate-400">(необов’язково)</span></label>
                @if($homeworkSubmission->teacher_file_path)
                <div class="flex items-center gap-3 mb-2 p-2 bg-indigo-50 rounded-lg">
                    <i class="fa fa-paperclip text-indigo-400"></i>
                    <a href="{{ asset('storage/'.$homeworkSubmission->teacher_file_path) }}" target="_blank"
                       class="text-sm text-indigo-600 hover:underline flex-1 truncate">
                        {{ basename($homeworkSubmission->teacher_file_path) }}
                    </a>
                </div>
                @endif
                <input type="file" name="teacher_file" accept=".pdf,.doc,.docx,.txt,.zip,.png,.jpg,.jpeg"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:text-xs">
                <p class="text-xs text-slate-400 mt-1">PDF, DOC, TXT, ZIP, PNG, JPG — макс 10МБ</p>
            </div>

            @if($homeworkSubmission->reviewed_at)
            <p class="text-xs text-slate-400 mb-4">
                Перевірено: {{ $homeworkSubmission->reviewed_at->format('d.m.Y H:i') }}
            </p>
            @endif

            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition">
                    {{ $homeworkSubmission->status === 'reviewed' ? 'Оновити оцінку' : 'Підтвердити перевірку' }}
                </button>
                <a href="{{ route('teacher.homework.index') }}" class="px-6 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Назад</a>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
