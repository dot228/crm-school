@extends('layouts.app')
@section('title', 'Домашні завдання')
@section('header', 'Домашні завдання')

@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-slate-800">Перевірка домашніх завдань</h2>
        <span class="text-sm text-slate-400">{{ $submissions->total() }} записів</span>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-5">
        <div class="flex flex-col sm:flex-row gap-2">
            <select name="status" class="w-full sm:w-auto border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі статуси</option>
                <option value="submitted" {{ request('status')==='submitted' ? 'selected':'' }}>Очікує перевірки</option>
                <option value="reviewed"  {{ request('status')==='reviewed'  ? 'selected':'' }}>Перевірено</option>
                <option value="pending"   {{ request('status')==='pending'   ? 'selected':'' }}>Не здано</option>
            </select>
            <select name="lesson_id" class="w-full sm:flex-1 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі уроки</option>
                @foreach($lessons as $lesson)
                <option value="{{ $lesson->id }}" {{ request('lesson_id')==$lesson->id ? 'selected':'' }}>
                    {{ $lesson->course->title ?? '' }} — {{ $lesson->title }}
                </option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="flex-1 sm:flex-none bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm transition">Фільтр</button>
                <a href="{{ route('teacher.homework.index') }}" class="flex-1 sm:flex-none text-center text-slate-400 hover:text-slate-600 text-sm px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 transition">Скинути</a>
            </div>
        </div>
    </form>


    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[580px]">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-5 py-3.5 font-medium text-slate-500">Учень</th>
                    <th class="text-left px-5 py-3.5 font-medium text-slate-500">Урок</th>
                    <th class="text-left px-5 py-3.5 font-medium text-slate-500">Здано</th>
                    <th class="text-left px-5 py-3.5 font-medium text-slate-500">Статус</th>
                    <th class="text-left px-5 py-3.5 font-medium text-slate-500">Оцінка</th>
                    <th class="text-right px-5 py-3.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $hw)
                @php $psc=['pending'=>'bg-slate-100 text-slate-500','submitted'=>'bg-amber-100 text-amber-700','reviewed'=>'bg-emerald-100 text-emerald-700']; @endphp
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition {{ $hw->status==='submitted' ? 'bg-amber-50/30' : '' }}">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <img src="{{ $hw->student->avatar_url }}" class="w-7 h-7 rounded-lg object-cover" alt="">
                            <span class="font-medium text-slate-800">{{ $hw->student->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-slate-600">
                        <p>{{ $hw->lesson->title ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $hw->lesson->course->title ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-slate-400 text-xs">
                        {{ $hw->submitted_at ? $hw->submitted_at->format('d.m.Y H:i') : '—' }}
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $psc[$hw->status] ?? '' }}">
                            {{ ['pending'=>'Не здано','submitted'=>'Очікує','reviewed'=>'Перевірено'][$hw->status] ?? $hw->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        @if($hw->grade)
                        <span class="text-lg font-bold text-indigo-600">{{ $hw->grade }}</span>
                        @else
                        <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('teacher.homework.show', $hw->id) }}"
                           class="text-sm {{ $hw->status==='submitted' ? 'text-amber-600 hover:text-amber-700 font-medium' : 'text-slate-400 hover:text-indigo-500' }} transition">
                            {{ $hw->status==='submitted' ? 'Перевірити' : 'Переглянути' }}
                            <i class="fa fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-slate-400 py-12">Домашніх завдань не знайдено</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($submissions->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $submissions->withQueryString()->links() }}</div>
        @endif
        </div>
    </div>
</div>
@endsection
