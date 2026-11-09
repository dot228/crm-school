@extends('layouts.app')
@section('title', 'Уроки')
@section('header', 'Уроки')

@section('content')
<div class="flex items-start sm:items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Розклад уроків</h2>
        <p class="text-sm text-slate-500 mt-0.5 hidden sm:block">Управління уроками та розкладом</p>
    </div>
    <a href="{{ route('lessons.create') }}" class="flex-shrink-0 inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
        <i class="fa fa-plus text-xs"></i><span class="hidden sm:inline">Новий урок</span>
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Курс</label>
            <select name="course_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі курси</option>
                @foreach($courses as $course)
                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Статус</label>
            <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі</option>
                @foreach(\App\Models\Lesson::STATUSES as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-600 transition">
            <i class="fa fa-search mr-1"></i> Фільтр
        </button>
        <a href="{{ route('lessons.index') }}" class="text-sm text-slate-500 hover:text-slate-700 px-3 py-2">Скинути</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[680px]">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">#</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Урок</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Курс / Група</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Дата</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Тривалість</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Статус</th>
                <th class="px-4 py-3.5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($lessons as $lesson)
            <tr class="hover:bg-slate-50 transition">
                <td class="px-6 py-3.5 text-slate-400 text-xs">{{ $lesson->order }}</td>
                <td class="px-4 py-3.5">
                    <a href="{{ route('lessons.show', $lesson) }}">
                        <p class="font-medium text-slate-800">{{ $lesson->title }}</p>
                    </a>
                    @if($lesson->zoom_link)
                    <a href="{{ $lesson->zoom_link }}" target="_blank" class="text-xs text-indigo-500 hover:underline"><i class="fa fa-video mr-1"></i>Zoom</a>
                    @endif
                </td>
                <td class="px-4 py-3.5">
                    <p class="text-slate-700">{{ $lesson->course->title ?? '—' }}</p>
                    @if($lesson->group)<p class="text-xs text-slate-400">{{ $lesson->group->name }}</p>@endif
                </td>
                <td class="px-4 py-3.5 text-slate-500 text-xs">
                    {{ $lesson->scheduled_at?->format('d.m.Y H:i') ?? '—' }}
                </td>
                <td class="px-4 py-3.5 text-slate-500 text-xs">{{ $lesson->duration_minutes }} хв</td>
                <td class="px-4 py-3.5">
                    @php $sc = ['scheduled'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-600']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$lesson->status] ?? '' }}">{{ ucfirst($lesson->status) }}</span>
                </td>
                <td class="px-4 py-3.5">
                    <div class="flex items-center gap-2 justify-end">
                        <a href="{{ route('lessons.edit', $lesson) }}" class="text-slate-400 hover:text-amber-500 transition"><i class="fa fa-pen"></i></a>
                        <form method="POST" action="{{ route('lessons.destroy', $lesson) }}" onsubmit="return confirm('Видалити урок?')">
                            @csrf @method('DELETE')
                            <button class="text-slate-400 hover:text-red-500 transition"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-slate-400 py-12">Уроків не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($lessons->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">{{ $lessons->withQueryString()->links() }}</div>
    @endif
    </div>
</div>
@endsection
