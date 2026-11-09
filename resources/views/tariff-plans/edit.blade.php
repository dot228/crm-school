@extends('layouts.app')
@section('title', 'Редагувати план')
@section('header', 'Тарифні плани')

@section('content')
<div class="max-w-lg">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('tariff-plans.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">Редагувати: {{ $tariffPlan->name }}</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('tariff-plans.update', $tariffPlan) }}" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Назва <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $tariffPlan->name) }}" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Опис</label>
                <textarea name="description" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('description', $tariffPlan->description) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Ціна (₴) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount', $tariffPlan->amount) }}" min="0" step="0.01" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Кількість уроків <span class="text-red-500">*</span></label>
                    <input type="number" name="count_lessons" value="{{ old('count_lessons', $tariffPlan->count_lessons) }}" min="1" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Статус</label>
                <select name="status" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="active"   {{ old('status', $tariffPlan->status) === 'active'   ? 'selected' : '' }}>Активний</option>
                    <option value="inactive" {{ old('status', $tariffPlan->status) === 'inactive' ? 'selected' : '' }}>Неактивний</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition">Зберегти</button>
                <a href="{{ route('tariff-plans.index') }}" class="px-6 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Скасувати</a>
            </div>
        </form>
    </div>
</div>
@endsection
