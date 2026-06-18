@extends('layouts.app')

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
            <span class="badge {{ $session->spin_type === 'token_ring' ? 'badge-info' : 'badge-warning' }}">
                {{ $session->spin_type === 'token_ring' ? 'Token Ring' : 'Faded Wheel' }}
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
            <button onclick="openLogModal({{ $session->id }}, '{{ $session->spin_type }}', {{ $session->current_spin }}, {{ $session->discount_percentage }}, '{{ addslashes($session->slots->where('type', 'item')->toJson()) }}')"
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
            <span class="task-title">
                {{ $session->current_token }}@if($session->token_target)/{{ $session->token_target }}@endif token
                @if($session->starting_token > 0)
                <span class="task-meta" style="display: block; font-size: 0.75rem; font-weight: normal; margin-top: 0.1rem;">(awal: {{ $session->starting_token }})</span>
                @endif
            </span>
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
        <div class="session-stat">
            <span class="task-meta">E(token/spin)</span>
            <span class="task-title">
                {{ number_format($session->expected_token_per_spin, 2) }}
                @if($session->avg_token_per_spin)
                <span class="task-meta">· aktual {{ number_format($session->avg_token_per_spin, 2) }}</span>
                @endif
            </span>
        </div>
        <div class="session-stat">
            <span class="task-meta">🍀 Luck Aktual</span>
            <span class="task-title">
                @if($session->luck_actual === null)
                <span class="badge badge-secondary">Belum ada data</span>
                @elseif($session->luck_actual >= 70)
                <span class="badge badge-success">{{ $session->luck_actual }}% Beruntung</span>
                @elseif($session->luck_actual >= 40)
                <span class="badge badge-warning">{{ $session->luck_actual }}% Normal</span>
                @else
                <span class="badge badge-danger">{{ $session->luck_actual }}% Sial</span>
                @endif
            </span>
        </div>
        @if(count($session->item_estimates) > 0)
        <p class="task-meta" style="font-weight: 600; margin-top: 0.5rem; margin-bottom: 0.25rem;">Item Hadiah</p>
        @foreach($session->item_estimates as $item)
        <div class="session-item-estimate{{ $item['is_target'] ? ' is-target' : '' }}">
            <div class="session-item-estimate-head">
                <span class="badge badge-{{ $item['rarity'] }}">{{ $item['name'] }}</span>
                @if($item['is_target'])
                <span class="badge badge-info" style="font-size: 0.65rem;">Target</span>
                @endif
            </div>
            <div class="session-stat" style="padding-left: 0; padding-right: 0;">
                <span class="task-meta">{{ $item['drop_rate'] }}% · {{ $item['token_exchange'] }} token</span>
                <span class="task-title" style="color: var(--accent-primary);">
                    @if($session->current_spin > 0)
                        @if($item['remaining_token'] > 0)
                        ~{{ $item['est_diamond_left'] }}dm ({{ $item['est_spins_left'] }}x)
                        @else
                        <span class="badge badge-success">Cukup</span>
                        @endif
                    @else
                        ~{{ $item['theoretical_diamond'] }}dm ({{ $item['theoretical_spins'] }}x)
                    @endif
                </span>
            </div>
            @if($session->current_spin > 0)
            <p class="task-meta" style="font-size: 0.7rem; margin-top: 0.1rem;">
                Token: {{ $session->current_token }}/{{ $item['token_exchange'] }}
                @if($item['remaining_token'] > 0)
                · kurang {{ $item['remaining_token'] }}
                @endif
            </p>
            @endif
        </div>
        @endforeach
        @else
        <p class="task-meta" style="margin-top: 0.5rem;">Belum ada item hadiah di wheel.</p>
        @endif
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
            <p class="task-meta" style="margin-bottom: 0.75rem;">Harga normal: 9 · 19 · 39 · 69 · 99 · 199 · 399 · 799</p>
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="has_discount" value="1">
                    Ada diskon 20%?
                </label>
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
            <button type="submit" class="btn btn-primary">Buat Sesi</button>
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
        <div class="form-grid-2">
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
        <div id="log-token-section" class="form-group">
            <label class="form-label">Token Didapat</label>
            <input type="number" name="token_gained" value="0" min="0" class="form-control">
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

    let slotIndex = 8;
    let currentSpinType = 'token_ring';
    let currentDiscount = 0;
    let currentSpinNumber = 0;

    function gcd(a, b) {
        return b === 0 ? a : gcd(b, a % b);
    }

    function lcm(a, b) {
        return (a * b) / gcd(a, b);
    }

    function spinsToHarga(spins) {
        const fiveSpins = Math.floor(spins / 5);
        const oneSpins = spins % 5;
        return (fiveSpins * 39) + (oneSpins * 9);
    }

    function fadedPrice(spinIdx, discount) {
        if (spinIdx < 0 || spinIdx >= fadedBase.length) return 0;
        return Math.ceil(fadedBase[spinIdx] * (1 - discount / 100));
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
        const isToken = el.value === 'token_ring';
        document.getElementById('token-options').style.display = isToken ? 'block' : 'none';
        document.getElementById('faded-options').style.display = isToken ? 'none' : 'block';
    }

    function autoCalcDiamond() {
        const spinCount = parseInt(document.getElementById('log-spin-count').value) || 1;

        if (currentSpinType === 'faded_wheel') {
            let total = 0;
            for (let i = 0; i < spinCount; i++) {
                total += fadedPrice(currentSpinNumber + i, currentDiscount);
            }
            document.getElementById('log-diamond').value = total;
        } else {
            document.getElementById('log-diamond').value = spinsToHarga(spinCount);
        }
    }

    function openLogModal(id, spinType, currentSpin, discount, itemsJson) {
        currentSpinType = spinType;
        currentDiscount = discount;
        currentSpinNumber = currentSpin;

        document.getElementById('log-spin-count').value = 1;
        autoCalcDiamond();

        document.getElementById('log-token-section').style.display =
            spinType === 'token_ring' ? 'block' : 'none';

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
                const estDiamond = spinsToHarga(estSpin);
                dropRateHtml += `
                    <div class="session-stat">
                        <span class="task-meta">${i.name}</span>
                        <span class="task-title" style="color: var(--accent-primary);">${rate.toFixed(1)}% · ~${estDiamond}dm (${estSpin}x)</span>
                    </div>`;
            });
        }

        document.getElementById('session-droprate-list').innerHTML = dropRateHtml;
    }

    document.addEventListener('change', function(e) {
        if (e.target.closest('#wheel-slots-container') || e.target.closest('#token-options .wheel-token-grid')) {
            updateExpected();
        }
    });
</script>
@endpush