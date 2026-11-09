@extends('layouts.app')
@section('title', 'Новий запис у розкладі')
@section('header', 'Розклад')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('schedules.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">Новий запис у розкладі</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('schedules.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Урок <span class="text-red-500">*</span></label>
                <select name="lesson_id" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">— Оберіть урок —</option>
                    @foreach($lessons as $lesson)
                    <option value="{{ $lesson->id }}" {{ old('lesson_id') == $lesson->id ? 'selected' : '' }}>
                        {{ $lesson->course->title ?? '?' }} — {{ $lesson->title }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Студент <span class="text-red-500">*</span></label>
                    <select name="student_id" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Оберіть студента —</option>
                        @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>{{ $student->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Викладач <span class="text-red-500">*</span></label>
                    <select name="teacher_id" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Оберіть викладача —</option>
                        @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Дата <span class="text-red-500">*</span></label>
                    <input type="date" name="scheduled_date" value="{{ old('scheduled_date') }}" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Час <span class="text-red-500">*</span></label>
                    <input type="time" name="scheduled_time" value="{{ old('scheduled_time') }}" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Тривалість (хв)</label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="15" max="480"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Статус</label>
                    <select name="status" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\Schedule::STATUSES as $s)
                        <option value="{{ $s }}" {{ old('status','scheduled') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!auth()->user()->isTeacher())
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Заміна викладача
                        <span class="text-xs text-slate-400 font-normal ml-1">(якщо основний відсутній)</span>
                    </label>
                    <select name="substitute_teacher_id" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Без заміни —</option>
                        @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('substitute_teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            {{-- Absence (shown when status = no_show) --}}
            <div id="absence-section" class="hidden border border-amber-200 bg-amber-50 rounded-xl p-4 space-y-3">
                <p class="text-xs font-semibold text-amber-700"><i class="fa fa-triangle-exclamation mr-1"></i>Причина відсутності</p>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Тип відсутності</label>
                    <select name="absence_type" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Оберіть —</option>
                        <option value="excused" {{ old('absence_type')==='excused' ? 'selected' : '' }}>Поважна причина (урок не списується)</option>
                        <option value="unexcused" {{ old('absence_type')==='unexcused' ? 'selected' : '' }}>Без поважної причини (урок списується)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Пояснення</label>
                    <textarea name="absence_reason" rows="2"
                              class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="Опишіть причину відсутності…">{{ old('absence_reason') }}</textarea>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Zoom посилання</label>
                <input type="url" name="zoom_link" value="{{ old('zoom_link') }}" placeholder="https://zoom.us/j/..."
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                @if($zoomConfigured)
                <label class="flex items-center gap-2 mt-2 cursor-pointer">
                    <input type="checkbox" name="generate_zoom" value="1" {{ old('generate_zoom', '1') ? 'checked' : '' }}
                           class="rounded border-slate-300 text-indigo-500">
                    <span class="text-xs text-slate-600">Згенерувати Zoom посилання автоматично</span>
                </label>
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Примітки</label>
                <textarea name="notes" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition">Зберегти</button>
                <a href="{{ route('schedules.index') }}" class="px-6 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Скасувати</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.querySelector('select[name="status"]');
    const sec = document.getElementById('absence-section');
    function toggle() { sec.classList.toggle('hidden', sel.value !== 'no_show'); }
    sel?.addEventListener('change', toggle);
    toggle();
});
</script>
@endpush
@endsection
