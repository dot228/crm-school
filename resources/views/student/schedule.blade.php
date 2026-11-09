@extends('layouts.app')
@section('title', 'Мій розклад')
@section('header', 'Мій розклад')

@section('content')
@php
    $viewMode  = $viewMode  ?? 'list';
    $calPeriod = $calPeriod ?? 'month';
    $calYear   = $calYear   ?? now()->year;
    $calMonth  = $calMonth  ?? now()->month;
    $calDate   = $calDate   ?? now();
    $scColors  = ['scheduled'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-600','no_show'=>'bg-orange-100 text-orange-600'];
    $scLabels  = ['scheduled'=>'Заплановано','completed'=>'Проведено','cancelled'=>'Скасовано','no_show'=>"Не з'явився"];
    $today     = now()->toDateString();
    $baseParams = [];
@endphp

<div class="flex items-start sm:items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Мій розклад</h2>
        <p class="text-sm text-slate-500 mt-0.5 hidden sm:block">Заплановані та минулі заняття</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap justify-end">
        <div class="flex border border-slate-200 rounded-xl overflow-hidden">
            <a href="{{ route('student.schedule', ['view'=>'list']) }}"
               class="px-3 py-2 text-sm transition {{ $viewMode==='list' ? 'bg-indigo-500 text-white' : 'text-slate-500 hover:bg-slate-50' }}">
                <i class="fa fa-list text-xs"></i>
            </a>
            <a href="{{ route('student.schedule', ['view'=>'calendar','cal_period'=>'month']) }}"
               class="px-3 py-2 text-sm transition {{ $viewMode==='calendar' ? 'bg-indigo-500 text-white' : 'text-slate-500 hover:bg-slate-50' }}">
                <i class="fa fa-calendar text-xs"></i>
            </a>
        </div>
        @if($viewMode === 'calendar')
        <div class="flex border border-slate-200 rounded-xl overflow-hidden text-xs">
            <a href="{{ route('student.schedule', ['view'=>'calendar','cal_period'=>'month','cal_year'=>$calYear,'cal_month'=>$calMonth]) }}"
               class="px-3 py-2 transition {{ $calPeriod==='month' ? 'bg-slate-700 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Місяць</a>
            <a href="{{ route('student.schedule', ['view'=>'calendar','cal_period'=>'week','cal_date'=>(is_string($calDate)?$calDate:$calDate->toDateString())]) }}"
               class="px-3 py-2 transition {{ $calPeriod==='week' ? 'bg-slate-700 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Тиждень</a>
            <a href="{{ route('student.schedule', ['view'=>'calendar','cal_period'=>'day','cal_date'=>(is_string($calDate)?$calDate:$calDate->toDateString())]) }}"
               class="px-3 py-2 transition {{ $calPeriod==='day' ? 'bg-slate-700 text-white' : 'text-slate-500 hover:bg-slate-50' }}">День</a>
        </div>
        @endif
    </div>
</div>

{{-- ===== LIST VIEW ===== --}}
@if($viewMode === 'list')
<div class="max-w-3xl">
    @forelse($schedules as $schedule)
    @php $isUpcoming = $schedule->scheduled_date >= now()->toDateString() && $schedule->status === 'scheduled'; @endphp
    <div class="bg-white rounded-2xl shadow-sm border {{ $isUpcoming ? 'border-indigo-100' : 'border-slate-100' }} p-5 mb-4">
        <div class="flex items-start justify-between gap-4">
            <div class="flex gap-4 flex-1 min-w-0">
                <div class="flex-shrink-0 text-center w-14">
                    <p class="text-2xl font-bold {{ $isUpcoming ? 'text-indigo-600' : 'text-slate-400' }}">
                        {{ $schedule->scheduled_date->format('d') }}
                    </p>
                    <p class="text-xs {{ $isUpcoming ? 'text-indigo-400' : 'text-slate-400' }} uppercase tracking-wide">
                        {{ $schedule->scheduled_date->isoFormat('MMM') }}
                    </p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-800 truncate">{{ $schedule->lesson->course->title ?? '—' }} — {{ $schedule->lesson->title ?? '—' }}</p>
                    <p class="text-sm text-slate-500 mt-0.5">
                        <i class="fa fa-clock text-xs text-slate-400 mr-1"></i>
                        {{ \Illuminate\Support\Carbon::parse($schedule->scheduled_time)->format('H:i') }} · {{ $schedule->duration_minutes }} хв
                    </p>
                    <p class="text-sm text-slate-500 mt-0.5">
                        <i class="fa fa-user text-xs text-slate-400 mr-1"></i>{{ $schedule->teacher->name ?? '—' }}
                    </p>
                </div>
            </div>
            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $scColors[$schedule->status] ?? 'bg-slate-100 text-slate-500' }}">
                    {{ $scLabels[$schedule->status] ?? ucfirst($schedule->status) }}
                </span>
                @if($isUpcoming && $schedule->zoom_link)
                <a href="{{ $schedule->zoom_link }}" target="_blank"
                   class="inline-flex items-center gap-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition shadow-sm">
                    <i class="fa fa-video text-xs"></i> Приєднатись
                </a>
                @elseif($isUpcoming)
                <span class="text-xs text-slate-400 italic">Посилання ще не додано</span>
                @endif
            </div>
        </div>
        @if($schedule->notes)
        <div class="mt-3 pt-3 border-t border-slate-50 text-xs text-slate-500">
            <i class="fa fa-note-sticky mr-1 text-slate-400"></i>{{ $schedule->notes }}
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
        <i class="fa fa-calendar-days text-3xl text-slate-200 mb-3 block"></i>
        <p class="text-slate-400 text-sm">Занять ще не заплановано</p>
    </div>
    @endforelse
    @if(isset($schedules) && $schedules->hasPages())
    <div class="mt-4">{{ $schedules->links() }}</div>
    @endif
</div>

{{-- ===== CALENDAR VIEW ===== --}}
@else
    @php $calSchedules = $calSchedules ?? collect(); @endphp

    {{-- ===== MONTH VIEW ===== --}}
    @if($calPeriod === 'month')
    @php
        $calDateObj  = \Carbon\Carbon::create($calYear, $calMonth, 1);
        $prevMonth   = $calDateObj->copy()->subMonth();
        $nextMonth   = $calDateObj->copy()->addMonth();
        $firstDay    = (int) $calDateObj->copy()->startOfMonth()->dayOfWeek;
        $firstDay    = $firstDay === 0 ? 6 : $firstDay - 1;
        $daysInMonth = $calDateObj->daysInMonth;
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <a href="{{ route('student.schedule', ['view'=>'calendar','cal_period'=>'month','cal_year'=>$prevMonth->year,'cal_month'=>$prevMonth->month]) }}"
               class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
                <i class="fa fa-chevron-left text-xs"></i>
            </a>
            <h3 class="text-base font-semibold text-slate-800 capitalize">{{ $calDateObj->translatedFormat('F Y') }}</h3>
            <a href="{{ route('student.schedule', ['view'=>'calendar','cal_period'=>'month','cal_year'=>$nextMonth->year,'cal_month'=>$nextMonth->month]) }}"
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
                    @if($dayItems->count() > 2)<span class="text-[10px] text-slate-400">+{{ $dayItems->count()-2 }}</span>@endif
                </div>
                @foreach($dayItems->take(2) as $ev)
                <div class="text-[10px] leading-tight px-1 py-0.5 rounded mb-0.5 truncate {{ $scColors[$ev->status] ?? 'bg-slate-100 text-slate-600' }}"
                     title="{{ \Illuminate\Support\Carbon::parse($ev->scheduled_time)->format('H:i') }} — {{ $ev->lesson->title }}">
                    {{ \Illuminate\Support\Carbon::parse($ev->scheduled_time)->format('H:i') }} {{ $ev->lesson->title }}
                    @if($ev->zoom_link && $ev->status === 'scheduled')
                    <a href="{{ $ev->zoom_link }}" target="_blank" class="ml-1 text-blue-600"><i class="fa fa-video text-[9px]"></i></a>
                    @endif
                </div>
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
            <a href="{{ route('student.schedule', ['view'=>'calendar','cal_period'=>'week','cal_date'=>$prevWeek->toDateString()]) }}"
               class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
                <i class="fa fa-chevron-left text-xs"></i>
            </a>
            <h3 class="text-sm font-semibold text-slate-800">{{ $weekStart->format('d.m') }} — {{ $weekEnd->format('d.m.Y') }}</h3>
            <a href="{{ route('student.schedule', ['view'=>'calendar','cal_period'=>'week','cal_date'=>$nextWeek->toDateString()]) }}"
               class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
                <i class="fa fa-chevron-right text-xs"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
        <div class="grid grid-cols-7 min-w-[420px] border-b border-slate-100">
            @foreach($weekDays as $wd)
            @php $isToday = $wd->toDateString() === $today; @endphp
            <div class="py-2 text-center border-r border-slate-100 last:border-0">
                <p class="text-xs font-semibold text-slate-400 uppercase">{{ $wd->isoFormat('dd') }}</p>
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-sm font-bold mt-0.5
                    {{ $isToday ? 'bg-indigo-500 text-white' : 'text-slate-700' }}">{{ $wd->format('d') }}</span>
            </div>
            @endforeach
        </div>
        <div class="grid grid-cols-7 min-w-[420px] divide-x divide-slate-100">
            @foreach($weekDays as $wd)
            @php $dayItems = $calSchedules[$wd->toDateString()] ?? collect(); $isToday = $wd->toDateString() === $today; @endphp
            <div class="min-h-[120px] p-1 {{ $isToday ? 'bg-indigo-50/50' : '' }}">
                @forelse($dayItems as $ev)
                <div class="text-xs leading-tight px-1.5 py-1 rounded-lg mb-1 {{ $scColors[$ev->status] ?? 'bg-slate-100 text-slate-600' }}">
                    <p class="font-semibold">{{ \Illuminate\Support\Carbon::parse($ev->scheduled_time)->format('H:i') }}</p>
                    <p class="truncate">{{ $ev->lesson->title }}</p>
                    @if($ev->zoom_link && $ev->status === 'scheduled')
                    <a href="{{ $ev->zoom_link }}" target="_blank" class="text-blue-600 hover:underline text-[10px]"><i class="fa fa-video mr-0.5"></i>Zoom</a>
                    @endif
                </div>
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
    <div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <a href="{{ route('student.schedule', ['view'=>'calendar','cal_period'=>'day','cal_date'=>$prevDay->toDateString()]) }}"
               class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
                <i class="fa fa-chevron-left text-xs"></i>
            </a>
            <h3 class="text-base font-semibold text-slate-800 capitalize">{{ $calDayObj->translatedFormat('l, d F Y') }}</h3>
            <a href="{{ route('student.schedule', ['view'=>'calendar','cal_period'=>'day','cal_date'=>$nextDay->toDateString()]) }}"
               class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
                <i class="fa fa-chevron-right text-xs"></i>
            </a>
        </div>
        <div class="divide-y divide-slate-50 p-4">
            @forelse($dayItems as $ev)
            @php $isUpcoming = $ev->scheduled_date >= now()->toDateString() && $ev->status === 'scheduled'; @endphp
            <div class="flex items-start gap-4 py-3">
                <div class="w-14 text-center flex-shrink-0">
                    <p class="text-base font-bold {{ $isUpcoming ? 'text-indigo-600' : 'text-slate-400' }}">
                        {{ \Illuminate\Support\Carbon::parse($ev->scheduled_time)->format('H:i') }}
                    </p>
                    <p class="text-xs text-slate-400">{{ $ev->duration_minutes }} хв</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-800">{{ $ev->lesson->title }}</p>
                    <p class="text-xs text-slate-500">{{ $ev->lesson->course->title ?? '—' }}</p>
                    <p class="text-xs text-slate-400 mt-0.5"><i class="fa fa-user mr-1"></i>{{ $ev->teacher->name ?? '—' }}</p>
                </div>
                <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $scColors[$ev->status] ?? '' }}">
                        {{ $scLabels[$ev->status] ?? $ev->status }}
                    </span>
                    @if($isUpcoming && $ev->zoom_link)
                    <a href="{{ $ev->zoom_link }}" target="_blank"
                       class="inline-flex items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold px-2.5 py-1 rounded-xl transition">
                        <i class="fa fa-video text-xs"></i> Zoom
                    </a>
                    @endif
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
