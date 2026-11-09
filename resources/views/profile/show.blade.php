@extends('layouts.app')
@section('title', 'Мій профіль')
@section('header', 'Профіль')

@section('content')
<div class="max-w-3xl">

    {{-- Header card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <div class="flex items-start gap-5">
            <img src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-2xl object-cover flex-shrink-0" alt="">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <h2 class="text-xl font-bold text-slate-800">{{ $user->name }}</h2>
                    @php $roleColors = [1=>'bg-red-100 text-red-700', 2=>'bg-orange-100 text-orange-700', 3=>'bg-violet-100 text-violet-700', 4=>'bg-blue-100 text-blue-700']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $roleColors[$user->role_id] ?? '' }}">{{ $user->role_label }}</span>
                    @php $sc = ['active'=>'bg-emerald-100 text-emerald-700','inactive'=>'bg-slate-100 text-slate-500','blocked'=>'bg-red-100 text-red-600']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$user->status] ?? '' }}">{{ ucfirst($user->status) }}</span>
                    @if($user->hasTwoFactorEnabled())
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700"><i class="fa fa-shield-halved mr-1"></i>2FA увімкнено</span>
                    @endif
                </div>
                <a href="mailto:{{ $user->email }}" class="text-slate-500 text-sm hover:text-indigo-600 transition">{{ $user->email }}</a>
                @if($user->phone)<p class="text-slate-500 text-sm mt-0.5"><i class="fa fa-phone mr-1 text-xs text-slate-400"></i><a href="tel:{{ $user->phone }}" class="hover:text-indigo-600 transition">{{ $user->phone }}</a></p>@endif
                @if($user->bio)<p class="text-slate-600 text-sm mt-3 leading-relaxed">{{ $user->bio }}</p>@endif
            </div>
            <a href="{{ route('profile.edit') }}"
               class="inline-flex items-center gap-2 border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition flex-shrink-0">
                <i class="fa fa-pen text-xs"></i> Редагувати
            </a>
        </div>
    </div>

    {{-- === STUDENT === --}}
    @if($user->isStudent())

    {{-- Payment summary --}}
    @php
        $payments   = $user->payments->sortByDesc('payment_date');
        $totalPaid  = $payments->where('status', 'paid')->sum('amount');
        $totalDebt  = $payments->where('status', 'pending')->sum('amount');
    @endphp
    @php $totalBalance = $user->enrollments->sum('lessons_balance'); @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Оплачено</p>
            <p class="text-xl font-bold text-emerald-600 leading-tight">{{ number_format($totalPaid, 0) }} ₴</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Заборг</p>
            <p class="text-xl font-bold {{ $totalDebt > 0 ? 'text-red-500' : 'text-slate-400' }} leading-tight">{{ number_format($totalDebt, 0) }} ₴</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Зарахувань</p>
            <p class="text-xl font-bold text-slate-700 leading-tight">{{ $user->enrollments->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border {{ $totalBalance > 0 ? 'border-indigo-100' : 'border-red-100' }} shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Баланс уроків</p>
            <p class="text-xl font-bold {{ $totalBalance > 0 ? 'text-indigo-600' : 'text-red-500' }} leading-tight">{{ $totalBalance }}</p>
        </div>
    </div>
    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
    <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-4 mb-5 flex items-center justify-between">
        <div>
            <p class="text-xs text-slate-400">Грошовий баланс <span class="text-slate-300">· видно тільки адміну/менеджеру</span></p>
            <p class="text-xl font-bold {{ $user->money_balance > 0 ? 'text-emerald-600' : 'text-slate-400' }} leading-tight mt-0.5">{{ number_format($user->money_balance, 2) }} ₴</p>
        </div>
        <i class="fa fa-wallet text-2xl text-emerald-300"></i>
    </div>
    @endif

    {{-- Enrollments --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Зарахування</h4>
        @forelse($user->enrollments as $enrollment)
        <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0 gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-slate-800 truncate">{{ $enrollment->group->course->title ?? '—' }}</p>
                <p class="text-xs text-slate-400">Група: {{ $enrollment->group->name ?? '—' }} · {{ $enrollment->enrolled_at->format('d.m.Y') }}</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <div class="text-right">
                    <p class="text-xs text-slate-400">Баланс</p>
                    <p class="text-sm font-bold {{ $enrollment->lessons_balance > 0 ? 'text-indigo-600' : 'text-red-500' }}">
                        {{ $enrollment->lessons_balance }} ур.
                    </p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs {{ $enrollment->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $enrollment->status }}
                </span>
            </div>
        </div>
        @empty
        <p class="text-sm text-slate-400 py-4 text-center">Немає зарахувань</p>
        @endforelse
    </div>

    {{-- Payment history --}}
    @if($payments->count())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Історія платежів</h4>
        @php $psc = ['paid'=>'bg-emerald-100 text-emerald-700','pending'=>'bg-amber-100 text-amber-700','cancelled'=>'bg-red-100 text-red-600']; @endphp
        @foreach($payments->take(10) as $payment)
        <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
            <div>
                <p class="text-sm font-medium text-slate-800">{{ number_format($payment->amount, 0) }} ₴</p>
                <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d.m.Y') }} · {{ $payment->notes ?? '—' }}</p>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $psc[$payment->status] ?? '' }}">
                {{ ucfirst($payment->status) }}
            </span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Guardian info --}}
    @if($user->guardian_name || $user->date_of_birth)
    <div class="bg-white rounded-2xl shadow-sm border {{ !$user->isAdult() ? 'border-amber-100' : 'border-slate-100' }} p-6 mb-5">
        <h4 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <i class="fa fa-cake-candles text-slate-400"></i> Особисті дані
        </h4>
        @if($user->date_of_birth)
        <div class="flex items-center justify-between py-2 border-b border-slate-50">
            <span class="text-sm text-slate-500">Дата народження</span>
            <span class="text-sm font-medium text-slate-700">
                {{ $user->date_of_birth->format('d.m.Y') }}
                <span class="text-xs text-slate-400 ml-1">({{ $user->age }} р.)</span>
                @if(!$user->isAdult())
                <span class="ml-1 px-1.5 py-0.5 bg-amber-100 text-amber-700 text-xs rounded-full">неповнолітній</span>
                @endif
            </span>
        </div>
        @endif
        @if($user->guardian_name)
        <div class="mt-3">
            <div class="flex items-center gap-2 mb-2">
                <i class="fa fa-user-shield text-amber-500 text-sm"></i>
                <span class="text-sm font-medium text-slate-700">Відповідальна особа</span>
                @if($user->guardian_relation)
                <span class="text-xs text-slate-400">({{ $user->guardian_relation }})</span>
                @endif
            </div>
            <p class="text-sm text-slate-800 font-medium">{{ $user->guardian_name }}</p>
            @if($user->guardian_phone)
            <p class="text-xs text-slate-500 mt-0.5"><i class="fa fa-phone mr-1 text-slate-400"></i>{{ $user->guardian_phone }}</p>
            @endif
            @if($user->guardian_email)
            <p class="text-xs text-slate-500 mt-0.5"><i class="fa fa-envelope mr-1 text-slate-400"></i>{{ $user->guardian_email }}</p>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- Upcoming schedules --}}
    @if($schedules->count())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Мої заняття</h4>
        @foreach($schedules as $s)
        <div class="flex items-center gap-3 py-2.5 border-b border-slate-50 last:border-0">
            <div class="w-10 text-center">
                <p class="text-xs font-bold text-indigo-600">{{ $s->scheduled_date->format('d') }}</p>
                <p class="text-xs text-slate-400">{{ $s->scheduled_date->translatedFormat('M') }}</p>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-slate-800">{{ $s->lesson->title }}</p>
                <p class="text-xs text-slate-400">{{ $s->lesson->course->title ?? '—' }} · {{ \Carbon\Carbon::parse($s->scheduled_time)->format('H:i') }} · {{ $s->teacher->name }}</p>
            </div>
            @php $sc2=['scheduled'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-600','no_show'=>'bg-orange-100 text-orange-600']; @endphp
            <span class="px-2 py-0.5 rounded-full text-xs {{ $sc2[$s->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$s->status)) }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- === TEACHER === --}}
    @elseif($user->isTeacher())

    <div class="grid grid-cols-2 gap-4 mb-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs text-slate-400 mb-1">Груп</p>
            <p class="text-2xl font-bold text-violet-600">{{ $user->teachingGroups->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs text-slate-400 mb-1">Занять у розкладі</p>
            <p class="text-2xl font-bold text-slate-700">{{ $schedules->count() }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Мої групи</h4>
        @forelse($user->teachingGroups as $group)
        <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
            <div>
                <p class="text-sm font-medium text-slate-800">{{ $group->name }}</p>
                <p class="text-xs text-slate-400">{{ $group->course->title ?? '—' }}</p>
            </div>
            <span class="text-xs text-slate-400">{{ $group->students_count ?? 0 }} студентів</span>
        </div>
        @empty
        <p class="text-sm text-slate-400 py-4 text-center">Груп не призначено</p>
        @endforelse
    </div>

    @if($schedules->count())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Мої заняття</h4>
        @foreach($schedules as $s)
        <div class="flex items-center gap-3 py-2.5 border-b border-slate-50 last:border-0">
            <div class="w-10 text-center">
                <p class="text-xs font-bold text-violet-600">{{ $s->scheduled_date->format('d') }}</p>
                <p class="text-xs text-slate-400">{{ $s->scheduled_date->translatedFormat('M') }}</p>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-slate-800">{{ $s->lesson->title }}</p>
                <p class="text-xs text-slate-400">{{ $s->lesson->course->title ?? '—' }} · {{ \Carbon\Carbon::parse($s->scheduled_time)->format('H:i') }} · {{ $s->student->name }}</p>
            </div>
            @php $sc2=['scheduled'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-600','no_show'=>'bg-orange-100 text-orange-600']; @endphp
            <span class="px-2 py-0.5 rounded-full text-xs {{ $sc2[$s->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$s->status)) }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- === MANAGER === --}}
    @elseif($user->isManager())

    @php
        $teacherCount  = \App\Models\User::where('role_id', \App\Models\User::ROLE_TEACHER)->count();
        $studentCount  = \App\Models\User::where('role_id', \App\Models\User::ROLE_STUDENT)->count();
        $scheduleCount = \App\Models\Schedule::count();
    @endphp
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Викладачів</p>
            <p class="text-xl font-bold text-violet-600">{{ $teacherCount }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Студентів</p>
            <p class="text-xl font-bold text-blue-600">{{ $studentCount }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Занять</p>
            <p class="text-xl font-bold text-slate-700">{{ $scheduleCount }}</p>
        </div>
    </div>

    {{-- === ADMIN === --}}
    @elseif($user->isAdmin())

    @php
        $totalUsers    = \App\Models\User::count();
        $totalCourses  = \App\Models\Course::count();
        $totalPayments = \App\Models\Payment::where('status','paid')->sum('amount');
    @endphp
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Користувачів</p>
            <p class="text-xl font-bold text-red-600">{{ $totalUsers }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Курсів</p>
            <p class="text-xl font-bold text-indigo-600">{{ $totalCourses }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Оплати</p>
            <p class="text-lg font-bold text-emerald-600 leading-tight">{{ number_format($totalPayments, 0) }} ₴</p>
        </div>
    </div>
    @endif

    {{-- 2FA status for all roles --}}
    <div class="bg-white rounded-2xl shadow-sm border {{ $user->hasTwoFactorEnabled() ? 'border-emerald-100' : 'border-amber-100' }} p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center {{ $user->hasTwoFactorEnabled() ? 'bg-emerald-100' : 'bg-amber-50' }}">
                    <i class="fa fa-shield-halved text-lg {{ $user->hasTwoFactorEnabled() ? 'text-emerald-600' : 'text-amber-500' }}"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Двофакторна аутентифікація</p>
                    @if($user->hasTwoFactorEnabled())
                    <p class="text-xs text-emerald-600 mt-0.5">
                        <i class="fa fa-circle-check mr-1"></i> Увімкнено
                        <span class="text-slate-400">· з {{ $user->two_factor_confirmed_at->format('d.m.Y') }}</span>
                    </p>
                    @else
                    <p class="text-xs text-amber-600 mt-0.5">
                        <i class="fa fa-triangle-exclamation mr-1"></i> Вимкнено — рекомендуємо увімкнути
                    </p>
                    @endif
                </div>
            </div>
            <a href="{{ route('profile.edit') }}"
               class="inline-flex items-center gap-1.5 text-xs border px-3 py-1.5 rounded-lg transition
                   {{ $user->hasTwoFactorEnabled()
                       ? 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'
                       : 'border-amber-200 text-amber-700 hover:bg-amber-50' }}">
                <i class="fa fa-pen text-xs"></i>
                {{ $user->hasTwoFactorEnabled() ? 'Налаштування' : 'Увімкнути' }}
            </a>
        </div>
    </div>

</div>
@endsection
