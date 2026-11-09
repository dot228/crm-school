<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CRM School') — EduCRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#6366f1', light: '#818cf8', dark: '#4f46e5' },
                        sidebar: '#0f172a',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-item.active { background: rgba(99,102,241,0.15); border-left: 3px solid #6366f1; }
        .sidebar-item:not(.active) { border-left: 3px solid transparent; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased">

{{-- Mobile sidebar overlay --}}
<div id="sidebar-overlay" onclick="closeSidebar()"
     class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden"></div>

<div class="flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <aside id="sidebar"
           class="fixed lg:relative z-30 w-64 h-full bg-slate-900 text-slate-100 flex flex-col flex-shrink-0 overflow-y-auto
                  -translate-x-full lg:translate-x-0 transition-transform duration-200">
        <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-700">
            <div class="w-9 h-9 bg-indigo-500 rounded-lg flex items-center justify-center">
                <i class="fa fa-graduation-cap text-white text-sm"></i>
            </div>
            <span class="font-bold text-lg tracking-tight">EduCRM</span>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1">
            {{-- Admin/Manager only --}}
            @if(auth()->user()->role_id <= 2)
            <a href="{{ route('dashboard') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('dashboard') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-chart-pie w-4 text-center"></i> Дашборд
            </a>
            @endif
            <a href="{{ route('profile.show') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('profile*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-circle-user w-4 text-center"></i> Мій профіль
            </a>
            {{-- Teacher --}}
            @if(auth()->user()->isTeacher())
            <div class="pt-3 pb-1 px-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Викладач</span>
            </div>
            <a href="{{ route('teacher.lessons') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('teacher.lessons') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-book-open w-4 text-center"></i> Мої курси та уроки
            </a>
            <a href="{{ route('teacher.students') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('teacher.students*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-users w-4 text-center"></i> Мої учні
            </a>
            <a href="{{ route('schedules.index') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('schedules*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-calendar-days w-4 text-center"></i> Мій розклад
            </a>
            @php $pendingHw = \App\Models\HomeworkSubmission::whereIn('lesson_id',
                \App\Models\Lesson::whereIn('course_id',
                    \App\Models\Course::where('teacher_id', auth()->id())
                        ->orWhereHas('groups', fn($q) => $q->where('teacher_id', auth()->id()))
                        ->pluck('id')
                )->pluck('id')
            )->where('status','submitted')->count(); @endphp
            <a href="{{ route('teacher.homework.index') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('teacher.homework*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-pen-to-square w-4 text-center"></i>
                <span>Домашні завдання</span>
                @if($pendingHw > 0)
                <span class="ml-auto bg-amber-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full flex-shrink-0">{{ $pendingHw }}</span>
                @endif
            </a>
            <a href="{{ route('teacher.availability') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('teacher.availability*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-clock w-4 text-center"></i> Мій розклад доступності
            </a>
            @endif

            {{-- Student --}}
            @if(auth()->user()->isStudent())
            <div class="pt-3 pb-1 px-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Навчання</span>
            </div>
            <a href="{{ route('student.schedule') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('student.schedule') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-calendar-days w-4 text-center"></i> Мій розклад
            </a>
            <a href="{{ route('student.homework') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('student.homework*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-pen-to-square w-4 text-center"></i> Домашні завдання
            </a>
            <a href="{{ route('student.payments') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('student.payments*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-credit-card w-4 text-center"></i> Оплата
            </a>
            <a href="{{ route('student.availability') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('student.availability*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-clock w-4 text-center"></i> Вільні години
            </a>
            @endif

            {{-- Admin + Manager --}}
            @if(auth()->user()->role_id <= 2)
            <div class="pt-3 pb-1 px-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Навчання</span>
            </div>
            <a href="{{ route('groups.index') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('groups*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-users w-4 text-center"></i> Групи
            </a>
            <a href="{{ route('lessons.index') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('lessons*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-chalkboard-teacher w-4 text-center"></i> Уроки
            </a>

            <div class="pt-3 pb-1 px-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Студенти</span>
            </div>
            <a href="{{ route('enrollments.index') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('enrollments*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-user-plus w-4 text-center"></i> Зарахування
            </a>
            <a href="{{ route('payments.index') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('payments*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-credit-card w-4 text-center"></i> Платежі
            </a>

            <div class="pt-3 pb-1 px-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Адміністрація</span>
            </div>
            <a href="{{ route('users.index') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('users*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-user-gear w-4 text-center"></i> Користувачі
            </a>
            <a href="{{ route('schedules.index') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('schedules*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-calendar-days w-4 text-center"></i> Розклад
            </a>

            {{-- Admin only --}}
            @if(auth()->user()->role_id === 1)
            <div class="pt-3 pb-1 px-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Тільки Admin</span>
            </div>
            <a href="{{ route('courses.index') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('courses*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-book w-4 text-center"></i> Курси
            </a>
            <a href="{{ route('tariff-plans.index') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('tariff-plans*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-receipt w-4 text-center"></i> Тарифні плани
            </a>
            <a href="{{ route('settings.zoom') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('settings.zoom*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-video w-4 text-center"></i> Zoom
            </a>
            <a href="{{ route('settings.wayforpay') }}"
               class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 transition {{ request()->routeIs('settings.wayforpay*') ? 'active text-indigo-400' : 'text-slate-300' }}">
                <i class="fa fa-credit-card w-4 text-center"></i> WayForPay
            </a>
            @endif
            @endif
        </nav>

        <div class="p-4 border-t border-slate-700">
            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 rounded-xl px-1 py-1 hover:bg-slate-800 transition group">
                <img src="{{ auth()->user()->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                <div class="overflow-hidden flex-1">
                    <p class="text-sm font-medium text-white truncate group-hover:text-indigo-300 transition">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-slate-400">{{ auth()->user()->role_label }}</p>
                </div>
                <i class="fa fa-pen text-xs text-slate-600 group-hover:text-indigo-400 transition flex-shrink-0"></i>
            </a>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
        <header class="bg-white border-b border-slate-200 px-4 lg:px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <button onclick="openSidebar()" class="lg:hidden text-slate-500 hover:text-slate-800 transition p-1">
                    <i class="fa fa-bars text-lg"></i>
                </button>
                <h1 class="text-lg lg:text-xl font-semibold text-slate-800">@yield('header', 'Дашборд')</h1>
            </div>
            <div class="flex items-center gap-2 lg:gap-4">
                <span class="hidden sm:block text-sm text-slate-400">{{ now()->isoFormat('D MMMM YYYY') }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-red-600 hover:bg-red-50 border border-slate-200 hover:border-red-200 px-3 py-1.5 rounded-xl transition">
                        <i class="fa fa-sign-out-alt text-xs"></i><span class="hidden sm:inline">Вийти</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            @if(session('success'))
            <div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">
                <i class="fa fa-check-circle text-emerald-500"></i>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
                <i class="fa fa-exclamation-circle text-red-500"></i>
                {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
<script>
function openSidebar() {
    document.getElementById('sidebar').classList.remove('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.remove('hidden');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.add('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.add('hidden');
}
</script>
</body>
</html>
