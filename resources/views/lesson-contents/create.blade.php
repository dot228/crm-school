@extends('layouts.app')
@section('title', 'Додати контент уроку')
@section('header', 'Уроки')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('lessons.show', $lesson) }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Додати контент</h2>
            <p class="text-sm text-slate-500">{{ $lesson->title }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('lessons.contents.store', $lesson) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Тип контенту <span class="text-red-500">*</span></label>
                    <select name="type" id="type" required onchange="toggleFields(this.value)"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\LessonContent::TYPES as $t)
                        <option value="{{ $t }}" {{ old('type','text') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Порядок</label>
                    <input type="number" name="order" value="{{ old('order', 0) }}" min="0"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Заголовок <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div id="field-body">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Текст контенту</label>
                <textarea name="body" rows="8" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 font-mono">{{ old('body') }}</textarea>
            </div>

            <div id="field-url" class="hidden">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">URL посилання</label>
                <input type="url" name="url" value="{{ old('url') }}" placeholder="https://..."
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div id="field-file" class="hidden">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Файл (макс. 20 МБ)</label>
                <input type="file" name="file"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_published" value="1" id="is_pub" class="rounded" {{ old('is_published', '1') ? 'checked' : '' }}>
                <label for="is_pub" class="text-sm text-slate-700">Опублікований</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition">Зберегти</button>
                <a href="{{ route('lessons.show', $lesson) }}" class="px-6 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Скасувати</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFields(type) {
    document.getElementById('field-body').classList.toggle('hidden', !['text'].includes(type));
    document.getElementById('field-url').classList.toggle('hidden', !['video','link'].includes(type));
    document.getElementById('field-file').classList.toggle('hidden', type !== 'file');
}
toggleFields(document.getElementById('type').value);
</script>
@endsection
