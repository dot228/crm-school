@extends('layouts.app')
@section('title', 'Групи')
@section('header', 'Групи')

@section('content')
    <div class="flex items-start sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Навчальні групи</h2>
            <p class="text-sm text-slate-500 mt-0.5 hidden sm:block">Управління групами студентів</p>
        </div>
        <a href="{{ route('groups.create') }}"
           class="flex-shrink-0 inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
            <i class="fa fa-plus text-xs"></i><span class="hidden sm:inline">Нова група</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Статус</label>
                <select name="status"
                        class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">Всі</option>
                    @foreach(\App\Models\Group::STATUSES as $s)
                        <option
                            value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Пошук</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Назва групи..."
                       class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-full sm:w-52 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <button type="submit"
                    class="bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-600 transition">
                <i class="fa fa-search mr-1"></i> Пошук
            </button>
            <a href="{{ route('groups.index') }}"
               class="text-sm text-slate-500 hover:text-slate-700 px-3 py-2">Скинути</a>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Назва
                        групи
                    </th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        Курс
                    </th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        Викладач
                    </th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        Студенти
                    </th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        Дати
                    </th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        Статус
                    </th>
                    <th class="px-4 py-3.5"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                @forelse($groups as $group)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-3.5 font-medium text-slate-800">
                            <a href="{{ route('groups.show', $group) }}">{{ $group->name }}</a>
                        </td>
                        <td class="px-4 py-3.5 text-slate-600">{{ $group->course->title ?? '—' }}</td>
                        <td class="px-4 py-3.5">
                            @if($group->teacher)
                                <div class="flex items-center gap-2">
                                    <img src="{{ $group->teacher->avatar_url }}"
                                         class="w-6 h-6 rounded-full object-cover" alt="">
                                    <span class="text-slate-600">{{ $group->teacher->name }}</span>
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1.5">
                                <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full"
                                         style="width: {{ $group->max_students > 0 ? min(100, ($group->students_count / $group->max_students) * 100) : 0 }}%"></div>
                                </div>
                                <span
                                    class="text-xs text-slate-500">{{ $group->students_count }}/{{ $group->max_students }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-slate-400">
                            {{ $group->start_date?->format('d.m.Y') ?? '—' }}
                            → {{ $group->end_date?->format('d.m.Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3.5">
                            @php $sc = ['active'=>'bg-emerald-100 text-emerald-700','completed'=>'bg-blue-100 text-blue-700','cancelled'=>'bg-red-100 text-red-600']; @endphp
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$group->status] ?? '' }}">{{ $group->status }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('groups.edit', $group) }}"
                                   class="text-slate-400 hover:text-amber-500 transition"><i class="fa fa-pen"></i></a>
                                <form method="POST" action="{{ route('groups.destroy', $group) }}"
                                      onsubmit="return confirm('Видалити групу?')">
                                    @csrf @method('DELETE')
                                    <button class="text-slate-400 hover:text-red-500 transition"><i
                                            class="fa fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-slate-400 py-12">Груп не знайдено</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            @if($groups->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">{{ $groups->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
@endsection
