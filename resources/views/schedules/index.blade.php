@extends('layouts.app')
@section('title', 'Розклад занять')
@section('header', 'Розклад')

@section('content')
@php
    $viewMode  = $viewMode  ?? 'list';
    $calPeriod = $calPeriod ?? 'month';
    $calYear   = $calYear   ?? now()->year;
    $calMonth  = $calMonth  ?? now()->month;
    $calDate   = $calDate   ?? now();
    $baseParams = array_filter(request()->except(['view','cal_year','cal_month','cal_period','cal_date','page']));
@endphp

<div class="flex items-start sm:items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Розклад занять</h2>
        <p class="text-sm text-slate-500 mt-0.5 hidden sm:block">Дата та час уроків по студентах і викладачах</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap justify-end">
        {{-- List/Calendar toggle --}}
        <div class="flex border border-slate-200 rounded-xl overflow-hidden">
            <a href="{{ route('schedules.index', array_merge($baseParams, ['view'=>'list'])) }}"
               class="px-3 py-2 text-sm transition {{ $viewMode==='list' ? 'bg-indigo-500 text-white' : 'text-slate-500 hover:bg-slate-50' }}">
                <i class="fa fa-list text-xs"></i>
            </a>
            <a href="{{ route('schedules.index', array_merge($baseParams, ['view'=>'calendar','cal_period'=>'month'])) }}"
               class="px-3 py-2 text-sm transition {{ $viewMode==='calendar' ? 'bg-indigo-500 text-white' : 'text-slate-500 hover:bg-slate-50' }}">
                <i class="fa fa-calendar text-xs"></i>
            </a>
        </div>
        {{-- Period toggle (calendar only) --}}
        @if($viewMode === 'calendar')
        <div class="flex border border-slate-200 rounded-xl overflow-hidden text-xs">
            <a href="{{ route('schedules.index', array_merge($baseParams, ['view'=>'calendar','cal_period'=>'month','cal_year'=>$calYear,'cal_month'=>$calMonth])) }}"
               class="px-3 py-2 transition {{ $calPeriod==='month' ? 'bg-slate-700 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Місяць</a>
            <a href="{{ route('schedules.index', array_merge($baseParams, ['view'=>'calendar','cal_period'=>'week','cal_date'=>(is_string($calDate)?$calDate:$calDate->toDateString())])) }}"
               class="px-3 py-2 transition {{ $calPeriod==='week' ? 'bg-slate-700 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Тиждень</a>
            <a href="{{ route('schedules.index', array_merge($baseParams, ['view'=>'calendar','cal_period'=>'day','cal_date'=>(is_string($calDate)?$calDate:$calDate->toDateString())])) }}"
               class="px-3 py-2 transition {{ $calPeriod==='day' ? 'bg-slate-700 text-white' : 'text-slate-500 hover:bg-slate-50' }}">День</a>
        </div>
        @endif
        <a href="{{ route('schedules.create') }}" class="flex-shrink-0 inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
            <i class="fa fa-plus text-xs"></i><span class="hidden sm:inline">Додати запис</span>
        </a>
    </div>
</div>

{{-- Filters (list only) --}}
@if($viewMode === 'list')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
    <form method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3">
        <input type="hidden" name="view" value="list">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Дата</label>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="w-full border border-slate-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        @if($teachers->count())
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Викладач</label>
            <select name="teacher_id" class="w-full border border-slate-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі</option>
                @foreach($teachers as $t)
                <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Студент</label>
            <select name="student_id" class="w-full border border-slate-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі</option>
                @foreach($students as $s)
                <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Статус</label>
            <select name="status" class="w-full border border-slate-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі</option>
                @foreach(\App\Models\Schedule::STATUSES as $st)
                <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-2 sm:col-span-4 flex gap-2">
            <button type="submit" class="flex-1 sm:flex-none bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-600 transition">
                <i class="fa fa-search mr-1"></i> Фільтр
            </button>
            <a href="{{ route('schedules.index', ['view'=>'list']) }}" class="flex-1 sm:flex-none text-center text-sm text-slate-500 hover:text-slate-700 border border-slate-200 hover:bg-slate-50 px-3 py-2 rounded-lg transition">Скинути</a>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[780px]">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Дата / Час</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Урок</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Студент</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Викладач</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">Статус</th>
                <th class="px-4 py-3.5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($schedules as $schedule)
            @php $sc = ['draft'=>'bg-slate-100 text-slate-500','scheduled'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-600','no_show'=>'bg-orange-100 text-orange-600']; @endphp
            <tr class="hover:bg-slate-50 transition {{ $schedule->status === 'draft' ? 'opacity-70' : '' }}">
                <td class="px-6 py-3.5">
                    <p class="font-medium text-slate-800">{{ $schedule->scheduled_date->format('d.m.Y') }}</p>
                    <p class="text-xs text-slate-400">{{ \Illuminate\Support\Carbon::parse($schedule->scheduled_time)->format('H:i') }}</p>
                </td>
                <td class="px-4 py-3.5">
                    <a href="{{ route('schedules.show', $schedule) }}">
                        <p class="font-medium text-slate-700">{{ $schedule->lesson->title }}</p>
                    </a>
                    <p class="text-xs text-slate-400">{{ $schedule->lesson->course->title ?? '—' }}</p>
                </td>
                <td class="px-4 py-3.5 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <img src="{{ $schedule->student->avatar_url }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                        <span class="text-slate-700">{{ $schedule->student->name }}</span>
                    </div>
                </td>
                <td class="px-4 py-3.5 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <img src="{{ $schedule->teacher->avatar_url }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                        <span class="text-slate-700">{{ $schedule->teacher->name }}</span>
                    </div>
                </td>
                <td class="px-4 py-3.5">
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$schedule->status] ?? '' }} whitespace-nowrap">
                            {{ ucfirst(str_replace('_',' ',$schedule->status)) }}
                        </span>
                        @if($schedule->zoom_link && $schedule->status === 'scheduled')
                        <a href="{{ $schedule->zoom_link }}" target="_blank"
                           class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 hover:bg-blue-200 text-xs font-medium px-2 py-0.5 rounded-full transition">
                            <i class="fa fa-video text-xs"></i>
                        </a>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3.5">
                    <div class="flex items-center gap-2 justify-end">
                        <a href="{{ route('schedules.edit', $schedule) }}" class="text-slate-400 hover:text-amber-500 transition"><i class="fa fa-pen"></i></a>
                        <form method="POST" action="{{ route('schedules.destroy', $schedule) }}" onsubmit="return confirm('Видалити?')">
                            @csrf @method('DELETE')
                            <button class="text-slate-400 hover:text-red-500 transition"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-slate-400 py-12">Записів у розкладі не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if(isset($schedules) && $schedules->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">{{ $schedules->withQueryString()->links() }}</div>
    @endif
</div>

@else
{{-- ===== CALENDAR VIEW ===== --}}
@php
    $scColors     = ['draft'=>'bg-slate-100 text-slate-400','scheduled'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-500','no_show'=>'bg-orange-100 text-orange-600'];
    $calSchedules = $calSchedules ?? collect();
    $today        = now()->toDateString();
@endphp

{{-- Calendar filters --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-2 items-end">
        <input type="hidden" name="view" value="calendar">
        <input type="hidden" name="cal_period" value="{{ $calPeriod }}">
        @if($calPeriod === 'week' || $calPeriod === 'day')
        <input type="hidden" name="cal_date" value="{{ is_string($calDate) ? $calDate : $calDate->toDateString() }}">
        @else
        <input type="hidden" name="cal_year" value="{{ $calYear }}">
        <input type="hidden" name="cal_month" value="{{ $calMonth }}">
        @endif
        @if($teachers->count())
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-medium text-slate-500 mb-1">Викладач</label>
            <select name="teacher_id" class="w-full border border-slate-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі</option>
                @foreach($teachers as $t)
                <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-medium text-slate-500 mb-1">Студент</label>
            <select name="student_id" class="w-full border border-slate-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі</option>
                @foreach($students as $s)
                <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-medium text-slate-500 mb-1">Статус</label>
            <select name="status" class="w-full border border-slate-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі</option>
                @foreach(\App\Models\Schedule::STATUSES as $st)
                <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-600 transition self-end">
            <i class="fa fa-search mr-1"></i> Фільтр
        </button>
    </form>
</div>

{{-- ===== MONTH VIEW ===== --}}
@if($calPeriod === 'month')
@php
    $calDateObj = \Carbon\Carbon::create($calYear, $calMonth, 1);
    $prevMonth  = $calDateObj->copy()->subMonth();
    $nextMonth  = $calDateObj->copy()->addMonth();
    $firstDay   = (int) $calDateObj->copy()->startOfMonth()->dayOfWeek;
    $firstDay   = $firstDay === 0 ? 6 : $firstDay - 1;
    $daysInMonth= $calDateObj->daysInMonth;
@endphp
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <a href="{{ route('schedules.index', array_merge($baseParams, ['view'=>'calendar','cal_period'=>'month','cal_year'=>$prevMonth->year,'cal_month'=>$prevMonth->month])) }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
            <i class="fa fa-chevron-left text-xs"></i>
        </a>
        <h3 class="text-base font-semibold text-slate-800 capitalize">{{ $calDateObj->translatedFormat('F Y') }}</h3>
        <a href="{{ route('schedules.index', array_merge($baseParams, ['view'=>'calendar','cal_period'=>'month','cal_year'=>$nextMonth->year,'cal_month'=>$nextMonth->month])) }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
            <i class="fa fa-chevron-right text-xs"></i>
        </a>
    </div>
    <div class="grid grid-cols-7 border-b border-slate-100">
        @foreach(['Пн','Вт','Ср','Чт','Пт','Сб','Нд'] as $dn)
        <div class="py-2 text-center text-xs font-semibold text-slate-400 uppercase">{{ $dn }}</div>
        @endforeach
    </div>
    <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-slate-100">
        @for($i = 0; $i < $firstDay; $i++)<div class="min-h-[80px] bg-slate-50/50 p-1"></div>@endfor
        @for($day = 1; $day <= $daysInMonth; $day++)
        @php
            $dateStr  = \Carbon\Carbon::create($calYear, $calMonth, $day)->format('Y-m-d');
            $dayItems = $calSchedules[$dateStr] ?? collect();
            $isToday  = $dateStr === $today;
        @endphp
        <div class="min-h-[80px] p-1 {{ $isToday ? 'bg-indigo-50' : '' }} hover:bg-slate-50/80 transition">
            <div class="flex items-center justify-between mb-1 px-0.5">
                <span class="text-xs font-medium {{ $isToday ? 'w-5 h-5 flex items-center justify-center bg-indigo-500 text-white rounded-full text-[10px]' : 'text-slate-500' }}">{{ $day }}</span>
                @if($dayItems->count() > 3)<span class="text-[10px] text-slate-400">+{{ $dayItems->count()-3 }}</span>@endif
            </div>
            @foreach($dayItems->take(3) as $ev)
            <a href="{{ route('schedules.show', $ev) }}"
               class="block text-[10px] leading-tight px-1 py-0.5 rounded mb-0.5 truncate {{ $scColors[$ev->status] ?? 'bg-slate-100 text-slate-600' }}"
               title="{{ \Illuminate\Support\Carbon::parse($ev->scheduled_time)->format('H:i') }} — {{ $ev->lesson->title }}">
                {{ \Illuminate\Support\Carbon::parse($ev->scheduled_time)->format('H:i') }} {{ $ev->lesson->title }}
            </a>
            @endforeach
        </div>
        @endfor
        @php $trailing = ($firstDay + $daysInMonth) % 7; $trailing = $trailing === 0 ? 0 : 7 - $trailing; @endphp
        @for($i = 0; $i < $trailing; $i++)<div class="min-h-[80px] bg-slate-50/50 p-1"></div>@endfor
    </div>
</div>

{{-- ===== WEEK VIEW ===== --}}
@elseif($calPeriod === 'week')
@php
    $prevWeek = $weekStart->copy()->subWeek();
    $nextWeek = $weekStart->copy()->addWeek();
    $weekDays = collect();
    for ($d = 0; $d < 7; $d++) $weekDays->push($weekStart->copy()->addDays($d));
@endphp
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <a href="{{ route('schedules.index', array_merge($baseParams, ['view'=>'calendar','cal_period'=>'week','cal_date'=>$prevWeek->toDateString()])) }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
            <i class="fa fa-chevron-left text-xs"></i>
        </a>
        <h3 class="text-sm font-semibold text-slate-800">
            {{ $weekStart->format('d.m') }} — {{ $weekEnd->format('d.m.Y') }}
        </h3>
        <a href="{{ route('schedules.index', array_merge($baseParams, ['view'=>'calendar','cal_period'=>'week','cal_date'=>$nextWeek->toDateString()])) }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
            <i class="fa fa-chevron-right text-xs"></i>
        </a>
    </div>
    <div class="overflow-x-auto">
    <div class="grid grid-cols-7 min-w-[560px] border-b border-slate-100">
        @foreach($weekDays as $wd)
        @php $isToday = $wd->toDateString() === $today; @endphp
        <div class="py-2 text-center border-r border-slate-100 last:border-0">
            <p class="text-xs font-semibold text-slate-400 uppercase">{{ $wd->isoFormat('dd') }}</p>
            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-sm font-bold mt-0.5
                {{ $isToday ? 'bg-indigo-500 text-white' : 'text-slate-700' }}">{{ $wd->format('d') }}</span>
        </div>
        @endforeach
    </div>
    <div class="grid grid-cols-7 min-w-[560px] divide-x divide-slate-100">
        @foreach($weekDays as $wd)
        @php $dayItems = $calSchedules[$wd->toDateString()] ?? collect(); $isToday = $wd->toDateString() === $today; @endphp
        <div class="min-h-[140px] p-1.5 {{ $isToday ? 'bg-indigo-50/50' : '' }}">
            @forelse($dayItems as $ev)
            <a href="{{ route('schedules.show', $ev) }}"
               class="block text-xs leading-tight px-1.5 py-1 rounded-lg mb-1 {{ $scColors[$ev->status] ?? 'bg-slate-100 text-slate-600' }} hover:opacity-80 transition">
                <p class="font-semibold">{{ \Illuminate\Support\Carbon::parse($ev->scheduled_time)->format('H:i') }}</p>
                <p class="truncate">{{ $ev->lesson->title }}</p>
                <p class="text-[10px] opacity-70 truncate">{{ $ev->student->name }}</p>
            </a>
            @empty
            <p class="text-center text-slate-200 text-xs mt-4">—</p>
            @endforelse
        </div>
        @endforeach
    </div>
    </div>
</div>

{{-- ===== DAY VIEW ===== --}}
@elseif($calPeriod === 'day')
@php
    $calDayObj = is_string($calDate) ? \Carbon\Carbon::parse($calDate) : $calDate;
    $prevDay   = $calDayObj->copy()->subDay();
    $nextDay   = $calDayObj->copy()->addDay();
    $dayItems  = $calSchedules instanceof \Illuminate\Support\Collection ? $calSchedules : collect($calSchedules);
@endphp
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <a href="{{ route('schedules.index', array_merge($baseParams, ['view'=>'calendar','cal_period'=>'day','cal_date'=>$prevDay->toDateString()])) }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
            <i class="fa fa-chevron-left text-xs"></i>
        </a>
        <h3 class="text-base font-semibold text-slate-800 capitalize">
            {{ $calDayObj->translatedFormat('l, d F Y') }}
        </h3>
        <a href="{{ route('schedules.index', array_merge($baseParams, ['view'=>'calendar','cal_period'=>'day','cal_date'=>$nextDay->toDateString()])) }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
            <i class="fa fa-chevron-right text-xs"></i>
        </a>
    </div>
    <div class="divide-y divide-slate-50 p-4">
        @forelse($dayItems as $ev)
        <div class="flex items-start gap-4 py-3">
            <div class="w-14 text-center flex-shrink-0">
                <p class="text-base font-bold text-indigo-600">{{ \Illuminate\Support\Carbon::parse($ev->scheduled_time)->format('H:i') }}</p>
                <p class="text-xs text-slate-400">{{ $ev->duration_minutes }} хв</p>
            </div>
            <div class="flex-1 min-w-0">
                <a href="{{ route('schedules.show', $ev) }}">
                    <p class="font-semibold text-slate-800">{{ $ev->lesson->title }}</p>
                </a>
                <p class="text-xs text-slate-500">{{ $ev->lesson->course->title ?? '—' }}</p>
                <div class="flex items-center gap-3 mt-1 text-xs text-slate-400">
                    <span><i class="fa fa-user mr-1"></i>{{ $ev->student->name }}</span>
                    <span><i class="fa fa-chalkboard-teacher mr-1"></i>{{ $ev->teacher->name }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $scColors[$ev->status] ?? '' }}">
                    {{ ucfirst(str_replace('_',' ',$ev->status)) }}
                </span>
            </div>
        </div>
        @empty
        <div class="py-12 text-center text-slate-400">
            <i class="fa fa-calendar-day text-3xl mb-3 block text-slate-200"></i>
            Занять на цей день не заплановано
        </div>
        @endforelse
    </div>
</div>
@endif

@endif
@endsection
