@extends('layouts.app')
@section('title', 'Зарахування')
@section('header', 'Зарахування')

@section('content')
<div class="flex items-start sm:items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Зарахування студентів</h2>
        <p class="text-sm text-slate-500 mt-0.5 hidden sm:block">Управління записами студентів у групи</p>
    </div>
    <a href="{{ route('enrollments.create') }}" class="flex-shrink-0 inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
        <i class="fa fa-plus text-xs"></i><span class="hidden sm:inline">Нове зарахування</span>
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Статус</label>
            <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі</option>
                @foreach(\App\Models\Enrollment::STATUSES as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-600 transition">
            <i class="fa fa-search mr-1"></i> Фільтр
        </button>
        <a href="{{ route('enrollments.index') }}" class="text-sm text-slate-500 hover:text-slate-700 px-3 py-2">Скинути</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[620px]">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Студент</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Курс / Група</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Дата</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Оплачено</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Статус</th>
                <th class="px-4 py-3.5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($enrollments as $enrollment)
            <tr class="hover:bg-slate-50 transition">
                <td class="px-6 py-3.5">
                    <div class="flex items-center gap-3">
                        <img src="{{ $enrollment->student->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                        <div>
                            <a href="{{ route('enrollments.show', $enrollment) }}">
                                <p class="font-medium text-slate-800">{{ $enrollment->student->name }}</p>
                            </a>
                            <p class="text-xs text-slate-400">{{ $enrollment->student->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3.5">
                    <p class="text-slate-700">{{ $enrollment->group->course->title ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ $enrollment->group->name }}</p>
                </td>
                <td class="px-4 py-3.5 text-slate-500 text-xs">{{ $enrollment->enrolled_at->format('d.m.Y') }}</td>
                <td class="px-4 py-3.5">
                    <span class="font-medium text-emerald-600">{{ number_format($enrollment->total_paid, 0) }} ₴</span>
                </td>
                <td class="px-4 py-3.5">
                    @php $sc = ['active'=>'bg-emerald-100 text-emerald-700','completed'=>'bg-blue-100 text-blue-700','cancelled'=>'bg-red-100 text-red-600','suspended'=>'bg-orange-100 text-orange-600']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$enrollment->status] ?? '' }}">{{ ucfirst($enrollment->status) }}</span>
                </td>
                <td class="px-4 py-3.5">
                    <div class="flex items-center gap-2 justify-end">
                        <a href="{{ route('enrollments.edit', $enrollment) }}" class="text-slate-400 hover:text-amber-500 transition"><i class="fa fa-pen"></i></a>
                        <form method="POST" action="{{ route('enrollments.destroy', $enrollment) }}" onsubmit="return confirm('Видалити зарахування?')">
                            @csrf @method('DELETE')
                            <button class="text-slate-400 hover:text-red-500 transition"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-slate-400 py-12">Зарахувань не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($enrollments->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">{{ $enrollments->withQueryString()->links() }}</div>
    @endif
    </div>
</div>
@endsection
