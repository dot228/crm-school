@extends('layouts.app')
@section('title', 'Користувачі')
@section('header', 'Користувачі')

@section('content')
<div class="flex items-start sm:items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Список користувачів</h2>
        <p class="text-sm text-slate-500 mt-0.5 hidden sm:block">Управління адміністраторами, менеджерами, викладачами та студентами</p>
    </div>
    <a href="{{ route('users.create') }}"
       class="flex-shrink-0 inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
        <i class="fa fa-plus text-xs"></i><span class="hidden sm:inline">Додати</span>
    </a>
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Роль</label>
            <select name="role_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі ролі</option>
                @foreach(\App\Models\User::ROLES as $id => $label)
                <option value="{{ $id }}" {{ request('role_id') == $id ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Статус</label>
            <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Всі статуси</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Пошук</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ім'я, email, телефон..."
                   class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-full sm:w-56 focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-600 transition">
            <i class="fa fa-search mr-1"></i> Пошук
        </button>
        <a href="{{ route('users.index') }}" class="text-sm text-slate-500 hover:text-slate-700 px-3 py-2">Скинути</a>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[700px]">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Користувач</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Роль</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Телефон</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Статус</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Дата</th>
                <th class="px-4 py-3.5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($users as $user)
            <tr class="hover:bg-slate-50 transition">
                <td class="px-6 py-3.5">
                    <div class="flex items-center gap-3">
                        <img src="{{ $user->avatar_url }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">
                        <div>
                            <a href="{{ route('users.show', $user) }}">
                                <p class="font-medium text-slate-800">{{ $user->name }}</p>
                            </a>
                            <p class="text-xs text-slate-400">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3.5">
                    @php
                        $roleColors = [1=>'bg-red-100 text-red-700', 2=>'bg-orange-100 text-orange-700', 3=>'bg-violet-100 text-violet-700', 4=>'bg-blue-100 text-blue-700'];
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $roleColors[$user->role_id] ?? 'bg-slate-100 text-slate-600' }}">
                        {{ $user->role_label }}
                    </span>
                </td>
                <td class="px-4 py-3.5 text-slate-500">@if($user->phone)<a href="tel:{{ $user->phone }}" class="hover:text-indigo-600 transition">{{ $user->phone }}</a>@else—@endif</td>
                <td class="px-4 py-3.5">
                    @php $sc = ['active'=>'bg-emerald-100 text-emerald-700','inactive'=>'bg-slate-100 text-slate-500','blocked'=>'bg-red-100 text-red-600']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$user->status] ?? '' }}">{{ $user->status }}</span>
                </td>
                <td class="px-4 py-3.5 text-slate-400 text-xs">{{ $user->created_at->format('d.m.Y') }}</td>
                <td class="px-4 py-3.5">
                    <div class="flex items-center gap-2 justify-end">
                        @php $canManage = auth()->user()->isAdmin() || $user->role_id >= 3; @endphp
                        @if($canManage)
                        <a href="{{ route('users.edit', $user) }}" class="text-slate-400 hover:text-amber-500 transition"><i class="fa fa-pen"></i></a>
                        @if(auth()->id() !== $user->id)
                        <form method="POST" action="{{ route('users.block', $user->id) }}">
                            @csrf
                            <button title="{{ $user->status === 'blocked' ? 'Розблокувати' : 'Заблокувати' }}"
                                    class="text-slate-400 {{ $user->status === 'blocked' ? 'hover:text-emerald-500' : 'hover:text-orange-500' }} transition">
                                <i class="fa {{ $user->status === 'blocked' ? 'fa-unlock' : 'fa-ban' }} text-xs"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Видалити користувача?')">
                            @csrf @method('DELETE')
                            <button class="text-slate-400 hover:text-red-500 transition"><i class="fa fa-trash"></i></button>
                        </form>
                        @endif
                        @else
                        <span class="text-xs text-slate-300 italic">—</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-slate-400 py-12">Користувачів не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">{{ $users->withQueryString()->links() }}</div>
    @endif
    </div>
</div>
@endsection
