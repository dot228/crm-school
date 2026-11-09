@extends('layouts.app')
@section('title', 'Запис розкладу #' . $schedule->id)
@section('header', 'Розклад')

@section('content')
@php
    $sc = ['scheduled'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700',
           'cancelled'=>'bg-red-100 text-red-600','no_show'=>'bg-orange-100 text-orange-600'];
    $canEdit = auth()->user()->role_id <= 2;
    $isOwnTeacher = in_array(auth()->id(), array_filter([$schedule->teacher_id, $schedule->substitute_teacher_id]));
    $canStatus = $schedule->status === 'scheduled' && (auth()->user()->role_id <= 2 || $isOwnTeacher);
@endphp
<div class="max-w-xl">
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('schedules.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">Заняття #{{ $schedule->id }}</h2>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
        @foreach($errors->all() as $e)<p><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</p>@endforeach
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-4">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
            <div>
                <p class="text-2xl font-bold text-slate-800">{{ $schedule->scheduled_date->format('d.m.Y') }}</p>
                <p class="text-slate-500 text-sm">{{\Illuminate\Support\Carbon::parse($schedule->scheduled_time)->format('H:i')}} · {{ $schedule->duration_minutes }} хв</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $sc[$schedule->status] ?? '' }}">
                    {{ ucfirst(str_replace('_',' ',$schedule->status)) }}
                </span>
                @if($canEdit)
                <a href="{{ route('schedules.edit', $schedule) }}" class="inline-flex items-center gap-1.5 border border-slate-200 text-slate-600 hover:bg-slate-50 px-3 py-1.5 rounded-xl text-sm transition">
                    <i class="fa fa-pen text-xs"></i> Редагувати
                </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-2.5">
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                <i class="fa fa-book text-indigo-400 w-5 text-center flex-shrink-0"></i>
                <div>
                    <p class="text-xs text-slate-400">Урок</p>
                    <p class="text-sm font-medium text-slate-700">{{ $schedule->lesson->course->title ?? '—' }} — {{ $schedule->lesson->title }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                <img src="{{ $schedule->student->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                <div>
                    <p class="text-xs text-slate-400">Студент</p>
                    <p class="text-sm font-medium text-slate-700">{{ $schedule->student->name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                @php $effTeacher = $schedule->substitute_teacher_id ? $schedule->substituteTeacher : $schedule->teacher; @endphp
                <img src="{{ $effTeacher->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                <div>
                    <p class="text-xs text-slate-400 flex items-center gap-1.5">
                        Викладач
                        @if($schedule->substitute_teacher_id)
                        <span class="bg-amber-100 text-amber-700 text-xs px-1.5 py-0.5 rounded-full">заміна</span>
                        @endif
                    </p>
                    <p class="text-sm font-medium text-slate-700">{{ $effTeacher->name }}</p>
                    @if($schedule->substitute_teacher_id)
                    <p class="text-xs text-slate-400">Основний: {{ $schedule->teacher->name }}</p>
                    @endif
                </div>
            </div>

            @if($schedule->zoom_link)
            <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="fa fa-video text-blue-500 flex-shrink-0"></i>
                        <p class="text-xs text-blue-600 truncate max-w-[200px]">{{ $schedule->zoom_link }}</p>
                    </div>
                    <a href="{{ $schedule->zoom_link }}" target="_blank"
                       class="flex-shrink-0 inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition shadow-sm">
                        <i class="fa fa-video"></i> Приєднатись до Zoom
                    </a>
                </div>
            </div>
            @endif

            @if($schedule->absence_type)
            <div class="p-3 bg-amber-50 rounded-xl border border-amber-100">
                <p class="text-xs text-amber-600 font-medium mb-1">
                    <i class="fa fa-triangle-exclamation mr-1"></i>
                    {{ $schedule->absence_type === 'excused' ? 'Поважна причина (урок не списано)' : 'Без поважної причини (урок списано)' }}
                </p>
                @if($schedule->absence_reason)
                <p class="text-xs text-slate-600">{{ $schedule->absence_reason }}</p>
                @endif
            </div>
            @endif

            @if($schedule->notes)
            <div class="p-3 bg-slate-50 rounded-xl">
                <p class="text-xs text-slate-400 mb-1">Примітки</p>
                <p class="text-sm text-slate-700">{{ $schedule->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Status update panel --}}
    @if($canStatus)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">Оновити статус уроку</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">

            {{-- Completed --}}
            <form method="POST" action="{{ route('schedules.updateStatus', $schedule) }}">
                @csrf
                <input type="hidden" name="status" value="completed">
                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition">
                    <i class="fa fa-circle-check"></i> Проведено
                </button>
            </form>

            {{-- No show with inline absence panel --}}
            <div>
                <button type="button" id="noshow-toggle"
                    class="w-full inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition">
                    <i class="fa fa-user-xmark"></i> Не з'явився
                </button>
                <div id="noshow-panel" class="hidden mt-2">
                    <form method="POST" action="{{ route('schedules.updateStatus', $schedule) }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="status" value="no_show">
                        <select name="absence_type" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-orange-300">
                            <option value="">— Тип відсутності —</option>
                            <option value="excused">Поважна (урок не списується)</option>
                            <option value="unexcused">Без поважної (урок списується)</option>
                        </select>
                        <textarea name="absence_reason" rows="2" placeholder="Причина…"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs resize-none focus:outline-none focus:ring-2 focus:ring-orange-300"></textarea>
                        <button type="submit"
                            class="w-full bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium py-2 rounded-xl transition">
                            Підтвердити
                        </button>
                    </form>
                </div>
            </div>

            {{-- Cancel --}}
            <form method="POST" action="{{ route('schedules.updateStatus', $schedule) }}"
                  onsubmit="return confirm('Скасувати урок?')">
                @csrf
                <input type="hidden" name="status" value="cancelled">
                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition">
                    <i class="fa fa-ban"></i> Скасувати
                </button>
            </form>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.getElementById('noshow-toggle')?.addEventListener('click', function () {
    document.getElementById('noshow-panel').classList.toggle('hidden');
});
</script>
@endpush
@endsection
