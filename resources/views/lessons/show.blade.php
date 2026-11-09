@extends('layouts.app')
@section('title', $lesson->title)
@section('header', 'Уроки')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('lessons.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">{{ $lesson->title }}</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-5">
        <div class="flex items-start justify-between gap-4 mb-5">
            <div>
                <h3 class="text-xl font-bold text-slate-800">{{ $lesson->title }}</h3>
                @if($lesson->description)<p class="text-slate-500 mt-1 text-sm">{{ $lesson->description }}</p>@endif
            </div>
            <div class="flex items-center gap-2">
                @php $sc = ['scheduled'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-600']; @endphp
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $sc[$lesson->status] ?? '' }}">{{ ucfirst($lesson->status) }}</span>
                <a href="{{ route('lessons.edit', $lesson) }}" class="inline-flex items-center gap-2 border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition">
                    <i class="fa fa-pen text-xs"></i> Редагувати
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4 border-y border-slate-100 mb-5">
            <div>
                <p class="text-xs text-slate-400 mb-1">Курс</p>
                <p class="text-sm font-medium text-slate-700">{{ $lesson->course->title ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-1">Група</p>
                <p class="text-sm font-medium text-slate-700">{{ $lesson->group->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-1">Дата</p>
                <p class="text-sm font-medium text-slate-700">{{ $lesson->scheduled_at?->format('d.m.Y H:i') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-1">Тривалість</p>
                <p class="text-sm font-medium text-slate-700">{{ $lesson->duration_minutes }} хв</p>
            </div>
        </div>

        @if($lesson->zoom_link)
        <a href="{{ $lesson->zoom_link }}" target="_blank"
           class="inline-flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2.5 rounded-xl text-sm font-medium transition mb-4">
            <i class="fa fa-video"></i> Приєднатись до Zoom
        </a>
        @endif

        @if($lesson->homework)
        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
            <p class="text-xs font-semibold text-amber-700 mb-1.5"><i class="fa fa-book-open mr-1"></i>Домашнє завдання</p>
            <p class="text-sm text-amber-800">{{ $lesson->homework }}</p>
        </div>
        @endif
    </div>

    {{-- Lesson Contents --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-sm font-semibold text-slate-700">Контент уроку</h4>
            @if(auth()->user()->role_id <= 2)
            <a href="{{ route('lessons.contents.create', $lesson) }}"
               class="inline-flex items-center gap-1.5 text-xs text-indigo-500 hover:text-indigo-700 border border-indigo-200 hover:border-indigo-400 px-3 py-1.5 rounded-lg transition">
                <i class="fa fa-plus text-xs"></i> Додати контент
            </a>
            @endif
        </div>

        @php $typeIcons = ['text'=>'fa-align-left','video'=>'fa-play-circle','file'=>'fa-paperclip','link'=>'fa-link']; @endphp
        @forelse($lesson->contents as $content)
        <div class="flex items-start gap-3 py-3 border-b border-slate-50 last:border-0">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $content->is_published ? 'bg-indigo-100' : 'bg-slate-100' }}">
                <i class="fa {{ $typeIcons[$content->type] ?? 'fa-file' }} text-xs {{ $content->is_published ? 'text-indigo-500' : 'text-slate-400' }}"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <p class="text-sm font-medium text-slate-800">{{ $content->title }}</p>
                    @if(!$content->is_published)
                    <span class="text-xs text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">чернетка</span>
                    @endif
                </div>
                @if($content->type === 'text' && $content->body)
                <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ Str::limit(strip_tags($content->body), 100) }}</p>
                @elseif(in_array($content->type, ['video','link']) && $content->url)
                <a href="{{ $content->url }}" target="_blank" class="text-xs text-indigo-500 hover:underline mt-0.5 block truncate">{{ $content->url }}</a>
                @elseif($content->type === 'file' && $content->file_path)
                <a href="{{ asset('storage/'.$content->file_path) }}" target="_blank" class="text-xs text-indigo-500 hover:underline mt-0.5 block">Завантажити файл</a>
                @endif
            </div>
            @if(auth()->user()->role_id <= 2)
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('lessons.contents.edit', [$lesson, $content]) }}" class="text-slate-400 hover:text-amber-500 transition"><i class="fa fa-pen text-xs"></i></a>
                <form method="POST" action="{{ route('lessons.contents.destroy', [$lesson, $content]) }}" onsubmit="return confirm('Видалити контент?')">
                    @csrf @method('DELETE')
                    <button class="text-slate-400 hover:text-red-500 transition"><i class="fa fa-trash text-xs"></i></button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <p class="text-sm text-slate-400 text-center py-6">Контент ще не додано</p>
        @endforelse
    </div>
</div>
@endsection
