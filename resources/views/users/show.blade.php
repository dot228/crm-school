@extends('layouts.app')
@section('title', $user->name)
@section('header', 'Користувачі')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('users.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">{{ $user->name }}</h2>
    </div>

    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <div class="flex items-start gap-5">
            <img src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-2xl object-cover flex-shrink-0" alt="">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <h3 class="text-xl font-semibold text-slate-800">{{ $user->name }}</h3>
                    @php $roleColors = [1=>'bg-red-100 text-red-700', 2=>'bg-orange-100 text-orange-700', 3=>'bg-violet-100 text-violet-700', 4=>'bg-blue-100 text-blue-700']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $roleColors[$user->role_id] ?? '' }}">{{ $user->role_label }}</span>
                    @php $sc = ['active'=>'bg-emerald-100 text-emerald-700','inactive'=>'bg-slate-100 text-slate-500','blocked'=>'bg-red-100 text-red-600']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$user->status] ?? '' }}">{{ ucfirst($user->status) }}</span>
                </div>
                <a href="mailto:{{ $user->email }}" class="text-slate-500 text-sm hover:text-indigo-600 transition">{{ $user->email }}</a>
                @if($user->phone)<p class="text-slate-500 text-sm mt-0.5"><i class="fa fa-phone mr-1 text-xs text-slate-400"></i><a href="tel:{{ $user->phone }}" class="hover:text-indigo-600 transition">{{ $user->phone }}</a></p>@endif
                @if($user->bio)<p class="text-slate-600 text-sm mt-3 leading-relaxed">{{ $user->bio }}</p>@endif
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center gap-2 border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition">
                    <i class="fa fa-pen text-xs"></i> Редагувати
                </a>
                @if(auth()->id() !== $user->id)
                <form method="POST" action="{{ route('users.block', $user->id) }}"
                      onsubmit="return confirm('{{ $user->status === 'blocked' ? 'Розблокувати' : 'Заблокувати' }} користувача?')">
                    @csrf
                    <button class="inline-flex items-center gap-2 border px-4 py-2 rounded-xl text-sm transition
                        {{ $user->status === 'blocked' ? 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' : 'border-red-200 text-red-600 hover:bg-red-50' }}">
                        <i class="fa {{ $user->status === 'blocked' ? 'fa-unlock' : 'fa-ban' }} text-xs"></i>
                        {{ $user->status === 'blocked' ? 'Розблокувати' : 'Заблокувати' }}
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- === STUDENT === --}}
    @if($user->isStudent())
    @php
        $payments  = $user->payments()->orderBy('payment_date','desc')->get();
        $totalPaid = $payments->where('status','paid')->sum('amount');
        $totalDebt = $payments->where('status','pending')->sum('amount');
    @endphp

    @php $totalBalance = $user->enrollments()->sum('lessons_balance'); @endphp
    <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-4 mb-4 flex items-center justify-between">
        <div>
            <p class="text-xs text-slate-400">Грошовий баланс</p>
            <p class="text-xl font-bold {{ $user->money_balance > 0 ? 'text-emerald-600' : 'text-slate-400' }} leading-tight mt-0.5">{{ number_format($user->money_balance, 2) }} ₴</p>
        </div>
        <i class="fa fa-wallet text-2xl text-emerald-300"></i>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Статус</p>
            <p class="text-sm font-bold {{ $totalDebt > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                {{ $totalDebt > 0 ? 'Заборг' : 'Оплачено' }}
            </p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Оплачено</p>
            <p class="text-lg font-bold text-emerald-600 leading-tight">{{ number_format($totalPaid, 0) }} ₴</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Заборг</p>
            <p class="text-lg font-bold {{ $totalDebt > 0 ? 'text-red-500' : 'text-slate-400' }} leading-tight">{{ number_format($totalDebt, 0) }} ₴</p>
        </div>
        <div class="bg-white rounded-2xl border {{ $totalBalance > 0 ? 'border-indigo-100' : 'border-red-100' }} shadow-sm p-3">
            <p class="text-xs text-slate-400 mb-1">Баланс уроків</p>
            <p class="text-lg font-bold {{ $totalBalance > 0 ? 'text-indigo-600' : 'text-red-500' }} leading-tight">{{ $totalBalance }}</p>
        </div>
    </div>

    {{-- Guardian info --}}
    @if($user->guardian_name || $user->date_of_birth)
    <div class="bg-white rounded-2xl shadow-sm border {{ !$user->isAdult() ? 'border-amber-100' : 'border-slate-100' }} p-6 mb-5">
        <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <i class="fa fa-cake-candles text-slate-400"></i> Особисті дані
        </h4>
        @if($user->date_of_birth)
        <p class="text-sm text-slate-600 mb-2">
            Дата народження:
            <span class="font-medium text-slate-800">{{ $user->date_of_birth->format('d.m.Y') }}</span>
            <span class="text-slate-400 text-xs ml-1">({{ $user->age }} р.)</span>
            @if(!$user->isAdult())
            <span class="ml-1 px-1.5 py-0.5 bg-amber-100 text-amber-700 text-xs rounded-full">неповнолітній</span>
            @endif
        </p>
        @endif
        @if($user->guardian_name)
        <div class="flex items-start gap-2 mt-1">
            <i class="fa fa-user-shield text-amber-500 mt-0.5"></i>
            <div>
                <p class="text-sm font-medium text-slate-800">
                    {{ $user->guardian_name }}
                    @if($user->guardian_relation)<span class="text-xs text-slate-400 font-normal ml-1">({{ $user->guardian_relation }})</span>@endif
                </p>
                @if($user->guardian_phone)<p class="text-xs text-slate-500"><i class="fa fa-phone mr-1"></i>{{ $user->guardian_phone }}</p>@endif
                @if($user->guardian_email)<p class="text-xs text-slate-500"><i class="fa fa-envelope mr-1"></i>{{ $user->guardian_email }}</p>@endif
            </div>
        </div>
        @endif
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Зарахування</h4>
        @forelse($user->enrollments()->with(['group.course'])->get() as $enrollment)
        <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0 gap-2">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-slate-800 truncate">{{ $enrollment->group->course->title ?? '—' }}</p>
                <p class="text-xs text-slate-400">Група: {{ $enrollment->group->name ?? '—' }} · {{ $enrollment->enrolled_at->format('d.m.Y') }}</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-sm font-bold {{ $enrollment->lessons_balance > 0 ? 'text-indigo-600' : 'text-red-500' }}">{{ $enrollment->lessons_balance }} ур.</span>
                <span class="px-2 py-0.5 rounded-full text-xs {{ $enrollment->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $enrollment->status }}</span>
            </div>
        </div>
        @empty
        <p class="text-sm text-slate-400 py-4 text-center">Немає зарахувань</p>
        @endforelse
    </div>

    @if($payments->count())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Платежі</h4>
        @php $psc = ['paid'=>'bg-emerald-100 text-emerald-700','pending'=>'bg-amber-100 text-amber-700','cancelled'=>'bg-red-100 text-red-600']; @endphp
        @foreach($payments as $payment)
        <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
            <div>
                <p class="text-sm font-medium text-slate-800">{{ number_format($payment->amount, 0) }} ₴</p>
                <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d.m.Y') }}{{ $payment->notes ? ' · '.$payment->notes : '' }}</p>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $psc[$payment->status] ?? '' }}">{{ ucfirst($payment->status) }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Upcoming schedules for student --}}
    @php $studentSchedules = \App\Models\Schedule::with(['lesson.course','teacher'])->where('student_id',$user->id)->orderBy('scheduled_date','desc')->limit(5)->get(); @endphp
    @if($studentSchedules->count())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Розклад занять</h4>
        @php $sc2=['scheduled'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-600','no_show'=>'bg-orange-100 text-orange-600']; @endphp
        @foreach($studentSchedules as $s)
        <div class="flex items-center gap-3 py-2.5 border-b border-slate-50 last:border-0">
            <div class="w-10 text-center flex-shrink-0">
                <p class="text-xs font-bold text-blue-600">{{ $s->scheduled_date->format('d') }}</p>
                <p class="text-xs text-slate-400">{{ $s->scheduled_date->format('m') }}</p>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800 truncate">{{ $s->lesson->title }}</p>
                <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($s->scheduled_time)->format('H:i') }} · {{ $s->teacher->name }}</p>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs {{ $sc2[$s->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$s->status)) }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- === TEACHER === --}}
    @elseif($user->isTeacher())
    @php $teacherSchedules = \App\Models\Schedule::with(['lesson.course','student'])->where('teacher_id',$user->id)->orderBy('scheduled_date','desc')->limit(5)->get(); @endphp

    <div class="grid grid-cols-2 gap-4 mb-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs text-slate-400 mb-1">Груп</p>
            <p class="text-2xl font-bold text-violet-600">{{ $user->teachingGroups()->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs text-slate-400 mb-1">Занять у розкладі</p>
            <p class="text-2xl font-bold text-slate-700">{{ $teacherSchedules->count() }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Групи</h4>
        @forelse($user->teachingGroups()->with('course')->get() as $group)
        <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
            <div>
                <p class="text-sm font-medium text-slate-800">{{ $group->name }}</p>
                <p class="text-xs text-slate-400">{{ $group->course->title ?? '—' }}</p>
            </div>
            <span class="text-xs text-slate-400">{{ $group->students_count ?? 0 }} студентів</span>
        </div>
        @empty
        <p class="text-sm text-slate-400 py-4 text-center">Немає груп</p>
        @endforelse
    </div>

    @if($teacherSchedules->count())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h4 class="text-sm font-semibold text-slate-700 mb-4">Розклад занять</h4>
        @php $sc2=['scheduled'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-600','no_show'=>'bg-orange-100 text-orange-600']; @endphp
        @foreach($teacherSchedules as $s)
        <div class="flex items-center gap-3 py-2.5 border-b border-slate-50 last:border-0">
            <div class="w-10 text-center flex-shrink-0">
                <p class="text-xs font-bold text-violet-600">{{ $s->scheduled_date->format('d') }}</p>
                <p class="text-xs text-slate-400">{{ $s->scheduled_date->format('m') }}</p>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800 truncate">{{ $s->lesson->title }}</p>
                <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($s->scheduled_time)->format('H:i') }} · {{ $s->student->name }}</p>
            </div>
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

</div>
@endsection
