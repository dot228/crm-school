@extends('layouts.app')
@section('title', 'Видати домашнє завдання')
@section('header', 'Мої курси')

@section('content')
<div class="max-w-3xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('teacher.lessons') }}" class="text-slate-400 hover:text-slate-600 transition">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Домашнє завдання</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ $lesson->course->title ?? '' }} — {{ $lesson->title }}</p>
        </div>
    </div>


    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    @if($students->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center">
        <i class="fa fa-users text-3xl text-slate-200 mb-3 block"></i>
        <p class="text-sm text-slate-400">У цьому курсі ще немає зарахованих учнів</p>
    </div>
    @else

    {{-- Quick-fill bar --}}
    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 mb-4 flex items-start gap-3">
        <div class="flex-1">
            <p class="text-xs font-semibold text-indigo-700 mb-1.5">
                <i class="fa fa-bolt mr-1"></i>Швидке заповнення — один текст для всіх
            </p>
            <textarea id="quick-fill" rows="2"
                      class="w-full border border-indigo-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white"
                      placeholder="Введіть текст і натисніть «Застосувати до всіх»…"></textarea>
        </div>
        <button type="button" onclick="applyToAll()"
                class="mt-5 flex-shrink-0 bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
            Застосувати до всіх
        </button>
    </div>

    <form method="POST" action="{{ route('teacher.lessons.homework.update', $lesson) }}" enctype="multipart/form-data">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 divide-y divide-slate-50 mb-5">
            @foreach($students as $i => $student)
            @php $sub = $existing->get($student->id); @endphp
            <div class="p-5">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <img src="{{ $student->avatar_url }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0">
                        <span class="text-sm font-medium text-slate-800">{{ $student->name }}</span>
                        @if($sub)
                        @php $sc=['pending'=>'bg-slate-100 text-slate-500','submitted'=>'bg-amber-100 text-amber-700','reviewed'=>'bg-emerald-100 text-emerald-700'];
                             $sl=['pending'=>'Не здав','submitted'=>'На перевірці','reviewed'=>'Перевірено']; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$sub->status] ?? '' }}">
                            {{ $sl[$sub->status] ?? $sub->status }}
                        </span>
                        @endif
                    </div>
                    @if($sub?->assigned_at)
                    <span class="text-xs text-slate-400">Видано: {{ $sub->assigned_at->format('d.m H:i') }}</span>
                    @endif
                </div>

                <input type="hidden" name="assignments[{{ $i }}][student_id]" value="{{ $student->id }}">
                <textarea name="assignments[{{ $i }}][text]"
                          rows="3"
                          class="student-textarea w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-y mb-2"
                          placeholder="Текст завдання для {{ $student->name }}… (залишіть порожнім, щоб не видавати)">{{ old("assignments.{$i}.text", $sub?->assignment_text) }}</textarea>

                {{-- File attachment --}}
                <div class="flex items-center gap-3 mt-1">
                    <label class="flex-1 flex items-center gap-2 cursor-pointer border border-dashed border-slate-300 hover:border-indigo-400 rounded-xl px-3 py-2 transition group">
                        <i class="fa fa-paperclip text-slate-400 group-hover:text-indigo-500 transition text-sm"></i>
                        <span class="text-xs text-slate-500 group-hover:text-indigo-600 transition">Прикріпити файл (PDF, DOC, ZIP, зображення)</span>
                        <input type="file" name="assignments[{{ $i }}][file]"
                               class="sr-only"
                               accept=".pdf,.doc,.docx,.txt,.zip,.png,.jpg,.jpeg"
                               onchange="this.parentElement.querySelector('span').textContent = this.files[0]?.name || 'Прикріпити файл'">
                    </label>
                    @if($sub?->assignment_file_path)
                    <a href="{{ Storage::url($sub->assignment_file_path) }}" target="_blank"
                       class="flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 rounded-xl px-3 py-2 transition flex-shrink-0">
                        <i class="fa fa-file text-xs"></i> Поточний файл
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition shadow-sm">
                <i class="fa fa-floppy-disk mr-1.5 text-xs"></i> Зберегти
            </button>
            <a href="{{ route('teacher.lessons') }}"
               class="px-5 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">
                Скасувати
            </a>
        </div>
    </form>
    @endif
</div>

@push('scripts')
<script>
function applyToAll() {
    const text = document.getElementById('quick-fill').value;
    document.querySelectorAll('.student-textarea').forEach(ta => ta.value = text);
}
</script>
@endpush
@endsection
