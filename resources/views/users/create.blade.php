@extends('layouts.app')
@section('title', 'Новий користувач')
@section('header', 'Користувачі')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('users.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <i class="fa fa-arrow-left"></i>
        </a>
        <h2 class="text-lg font-semibold text-slate-800">Новий користувач</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-5">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Ім'я <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Телефон</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Пароль <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Підтвердити пароль <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Роль <span class="text-red-500">*</span></label>
                    <select name="role_id" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach($roles as $id => $label)
                        <option value="{{ $id }}" {{ old('role_id', 4) == $id ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Статус <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="blocked" {{ old('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Аватар</label>
                    <input type="file" name="avatar_file" accept="image/*"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:text-xs">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Про себе</label>
                    <textarea name="bio" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('bio') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Дата народження</label>
                    <input type="date" name="date_of_birth" id="dob_create" value="{{ old('date_of_birth') }}"
                           max="{{ date('Y-m-d') }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            {{-- Guardian section --}}
            <div id="guardian_section" class="hidden border border-amber-200 bg-amber-50 rounded-2xl p-5 space-y-4">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa fa-user-shield text-amber-600"></i>
                    <h4 class="text-sm font-semibold text-amber-800">Відповідальна особа (батьки / опікун)</h4>
                    <span class="text-xs text-amber-600">Учню менше 18 років</span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Ім’я <span class="text-red-500">*</span></label>
                        <input type="text" name="guardian_name" value="{{ old('guardian_name') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        @error('guardian_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Ступінь (мати, батько, опікун)</label>
                        <input type="text" name="guardian_relation" value="{{ old('guardian_relation') }}"
                               placeholder="напр. Мати"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Телефон</label>
                        <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Email</label>
                        <input type="email" name="guardian_email" value="{{ old('guardian_email') }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition">
                    Зберегти
                </button>
                <a href="{{ route('users.index') }}" class="px-6 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Скасувати</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
(function() {
    const roleSelect = document.querySelector('select[name="role_id"]');
    const dobInput   = document.getElementById('dob_create');
    const section    = document.getElementById('guardian_section');

    function toggle() {
        const isStudent = roleSelect && roleSelect.value == 4;
        if (!isStudent) { section.classList.add('hidden'); return; }
        if (!dobInput || !dobInput.value) { section.classList.add('hidden'); return; }
        const dob  = new Date(dobInput.value);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
        section.classList.toggle('hidden', age >= 18);
    }

    if (roleSelect) roleSelect.addEventListener('change', toggle);
    if (dobInput)   dobInput.addEventListener('change', toggle);
    toggle();
})();
</script>
@endpush
@endsection
