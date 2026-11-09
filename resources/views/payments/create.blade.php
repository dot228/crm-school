@extends('layouts.app')
@section('title', 'Новий платіж')
@section('header', 'Платежі')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('payments.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">Новий платіж</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('payments.store') }}" class="space-y-5">
            @csrf

            {{-- Тип платежу --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Тип платежу <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_type" value="lessons" class="sr-only peer" {{ old('payment_type','lessons') === 'lessons' ? 'checked' : '' }}>
                        <span class="flex items-center gap-2 border-2 rounded-xl p-3 text-sm transition
                            peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700
                            border-slate-200 text-slate-600 hover:border-indigo-300">
                            <i class="fa fa-graduation-cap text-base"></i>
                            <span><strong class="block text-xs">Уроки</strong>Поповнити баланс уроків</span>
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_type" value="money" class="sr-only peer" {{ old('payment_type') === 'money' ? 'checked' : '' }}>
                        <span class="flex items-center gap-2 border-2 rounded-xl p-3 text-sm transition
                            peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700
                            border-slate-200 text-slate-600 hover:border-emerald-300">
                            <i class="fa fa-wallet text-base"></i>
                            <span><strong class="block text-xs">Гроші</strong>Поповнити грошовий баланс</span>
                        </span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Студент <span class="text-red-500">*</span></label>
                <select name="student_id" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">— Оберіть студента —</option>
                    @foreach($students as $student)
                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                        {{ $student->name }} ({{ $student->email }})
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Lessons section --}}
            <div id="lessons_section" class="space-y-4 border border-indigo-100 bg-indigo-50 rounded-2xl p-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Зарахування</label>
                    <select name="enrollment_id" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        <option value="">— Оберіть зарахування —</option>
                        @foreach($enrollments as $enrollment)
                        <option value="{{ $enrollment->id }}" {{ old('enrollment_id') == $enrollment->id ? 'selected' : '' }}>
                            {{ $enrollment->student->name }} — {{ $enrollment->group->course->title ?? '?' }} / {{ $enrollment->group->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @if(isset($tariffPlans) && $tariffPlans->count())
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Тарифний план <span class="text-xs text-slate-400">(авто-заповнить суму і кількість уроків)</span></label>
                    <select name="tariff_plan_id" id="tariff_select" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        <option value="">— Без тарифу (довільна сума) —</option>
                        @foreach($tariffPlans as $tp)
                        <option value="{{ $tp->id }}"
                                data-amount="{{ $tp->amount }}"
                                data-lessons="{{ $tp->count_lessons }}"
                                {{ old('tariff_plan_id') == $tp->id ? 'selected' : '' }}>
                            {{ $tp->name }} — {{ number_format($tp->amount,0) }} ₴ / {{ $tp->count_lessons }} ур.
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Кількість уроків</label>
                    <input type="number" name="lessons_count" id="lessons_count_input" value="{{ old('lessons_count') }}" min="1"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                    <p class="text-xs text-slate-400 mt-1">Залиште порожнім — буде взято з тарифного плану</p>
                </div>
            </div>

            {{-- Amount + common --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Сума (₴) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" id="amount_input" value="{{ old('amount') }}" min="0.01" step="0.01" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Дата платежу <span class="text-red-500">*</span></label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Метод оплати <span class="text-red-500">*</span></label>
                    <select name="method" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach($methods as $m)
                        <option value="{{ $m }}" {{ old('method', 'cash') === $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Статус <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\Payment::STATUSES as $s)
                        <option value="{{ $s }}" {{ old('status', 'paid') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Примітки</label>
                <textarea name="notes" rows="2" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('notes') }}</textarea>
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
    const radios   = document.querySelectorAll('input[name="payment_type"]');
    const section  = document.getElementById('lessons_section');
    const tariff   = document.getElementById('tariff_select');
    const amountIn = document.getElementById('amount_input');
    const lessonsIn= document.getElementById('lessons_count_input');

    function toggleSection() {
        const val = document.querySelector('input[name="payment_type"]:checked')?.value;
        if (section) section.classList.toggle('hidden', val === 'money');
    }

    radios.forEach(r => r.addEventListener('change', toggleSection));
    toggleSection();

    if (tariff) {
        tariff.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const amt = opt.getAttribute('data-amount');
            const les = opt.getAttribute('data-lessons');
            if (amt && amountIn) amountIn.value = amt;
            if (les && lessonsIn) lessonsIn.value = les;
        });
    }
})();
</script>
@endpush
@endsection
