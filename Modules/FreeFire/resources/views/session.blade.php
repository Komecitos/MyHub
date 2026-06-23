@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/freefire.css') }}">
@endpush

@section('topbar')
<a href="{{ route('freefire.calc') }}" class="btn btn-secondary">Kalkulator</a>
<a href="{{ route('freefire.session') }}" class="btn btn-primary">Sesi Spin</a>
@endsection

@section('content')

<div style="margin-bottom: 1rem;">
    <button onclick="openModal()" class="btn btn-primary">+ Sesi Baru</button>
</div>

{{-- SESI AKTIF --}}
@if($activeSessions->count() > 0)
<h3 class="section-header">Aktif ({{ $activeSessions->count() }})</h3>
@foreach($activeSessions as $session)
<div class="session-card">
    {{-- KOLOM 1: INFO SESI --}}
    <div class="session-col">
        <p class="task-title">{{ $session->item_name }}</p>
        <p class="task-meta" style="margin-bottom: 0.5rem;">
            <span class="badge {{ $session->spin_type === 'token_ring' ? 'badge-info' : ($session->spin_type === 'faded_wheel' ? 'badge-warning' : 'badge-danger') }}">
                {{ $session->spin_type === 'token_ring' ? 'Token Ring' : ($session->spin_type === 'faded_wheel' ? 'Faded Wheel' : 'Token Tower') }}
            </span>
        </p>
        @if($session->event_end)
        <p class="task-meta">📅 Berakhir: {{ \Carbon\Carbon::parse($session->event_end)->translatedFormat('d M Y') }}</p>
        @endif
        @if($session->obtained_items->isNotEmpty())
        <div class="session-obtained-items">
            <span class="session-obtained-items-label">✅ Didapat</span>
            <div class="session-obtained-items-list">
                @foreach($session->obtained_items as $obtainedName)
                {{-- Cari rarity dari slot yang cocok --}}
                @php
                $matchedSlot = $session->slots->first(fn($s) =>
                $s->type === 'item' &&
                strcasecmp(trim($s->item_name ?? ''), trim($obtainedName)) === 0
                );
                $rarity = $matchedSlot?->rarity ?? 'epic';
                @endphp
                <span class="badge badge-{{ $rarity }}">🎁 {{ $obtainedName }}</span>
                @endforeach
            </div>
        </div>
        @endif
        <div class="session-actions">
            <button onclick="openLogModal({{ $session->id }}, '{{ $session->spin_type }}', {{ $session->current_spin }}, {{ $session->discount_percentage > 0 ? 'true' : 'false' }}, '{{ addslashes($session->slots->where('type', 'item')->toJson()) }}', {{ $session->current_token }}, {{ $session->ticket_count }})"
                class="btn btn-secondary btn-sm">+ Spin</button>
            <form action="{{ route('freefire.session.complete', $session->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-primary btn-sm">Selesai</button>
            </form>
            <form action="{{ route('freefire.destroy', $session->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
            </form>
        </div>
    </div>

    {{-- KOLOM 2: STATISTIK --}}
    <div class="session-col session-col-stats">
        <p class="task-meta" style="margin-bottom: 0.5rem; font-weight: 600;">Statistik</p>
        <div class="session-stat">
            <span class="task-meta">💎 Terpakai</span>
            <span class="task-title">{{ $session->spent_diamond }} dm</span>
        </div>
        @if($session->spin_type === 'token_ring')
        <div class="session-stat">
            <span class="task-meta">🪙 Token</span>
            <span class="task-title">{{ $session->current_token }}/5</span>
        </div>
        @elseif($session->spin_type === 'token_tower')
        <div class="session-stat">
            <span class="task-meta">🏆 Token Tower</span>
            <span class="task-title">{{ $session->current_token }}/5</span>
        </div>
        <div class="session-stat">
            <span class="task-meta">🎰 Total Spin</span>
            <span class="task-title">{{ $session->current_spin }}x</span>
        </div>
        @else
        <div class="session-stat">
            <span class="task-meta">🎰 Spin Ke</span>
            <span class="task-title">{{ $session->current_spin }}/8</span>
        </div>
        <div class="session-stat">
            <span class="task-meta">💸 Spin Berikutnya</span>
            <span class="task-title">{{ $session->next_spin_cost }} dm</span>
        </div>
        @endif
    </div>

    {{-- KOLOM 3: PERKIRAAN --}}
    <div class="session-col session-col-estimate">
        <p class="task-meta" style="margin-bottom: 0.5rem; font-weight: 600;">Perkiraan</p>
        @if($session->spin_type === 'token_ring')
        {{-- ... konten token_ring tetap sama ... --}}
        @elseif($session->spin_type === 'token_tower')
        <div class="session-stat">
            <span class="task-meta">🎯 Token Tersisa</span>
            <span class="task-title">{{ $session->remaining_token }}</span>
        </div>
        <div class="session-stat">
            <span class="task-meta">🔄 Sisa Spin (Pity)</span>
            <span class="task-title">~{{ $session->est_spins_left }}x</span>
        </div>
        <div class="session-stat">
            <span class="task-meta">💎 Estimasi Diamond</span>
            <span class="task-title" style="color: var(--accent-primary);">~{{ $session->est_diamond_left }} dm</span>
        </div>
        @else
        <div class="session-stat">
            <span class="task-meta">🎰 Sisa Spin</span>
            <span class="task-title">{{ 8 - $session->current_spin }} spin</span>
        </div>
        <div class="session-stat">
            <span class="task-meta">💎 Sisa Diamond</span>
            <span class="task-title" style="color: var(--accent-primary);">~{{ $session->remaining_faded_cost }} dm</span>
        </div>
        @endif
    </div>
</div>
@endforeach
@else
<p class="empty-state">Belum ada sesi aktif.</p>
@endif

{{-- SESI SELESAI --}}
@if($completedSessions->count() > 0)
<div style="margin-top: 2rem;">
    <h3 class="section-header">Riwayat</h3>
    @foreach($completedSessions as $session)
    <div class="task-card completed">
        <div class="task-card-content">
            <div class="task-text">
                <p class="task-title">{{ $session->item_name }}</p>
                <p class="task-meta">
                    <span class="badge {{ $session->spin_type === 'token_ring' ? 'badge-info' : 'badge-warning' }}">
                        {{ $session->spin_type === 'token_ring' ? 'Token Ring' : 'Faded Wheel' }}
                    </span>
                    · Total: {{ $session->spent_diamond }} dm
                    · {{ $session->current_spin }} spin
                </p>
            </div>
        </div>
        <div class="task-actions">
            <form action="{{ route('freefire.destroy', $session->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- MODAL OVERLAY --}}
<div id="modal-overlay" class="modal-overlay" onclick="closeAllModals()"></div>

{{-- MODAL SESI BARU --}}
<div id="modal-create" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3>Sesi Spin Baru</h3>
        <button onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>
    <form action="{{ route('freefire.session.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Nama Item <span class="required">*</span></label>
            <input type="text" name="item_name" class="form-control" placeholder="cth: Bundle Cobra, Skyler...">
        </div>
        <div class="form-group">
            <label class="form-label">Jenis Spin</label>
            <select name="spin_type" class="form-control" onchange="toggleSpinType(this)">
                <option value="token_ring">Token Ring</option>
                <option value="faded_wheel">Faded Wheel</option>
                <option value="token_tower">Token Tower</option>
            </select>
        </div>
        <div class="form-grid-2" style="margin-top: 1rem;">
            <div>
                <label class="form-label">Tanggal Mulai Event</label>
                <input type="date" name="event_start" class="form-control">
            </div>
            <div>
                <label class="form-label">Tanggal Selesai Event</label>
                <input type="date" name="event_end" class="form-control">
            </div>
        </div>
        <div id="faded-options" style="display:none; margin-top: 1rem;">
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="has_discount" id="create-has-discount" value="1" onchange="previewFadedPrice()">
                    Ada diskon?
                </label>
            </div>

            <div class="calc-result" style="margin-top: 0.75rem;">
                <div class="stat-grid">
                    @php $fadedBasePrices = [9, 19, 39, 69, 99, 199, 399, 799]; @endphp
                    @foreach($fadedBasePrices as $i => $price)
                    <div class="stat-item">
                        <span class="stat-number create-faded-price" data-idx="{{ $i }}">{{ $price }}</span>
                        <span class="stat-label">Spin {{ $i+1 }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="calc-total" style="margin-top: 0.5rem;">
                    <span class="task-meta">Total 8 spin:</span>
                    <span id="create-faded-total" class="stat-number" style="color: var(--accent-primary);">1632 dm</span>
                </div>
            </div>
        </div>

        <div id="tower-options" style="display:none; margin-top: 1rem;">

            <p class="task-meta" style="margin-bottom: 0.75rem;">Harga: 1x = 19dm · 5x = 79dm · Target: 5 Token</p>

            <div class="form-group">
                <label class="form-label">Tingkat Keberuntungan (estimasi awal)
                    <span id="tower-create-luck-label" class="badge badge-info">0%</span>
                </label>
                <input type="range" name="tower_luck" id="tower-create-luck" min="0" max="100" value="0" step="10"
                    oninput="document.getElementById('tower-create-luck-label').textContent = this.value + '%'" class="form-range">
            </div>

            <div class="form-group">
                <label class="form-label">Drop Rate Spin Shard (%)</label>
                <input type="number" name="shard_rate" value="80" min="0" max="100" class="form-control">
            </div>
        </div>

        <div id="token-options" style="margin-top: 1rem;">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Token Awal (Starting Token)</label>
                <input type="number" name="starting_token" class="form-control" placeholder="0" value="0" min="0">
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <label class="form-label" style="margin: 0;">Komposisi Token (jumlah slot di wheel)</label>
                <button type="button" onclick="addItemSlot()" class="btn btn-secondary btn-sm">+ Item Hadiah</button>
            </div>
            <div class="wheel-token-grid">
                @foreach([1, 2, 3, 5, 10, 20, 30, 100] as $i => $val)
                <div class="wheel-token-item">
                    <span class="wheel-token-label">x{{ $val }}</span>
                    <input type="hidden" name="slots[{{ $i }}][type]" value="token">
                    <input type="hidden" name="slots[{{ $i }}][token_value]" value="{{ $val }}">
                    <input type="number"
                        name="slots[{{ $i }}][slot_count]"
                        value="0" min="0"
                        class="form-control wheel-token-input calc-token-input"
                        data-tokenval="{{ $val }}"
                        oninput="updateExpected()">
                </div>
                @endforeach
            </div>
            <div id="wheel-slots-container" style="margin-top: 0.75rem;"></div>
            <p class="task-meta" style="margin-top: 0.4rem; font-size: 0.7rem;">
                Isi "Token Tukar" = berapa token dibutuhkan untuk menukar item itu di toko. Isi "Slot" = berapa banyak item ini muncul di wheel.
            </p>
            <div class="calc-result" style="margin-top: 0.75rem;">
                <p class="task-meta" style="margin-bottom: 0.5rem;">
                    Total bobot: <span id="total-bobot">0</span> ·
                    E(token/spin): <span id="expected-token">0.00</span>
                </p>
                <div id="session-droprate-list"></div>
            </div>
        </div>
        <div class="form-actions">
            <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Batal</button>
            <button type="button" onclick="validateAndSubmitSession()" class="btn btn-primary">Buat Sesi</button>
        </div>
    </form>
</div>

{{-- MODAL LOG SPIN --}}
<div id="modal-log" class="modal modal-sm" aria-hidden="true">
    <div class="modal-header">
        <h3>Catat Spin</h3>
        <button onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>
    <form id="form-log" method="POST">
        @csrf

        {{-- TOGGLE MODE KHUSUS TOWER --}}
        <div id="log-tower-mode-toggle" class="form-group" style="display:none;">
            <label class="form-check">
                <input type="checkbox" id="log-tower-diamond-mode" onchange="toggleTowerMode(this)">
                Saya lupa hitung spin, input diamond saja
            </label>
        </div>

        <div id="log-price-mode-wrapper" class="form-group" style="display:none;">
            <label class="form-label">Mode Harga</label>
            <select id="log-price-mode" class="form-control" onchange="autoCalcDiamond()">
                <option value="normal">Normal</option>
                <option value="discount">Diskon</option>
                <option value="ticket" id="log-ticket-option">Tiket (Gratis)</option>
            </select>
        </div>
        <div id="log-normal-mode" class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Jumlah Spin</label>
                <input type="number" id="log-spin-count" name="spin_count"
                    value="1" min="1" class="form-control" oninput="autoCalcDiamond()">
            </div>
            <div class="form-group">
                <label class="form-label">Diamond Dipakai</label>
                <input type="number" id="log-diamond" name="diamond_spent"
                    class="form-control" readonly style="opacity: 0.7;">
            </div>
        </div>

        <div id="log-diamond-mode" class="form-group" style="display:none;">
            <label class="form-label">Total Diamond yang Dihabiskan</label>
            <input type="number" id="log-diamond-input" min="0" class="form-control"
                placeholder="cth: 700" oninput="calcSpinFromDiamond()">
            <p class="task-meta" style="margin-top: 0.3rem;" id="log-diamond-result"></p>
        </div>

        <div id="log-token-section" class="form-group">
            <label class="form-label">Token Didapat</label>
            <input type="number" name="token_gained" value="0" min="0" class="form-control">
        </div>

        <div id="log-tower-progress" class="form-group" style="display:none;">
            <label class="form-check">
                <input type="checkbox" id="log-tower-token-checkbox" onchange="toggleTowerTokenSelect(this)">
                Dapat Token?
            </label>
            <div id="log-tower-token-select-wrapper" style="display:none; margin-top: 0.5rem;">
                <label class="form-label">Token Berapa?</label>
                <select name="tower_token_number" id="log-tower-token-select" class="form-control">
                    <option value="1">Token 1</option>
                    <option value="2">Token 2</option>
                    <option value="3">Token 3</option>
                    <option value="4">Token 4</option>
                    <option value="5">Token 5</option>
                </select>
            </div>
        </div>

        <div id="log-item-section" class="form-group" style="display:none;">
            <label class="form-label">Dapat Item Langsung?</label>
            <div id="log-item-checkboxes" class="item-checkbox-grid"></div>
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    const tokenBaseWeight = {
        1: 300,
        2: 200,
        3: 150,
        5: 100,
        10: 60,
        20: 30,
        30: 15,
        100: 5
    };
    const fadedBase = [9, 19, 39, 69, 99, 199, 399, 799];
    const fadedDiscounted = [5, 15, 29, 49, 69, 99, 299, 699];

    let slotIndex = 8;
    let currentSpinType = 'token_ring';
    let currentSpinNumber = 0;
    let currentHasDiscount = false;
    let currentTicketCount = 0;


    function gcd(a, b) {
        return b === 0 ? a : gcd(b, a % b);
    }

    function lcm(a, b) {
        return (a * b) / gcd(a, b);
    }

    function spinsToHarga(spins, price1 = 9, price5 = 39) {
        const fiveSpins = Math.floor(spins / 5);
        const oneSpins = spins % 5;
        return (fiveSpins * price5) + (oneSpins * price1);
    }

    function fadedPrice(spinIdx, hasDiscount) {
        if (spinIdx < 0 || spinIdx >= fadedBase.length) return 0;
        return hasDiscount ? fadedDiscounted[spinIdx] : fadedBase[spinIdx];
    }

    function openModal() {
        document.getElementById('modal-create').classList.add('show');
        document.getElementById('modal-overlay').classList.add('show');
        updateExpected();
    }

    function closeAllModals() {
        document.getElementById('modal-create').classList.remove('show');
        document.getElementById('modal-log').classList.remove('show');
        document.getElementById('modal-overlay').classList.remove('show');
    }

    function toggleSpinType(el) {
        const type = el.value;
        document.getElementById('token-options').style.display = type === 'token_ring' ? 'block' : 'none';
        document.getElementById('faded-options').style.display = type === 'faded_wheel' ? 'block' : 'none';
        document.getElementById('tower-options').style.display = type === 'token_tower' ? 'block' : 'none';
    }

    function autoCalcDiamond() {
        const spinCount = parseInt(document.getElementById('log-spin-count').value) || 1;
        const priceMode = document.getElementById('log-price-mode')?.value || 'normal';
        const isDiscount = priceMode === 'discount';
        const isTicket = priceMode === 'ticket';

        if (currentSpinType === 'faded_wheel') {
            let total = 0;
            for (let i = 0; i < spinCount; i++) {
                total += fadedPrice(currentSpinNumber + i, isDiscount);
            }
            document.getElementById('log-diamond').value = total;
        } else if (currentSpinType === 'token_tower') {
            if (isTicket) {
                document.getElementById('log-diamond').value = 0;
            } else {
                const price1 = isDiscount ? 9 : 19;
                const price5 = isDiscount ? 39 : 79;
                document.getElementById('log-diamond').value = spinsToHarga(spinCount, price1, price5);
            }
        } else {
            if (isTicket) {
                document.getElementById('log-diamond').value = 0;
            } else {
                const price1 = isDiscount ? 5 : 9;
                const price5 = isDiscount ? 19 : 39;
                document.getElementById('log-diamond').value = spinsToHarga(spinCount, price1, price5);
            }
        }
    }

    function towerSpinsToHarga(spins) {
        const fiveSpins = Math.floor(spins / 5);
        const oneSpins = spins % 5;
        return (fiveSpins * 79) + (oneSpins * 19);
    }

    function toggleTowerMode(checkbox) {
        const isDiamondMode = checkbox.checked;
        document.getElementById('log-normal-mode').style.display = isDiamondMode ? 'none' : 'flex';
        document.getElementById('log-diamond-mode').style.display = isDiamondMode ? 'block' : 'none';

        if (isDiamondMode) {
            document.getElementById('log-spin-count').removeAttribute('name');
            document.getElementById('log-diamond').removeAttribute('name');
        } else {
            document.getElementById('log-spin-count').setAttribute('name', 'spin_count');
            document.getElementById('log-diamond').setAttribute('name', 'diamond_spent');
        }
    }

    function toggleTowerTokenSelect(checkbox) {
        document.getElementById('log-tower-token-select-wrapper').style.display = checkbox.checked ? 'block' : 'none';
    }

    function calcSpinFromDiamond() {
        const inputDiamond = parseInt(document.getElementById('log-diamond-input').value) || 0;

        // Cari kombinasi terdekat: maksimalkan paket 5x (79dm), sisanya 1x (19dm)
        const fiveSpins = Math.floor(inputDiamond / 79);
        let remainingDm = inputDiamond - (fiveSpins * 79);
        const oneSpins = Math.floor(remainingDm / 19);
        const usedDiamond = (fiveSpins * 79) + (oneSpins * 19);
        const totalSpin = (fiveSpins * 5) + oneSpins;
        const leftover = inputDiamond - usedDiamond;

        document.getElementById('log-diamond-result').innerHTML =
            `Estimasi: <strong>${totalSpin} spin</strong> (${fiveSpins}x paket 5x + ${oneSpins}x paket 1x) ` +
            `= ${usedDiamond}dm digunakan` +
            (leftover > 0 ? ` <span style="color: var(--text-muted);">(sisa ${leftover}dm diabaikan)</span>` : '');

        // Set hidden input untuk submit
        let hiddenSpin = document.getElementById('hidden-spin-count');
        let hiddenDiamond = document.getElementById('hidden-diamond-spent');

        if (!hiddenSpin) {
            hiddenSpin = document.createElement('input');
            hiddenSpin.type = 'hidden';
            hiddenSpin.id = 'hidden-spin-count';
            hiddenSpin.name = 'spin_count';
            document.getElementById('form-log').appendChild(hiddenSpin);
        }
        if (!hiddenDiamond) {
            hiddenDiamond = document.createElement('input');
            hiddenDiamond.type = 'hidden';
            hiddenDiamond.id = 'hidden-diamond-spent';
            hiddenDiamond.name = 'diamond_spent';
            document.getElementById('form-log').appendChild(hiddenDiamond);
        }

        hiddenSpin.value = totalSpin;
        hiddenDiamond.value = usedDiamond;
    }

    function openLogModal(id, spinType, currentSpin, discount, itemsJson, currentTokenLevel = 0, ticketCount = 0) {
        currentSpinType = spinType;
        currentHasDiscount = !!discount;
        currentSpinNumber = currentSpin;
        currentTicketCount = ticketCount;


        document.getElementById('log-spin-count').value = 1;

        const spinCountInput = document.getElementById('log-spin-count');
        spinCountInput.max = spinType === 'faded_wheel' ? 8 : 999;

        document.getElementById('log-tower-diamond-mode').checked = false;
        document.getElementById('log-normal-mode').style.display = 'flex';
        document.getElementById('log-diamond-mode').style.display = 'none';

        document.getElementById('log-price-mode-wrapper').style.display =
            (spinType === 'token_ring' || spinType === 'faded_wheel' || spinType === 'token_tower') ? 'block' : 'none';
        document.getElementById('log-price-mode').value = 'normal';

        document.getElementById('log-ticket-option').style.display =
            (spinType === 'token_ring' || spinType === 'token_tower') ? 'block' : 'none';

        document.getElementById('log-ticket-option').textContent =
            spinType === 'token_tower' ? 'Tiket (Pakai Shard)' : 'Tiket (Gratis)';

        autoCalcDiamond();

        document.getElementById('log-token-section').style.display =
            spinType === 'token_ring' ? 'block' : 'none';

        document.getElementById('log-tower-mode-toggle').style.display =
            spinType === 'token_tower' ? 'block' : 'none';
        document.getElementById('log-tower-progress').style.display =
            spinType === 'token_tower' ? 'block' : 'none';

        const itemSection = document.getElementById('log-item-section');
        const rawItems = JSON.parse(itemsJson || '[]');
        const items = Array.isArray(rawItems) ? rawItems : Object.values(rawItems);

        if (spinType === 'token_ring' && items.length > 0) {
            itemSection.style.display = 'block';
            document.getElementById('log-item-checkboxes').innerHTML = items.map(item => `
            <label class="item-checkbox-label">
                <input type="checkbox" name="got_item_id[]" value="${item.id}">
                <span class="badge badge-${item.rarity}">${item.item_name}</span>
            </label>
        `).join('');
        }
        if (spinType === 'token_tower') {
            document.getElementById('log-tower-token-checkbox').checked = false;
            document.getElementById('log-tower-token-select-wrapper').style.display = 'none';
            document.getElementById('log-tower-token-select').value = Math.min(5, currentTokenLevel + 1);
        } else {
            itemSection.style.display = 'none';
        }

        document.getElementById('form-log').action = '/freefires/session/' + id + '/log';
        document.getElementById('modal-log').classList.add('show');
        document.getElementById('modal-overlay').classList.add('show');
    }

    function addItemSlot() {
        const container = document.getElementById('wheel-slots-container');
        const div = document.createElement('div');
        div.className = 'calc-item-row wheel-slot-row';
        div.dataset.type = 'item';
        div.innerHTML = `
            <input type="hidden" name="slots[${slotIndex}][type]" value="item">
            <input type="text" name="slots[${slotIndex}][item_name]" class="form-control calc-item-name"
                placeholder="Nama item..." style="flex: 1;">
            <select name="slots[${slotIndex}][rarity]" class="form-control calc-item-rarity" style="width: 100px;">
                <option value="epic">Epic</option>
                <option value="legendary">Legendary</option>
                <option value="artifact">Artifact</option>
            </select>
            <input type="number" name="slots[${slotIndex}][token_exchange]" class="form-control calc-item-token"
                placeholder="Token Tukar" min="1" style="width: 90px;" oninput="updateExpected()">
            <input type="number" name="slots[${slotIndex}][slot_count]" class="form-control calc-item-slot"
                placeholder="Slot" value="1" min="1" style="width: 60px;" oninput="updateExpected()">
            <button type="button" onclick="removeSlot(this)" class="btn btn-danger btn-sm">×</button>
        `;
        container.appendChild(div);
        slotIndex++;
        updateExpected();
    }

    function removeSlot(btn) {
        btn.parentElement.remove();
        updateExpected();
    }


    function updateExpected() {
        const priceMode = document.getElementById('token-price-mode')?.value || 'normal';
        const price1 = priceMode === 'discount' ? 5 : 9;
        const price5 = priceMode === 'discount' ? 19 : 39;

        let tokenSlots = [];
        document.querySelectorAll('#token-options .calc-token-input').forEach(input => {
            const count = parseInt(input.value) || 0;
            const val = parseInt(input.dataset.tokenval);
            if (count > 0) {
                tokenSlots.push({
                    val,
                    count,
                    weight: tokenBaseWeight[val] * count
                });
            }
        });

        let itemRows = [];
        document.querySelectorAll('#wheel-slots-container .wheel-slot-row').forEach(row => {
            const name = row.querySelector('.calc-item-name')?.value || 'Item';
            const tokenReq = parseInt(row.querySelector('.calc-item-token')?.value) || 0;
            const slot = parseInt(row.querySelector('.calc-item-slot')?.value) || 0;
            if (tokenReq > 0 && slot > 0) {
                itemRows.push({
                    name,
                    tokenReq,
                    slot
                });
            }
        });

        let konstanta = 1;
        if (itemRows.length > 0) {
            konstanta = itemRows[0].tokenReq;
            for (let i = 1; i < itemRows.length; i++) {
                konstanta = lcm(konstanta, itemRows[i].tokenReq);
            }
        }

        itemRows.forEach(item => {
            item.baseWeight = konstanta / item.tokenReq;
            item.totalWeight = item.baseWeight * item.slot;
        });

        let totalBobot = 0;
        tokenSlots.forEach(t => totalBobot += t.weight);
        itemRows.forEach(i => totalBobot += i.totalWeight);

        let expectedToken = 0;
        tokenSlots.forEach(t => {
            const dropRate = totalBobot > 0 ? t.weight / totalBobot : 0;
            expectedToken += dropRate * t.val;
        });

        document.getElementById('total-bobot').textContent = totalBobot.toFixed(0);
        document.getElementById('expected-token').textContent = expectedToken.toFixed(2);

        let dropRateHtml = '';
        if (tokenSlots.length > 0) {
            dropRateHtml += '<p class="task-meta" style="font-weight:600; margin-bottom:0.3rem;">Drop Rate Token:</p>';
            tokenSlots.forEach(t => {
                const rate = totalBobot > 0 ? (t.weight / totalBobot * 100) : 0;
                dropRateHtml += `
                <div class="session-stat">
                    <span class="task-meta">Token x${t.val}</span>
                    <span class="task-title">${rate.toFixed(1)}%</span>
                </div>`;
            });
        }

        if (itemRows.length > 0) {
            dropRateHtml += '<p class="task-meta" style="font-weight:600; margin-top:0.75rem; margin-bottom:0.3rem;">Item Hadiah:</p>';
            itemRows.forEach(i => {
                const rate = totalBobot > 0 ? (i.totalWeight / totalBobot * 100) : 0;
                const estSpin = expectedToken > 0 ? Math.ceil(i.tokenReq / expectedToken) : 0;
                const estDiamond = spinsToHarga(estSpin, price1, price5);
                dropRateHtml += `
                <div class="session-stat">
                    <span class="task-meta">${i.name}</span>
                    <span class="task-title" style="color: var(--accent-primary);">${rate.toFixed(1)}% · ~${estDiamond}dm (${estSpin}x)</span>
                </div>`;
            });
        }

        document.getElementById('session-droprate-list').innerHTML = dropRateHtml;
    }

    function validateAndSubmitSession() {
        const form = document.querySelector('#modal-create form');
        const itemName = form.querySelector('[name="item_name"]').value.trim();
        const spinType = form.querySelector('[name="spin_type"]').value;

        let errors = [];

        if (!itemName) {
            errors.push('Nama Item wajib diisi.');
        }

        if (spinType === 'token_ring') {
            let hasComposition = false;
            document.querySelectorAll('#token-options .calc-token-input').forEach(input => {
                if (parseInt(input.value) > 0) hasComposition = true;
            });
            document.querySelectorAll('#wheel-slots-container .wheel-slot-row').forEach(row => {
                const slot = parseInt(row.querySelector('.calc-item-slot')?.value) || 0;
                if (slot > 0) hasComposition = true;
            });

            if (!hasComposition) {
                errors.push('Token Ring wajib punya minimal 1 komposisi token atau item hadiah.');
            }
        }

        if (spinType === 'faded_wheel') {
            // Faded Wheel tidak butuh validasi tambahan selain nama item
            // (harga sudah fix, diskon opsional)
        }

        if (spinType === 'token_tower') {
            const shardRate = form.querySelector('[name="shard_rate"]');
            if (shardRate && (parseInt(shardRate.value) < 0 || parseInt(shardRate.value) > 100)) {
                errors.push('Drop Rate Spin Shard harus antara 0-100%.');
            }
        }

        const eventStart = form.querySelector('[name="event_start"]').value;
        const eventEnd = form.querySelector('[name="event_end"]').value;
        if (eventStart && eventEnd && eventStart > eventEnd) {
            errors.push('Tanggal Mulai Event tidak boleh setelah Tanggal Selesai.');
        }

        if (errors.length > 0) {
            showToast(errors.join(' '), 'error');
            return;
        }

        form.submit();
    }

    function previewFadedPrice() {
        const hasDiscount = document.getElementById('create-has-discount').checked;
        let total = 0;

        document.querySelectorAll('.create-faded-price').forEach(el => {
            const idx = parseInt(el.dataset.idx);
            const price = hasDiscount ? fadedDiscounted[idx] : fadedBase[idx];
            el.textContent = price + ' dm';
            total += price;
        });

        document.getElementById('create-faded-total').textContent = total + ' dm';
    }

    function toggleSpinType(el) {
        const type = el.value;
        document.getElementById('token-options').style.display = type === 'token_ring' ? 'block' : 'none';
        document.getElementById('faded-options').style.display = type === 'faded_wheel' ? 'block' : 'none';
        document.getElementById('tower-options').style.display = type === 'token_tower' ? 'block' : 'none';

        if (type === 'faded_wheel') previewFadedPrice();
    }

    document.addEventListener('change', function(e) {
        if (e.target.closest('#wheel-slots-container') || e.target.closest('#token-options .wheel-token-grid')) {
            updateExpected();
        }
    });
</script>
@endpush