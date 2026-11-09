@extends('layouts.app')
@section('title', 'Мої вільні години')
@section('header', 'Вільні години')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Вільні години по днях тижня</h2>
            <p class="text-xs text-slate-400 mt-0.5">Вкажіть інтервали, коли ви можете займатись. Можна додати кілька інтервалів на день.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-sm text-emerald-700 flex items-center gap-2">
        <i class="fa fa-circle-check flex-shrink-0"></i> {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
        @foreach($errors->all() as $e)<p><i class="fa fa-circle-exclamation mr-1"></i>{{ $e }}</p>@endforeach
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 divide-y divide-slate-50">
        <form method="POST" action="{{ route('student.availability.store') }}">
            @csrf

            @foreach($days as $dayNum => $dayName)
            @php $daySlots = $availabilities->get($dayNum, collect()); @endphp
            <div class="px-6 py-4" id="day-block-{{ $dayNum }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-slate-700 w-28">{{ $dayName }}</span>
                    <button type="button"
                            onclick="addSlot({{ $dayNum }})"
                            class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-700 font-medium transition">
                        <i class="fa fa-plus text-xs"></i> Додати інтервал
                    </button>
                </div>

                <div class="space-y-2" id="slots-{{ $dayNum }}">
                    @forelse($daySlots as $slotIndex => $slot)
                    <div class="slot-row flex items-center gap-2">
                        <input type="time"
                               name="slots[{{ $dayNum }}][{{ $slotIndex }}][start_time]"
                               value="{{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('H:i') }}"
                               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-32">
                        <span class="text-slate-400 text-sm font-medium">—</span>
                        <input type="time"
                               name="slots[{{ $dayNum }}][{{ $slotIndex }}][end_time]"
                               value="{{ \Illuminate\Support\Carbon::parse($slot->end_time)->format('H:i') }}"
                               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-32">
                        <button type="button" onclick="removeSlot(this)"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition flex-shrink-0">
                            <i class="fa fa-xmark text-sm"></i>
                        </button>
                    </div>
                    @empty
                    <p class="text-xs text-slate-300 italic" id="empty-{{ $dayNum }}">Не вказано — натисніть «Додати інтервал»</p>
                    @endforelse
                </div>
            </div>
            @endforeach

            <div class="px-6 py-5">
                <button type="submit"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition shadow-sm">
                    <i class="fa fa-floppy-disk mr-1.5 text-xs"></i>Зберегти
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const slotCounts = {};

function addSlot(dayNum) {
    const container = document.getElementById('slots-' + dayNum);

    const empty = document.getElementById('empty-' + dayNum);
    if (empty) empty.remove();

    const idx = slotCounts[dayNum] = (slotCounts[dayNum] ?? container.querySelectorAll('.slot-row').length);
    slotCounts[dayNum]++;

    const row = document.createElement('div');
    row.className = 'slot-row flex items-center gap-2';
    row.innerHTML = `
        <input type="time"
               name="slots[${dayNum}][${idx}][start_time]"
               value="09:00"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-32">
        <span class="text-slate-400 text-sm font-medium">—</span>
        <input type="time"
               name="slots[${dayNum}][${idx}][end_time]"
               value="17:00"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-32">
        <button type="button" onclick="removeSlot(this)"
                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition flex-shrink-0">
            <i class="fa fa-xmark text-sm"></i>
        </button>
    `;
    container.appendChild(row);
}

function removeSlot(btn) {
    btn.closest('.slot-row').remove();
}
</script>
@endpush
@endsection
