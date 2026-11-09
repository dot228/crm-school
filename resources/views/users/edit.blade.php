@extends('layouts.app')
@section('title', 'Редагувати користувача')
@section('header', 'Користувачі')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('users.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="fa fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-slate-800">Редагувати: {{ $user->name }}</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div class="flex items-center gap-4 mb-2">
                <img src="{{ $user->avatar_url }}" class="w-14 h-14 rounded-full object-cover" alt="">
                <div>
                    <p class="font-medium text-slate-800">{{ $user->name }}</p>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Ім'я <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Телефон</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Новий пароль</label>
                    <input type="password" name="password"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Залиште порожнім">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Підтвердити пароль</label>
                    <input type="password" name="password_confirmation"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Роль <span class="text-red-500">*</span></label>
                    <select name="role_id" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach($roles as $id => $label)
                        <option value="{{ $id }}" {{ old('role_id', $user->role_id) == $id ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Статус <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\User::STATUSES as $s)
                        <option value="{{ $s }}" {{ old('status', $user->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Аватар</label>
                    <input type="file" name="avatar_file" accept="image/*"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:text-xs">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Про себе</label>
                    <textarea name="bio" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('bio', $user->bio) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Дата народження</label>
                    <input type="date" name="date_of_birth" id="dob_edit"
                           value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                           max="{{ date('Y-m-d') }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            {{-- Guardian section --}}
            <div id="guardian_section_edit"
                 class="{{ ($user->isStudent() && !$user->isAdult()) ? '' : 'hidden' }} border border-amber-200 bg-amber-50 rounded-2xl p-5 space-y-4">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa fa-user-shield text-amber-600"></i>
                    <h4 class="text-sm font-semibold text-amber-800">Відповідальна особа (батьки / опікун)</h4>
                    <span class="text-xs text-amber-600">Учню менше 18 років</span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Ім’я <span class="text-red-500">*</span></label>
                        <input type="text" name="guardian_name" value="{{ old('guardian_name', $user->guardian_name) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        @error('guardian_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Ступінь (мати, батько, опікун)</label>
                        <input type="text" name="guardian_relation" value="{{ old('guardian_relation', $user->guardian_relation) }}"
                               placeholder="напр. Мати"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Телефон</label>
                        <input type="text" name="guardian_phone" value="{{ old('guardian_phone', $user->guardian_phone) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Email</label>
                        <input type="email" name="guardian_email" value="{{ old('guardian_email', $user->guardian_email) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition">Зберегти</button>
                <a href="{{ route('users.index') }}" class="px-6 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Скасувати</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
(function() {
    const roleSelect = document.querySelector('select[name="role_id"]');
    const dobInput   = document.getElementById('dob_edit');
    const section    = document.getElementById('guardian_section_edit');

    function calcAge(val) {
        if (!val) return null;
        const dob = new Date(val);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
        return age;
    }

    function toggle() {
        const isStudent = roleSelect ? roleSelect.value == 4 : section !== null;
        if (!isStudent) { section.classList.add('hidden'); return; }
        const age = calcAge(dobInput ? dobInput.value : null);
        section.classList.toggle('hidden', age === null || age >= 18);
    }

    if (roleSelect) roleSelect.addEventListener('change', toggle);
    if (dobInput)   dobInput.addEventListener('change', toggle);
})();
</script>
@endpush
@endsection
