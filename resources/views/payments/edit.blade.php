@extends('layouts.app')
@section('title', 'Редагувати платіж')
@section('header', 'Платежі')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('payments.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">Редагувати платіж #{{ $payment->id }}</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('payments.update', $payment) }}" class="space-y-5">
            @csrf @method('PUT')

            {{-- Тип платежу --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Тип платежу</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_type" value="lessons" class="sr-only peer"
                               {{ old('payment_type', $payment->payment_type ?? 'lessons') === 'lessons' ? 'checked' : '' }}>
                        <span class="flex items-center gap-2 border-2 rounded-xl p-3 text-sm transition
                            peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700
                            border-slate-200 text-slate-600 hover:border-indigo-300">
                            <i class="fa fa-graduation-cap"></i>
                            <span><strong class="block text-xs">Уроки</strong>Баланс уроків</span>
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_type" value="money" class="sr-only peer"
                               {{ old('payment_type', $payment->payment_type ?? 'lessons') === 'money' ? 'checked' : '' }}>
                        <span class="flex items-center gap-2 border-2 rounded-xl p-3 text-sm transition
                            peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700
                            border-slate-200 text-slate-600 hover:border-emerald-300">
                            <i class="fa fa-wallet"></i>
                            <span><strong class="block text-xs">Гроші</strong>Грошовий баланс</span>
                        </span>
                    </label>
                </div>
            </div>

            <div id="edit_lessons_section" class="space-y-4 border border-indigo-100 bg-indigo-50 rounded-2xl p-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Зарахування</label>
                    <select name="enrollment_id" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        <option value="">— Без зарахування —</option>
                        @foreach($enrollments as $enrollment)
                        <option value="{{ $enrollment->id }}" {{ old('enrollment_id', $payment->enrollment_id) == $enrollment->id ? 'selected' : '' }}>
                            {{ $enrollment->student->name }} — {{ $enrollment->group->course->title ?? '?' }} / {{ $enrollment->group->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Кількість уроків</label>
                    <input type="number" name="lessons_count" value="{{ old('lessons_count', $payment->lessons_count) }}" min="1"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Студент <span class="text-red-500">*</span></label>
                    <select name="student_id" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\User::where('role_id', 4)->get() as $student)
                        <option value="{{ $student->id }}" {{ old('student_id', $payment->student_id) == $student->id ? 'selected' : '' }}>{{ $student->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Сума (₴) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount', $payment->amount) }}" min="0.01" step="0.01" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Дата платежу <span class="text-red-500">*</span></label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Метод оплати <span class="text-red-500">*</span></label>
                    <select name="method" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach($methods as $m)
                        <option value="{{ $m }}" {{ old('method', $payment->method) === $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Статус <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\Payment::STATUSES as $s)
                        <option value="{{ $s }}" {{ old('status', $payment->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Примітки</label>
                <textarea name="notes" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('notes', $payment->notes) }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition">Зберегти</button>
                <a href="{{ route('payments.index') }}" class="px-6 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Скасувати</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
(function() {
    const radios  = document.querySelectorAll('input[name="payment_type"]');
    const section = document.getElementById('edit_lessons_section');
    function toggle() {
        const val = document.querySelector('input[name="payment_type"]:checked')?.value;
        if (section) section.classList.toggle('hidden', val === 'money');
    }
    radios.forEach(r => r.addEventListener('change', toggle));
    toggle();
})();
</script>
@endpush
@endsection
