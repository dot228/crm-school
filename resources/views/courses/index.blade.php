@extends('layouts.app')
@section('title', 'Курси')
@section('header', 'Курси')

@section('content')
<div class="flex items-start sm:items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Каталог курсів</h2>
        <p class="text-sm text-slate-500 mt-0.5 hidden sm:block">Управління навчальними курсами</p>
    </div>
    <a href="{{ route('courses.create') }}" class="flex-shrink-0 inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
        <i class="fa fa-plus text-xs"></i><span class="hidden sm:inline">Додати курс</span>
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Статус</label>
            <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі</option>
                @foreach(\App\Models\Course::STATUSES as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Рівень</label>
            <select name="level" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі рівні</option>
                @foreach(\App\Models\Course::LEVELS as $l)
                <option value="{{ $l }}" {{ request('level') === $l ? 'selected' : '' }}>{{ ucfirst($l) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Пошук</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Назва курсу..."
                   class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-full sm:w-52 focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-600 transition">
            <i class="fa fa-search mr-1"></i> Пошук
        </button>
        <a href="{{ route('courses.index') }}" class="text-sm text-slate-500 hover:text-slate-700 px-3 py-2">Скинути</a>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse($courses as $course)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition">
        <div class="h-36 bg-gradient-to-br from-indigo-500 to-violet-600 relative overflow-hidden">
            @if($course->image)
            <img src="{{ $course->image_url }}" class="w-full h-full object-cover" alt="{{ $course->title }}">
            @else
            <div class="absolute inset-0 flex items-center justify-center">
                <i class="fa fa-book text-white/30 text-5xl"></i>
            </div>
            @endif
            @php $lc = ['beginner'=>'bg-emerald-500','intermediate'=>'bg-amber-500','advanced'=>'bg-red-500']; @endphp
            <span class="absolute top-3 right-3 text-xs text-white font-medium px-2.5 py-1 rounded-full {{ $lc[$course->level] ?? 'bg-slate-500' }}">
                {{ ucfirst($course->level) }}
            </span>
        </div>
        <div class="p-5">
            <div class="flex items-start justify-between gap-2 mb-2">
                <h3 class="font-semibold text-slate-800 leading-tight">{{ $course->title }}</h3>
                @php $sc = ['active'=>'bg-emerald-100 text-emerald-700','inactive'=>'bg-slate-100 text-slate-500','archived'=>'bg-orange-100 text-orange-600']; @endphp
                <span class="flex-shrink-0 px-2 py-0.5 rounded-full text-xs {{ $sc[$course->status] ?? '' }}">{{ $course->status }}</span>
            </div>
            @if($course->description)
            <p class="text-xs text-slate-500 mb-3 line-clamp-2">{{ $course->description }}</p>
            @endif
            <div class="flex items-center gap-3 text-xs text-slate-400 mb-4">
                <span><i class="fa fa-clock mr-1"></i>{{ $course->duration_hours }}г</span>
                <span><i class="fa fa-users mr-1"></i>{{ $course->groups->count() }} груп</span>
                <span class="ml-auto font-semibold text-indigo-600 text-sm">{{ number_format($course->price, 0) }} ₴</span>
            </div>
            @if($course->teacher)
            <div class="flex items-center gap-2 mb-4 pb-4 border-b border-slate-100">
                <img src="{{ $course->teacher->avatar_url }}" class="w-6 h-6 rounded-full object-cover" alt="">
                <span class="text-xs text-slate-500">{{ $course->teacher->name }}</span>
            </div>
            @endif
            <div class="flex items-center gap-2">
                <a href="{{ route('courses.show', $course) }}" class="flex-1 text-center bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-medium py-2 rounded-lg transition">
                    Деталі
                </a>
                <a href="{{ route('courses.edit', $course) }}" class="flex-1 text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-medium py-2 rounded-lg transition">
                    Редагувати
                </a>
                <form method="POST" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('Видалити курс?')">
                    @csrf @method('DELETE')
                    <button class="p-2 text-slate-400 hover:text-red-500 transition"><i class="fa fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-16 text-slate-400">
        <i class="fa fa-book text-4xl mb-3 block"></i>
        <p>Курси не знайдено</p>
    </div>
    @endforelse
</div>
@if($courses->hasPages())
<div class="mt-6">{{ $courses->withQueryString()->links() }}</div>
@endif
@endsection
