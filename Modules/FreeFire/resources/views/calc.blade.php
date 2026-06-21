@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/freefire.css') }}">
@endpush

@section('topbar')
<a href="{{ route('freefire.calc') }}" class="btn btn-primary">Kalkulator</a>
<a href="{{ route('freefire.session') }}" class="btn btn-secondary">Sesi Spin</a>
@endsection

@section('content')

<div class="calc-wrapper">
    <div class="calc-layout">

        {{-- PILIH JENIS SPIN --}}
        <div class="calc-tabs">
            <button class="btn btn-primary tab-btn active" onclick="switchTab('faded')">Faded Wheel</button>
            <button class="btn btn-secondary tab-btn" onclick="switchTab('token')">Token Ring</button>
            <button class="btn btn-secondary tab-btn" onclick="switchTab('tower')">Token Tower</button>
        </div>

        {{-- FADED WHEEL --}}
        <div id="tab-faded" class="calc-section">
            <div class="widget-card">
                <h4 class="widget-title">Faded Wheel</h4>
                <p class="task-meta" style="margin-bottom: 1rem;">Harga normal: 9 · 19 · 39 · 69 · 99 · 199 · 399 · 799</p>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" id="faded-has-discount" onchange="calcFaded()">
                        Ada diskon?
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">Spin sampai ke-</label>
                    <select id="faded-target" class="form-control" onchange="calcFaded()">
                        <option value="1">Spin 1</option>
                        <option value="2">Spin 2</option>
                        <option value="3">Spin 3</option>
                        <option value="4">Spin 4</option>
                        <option value="5">Spin 5</option>
                        <option value="6">Spin 6</option>
                        <option value="7">Spin 7</option>
                        <option value="8" selected>Spin 8 (Item Utama)</option>
                    </select>
                </div>

                <div class="calc-result">
                    <div class="stat-grid">
                        @php $fadedPrices = [9, 19, 39, 69, 99, 199, 399, 799]; @endphp
                        @foreach($fadedPrices as $i => $price)
                        <div class="stat-item">
                            <span class="stat-number faded-price" data-base="{{ $price }}" data-spin="{{ $i+1 }}">{{ $price }}</span>
                            <span class="stat-label">Spin {{ $i+1 }}</span>
                        </div>
                        @endforeach
                    </div>
                    <div class="calc-total">
                        <span class="task-meta">Total sampai spin yang dipilih:</span>
                        <span id="faded-total" class="stat-number" style="color: var(--accent-primary);">1632 dm</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOKEN RING --}}
        <div id="tab-token" class="calc-section" style="display:none;">
            <div class="widget-card">
                <h4 class="widget-title">Token Ring</h4>

                {{-- KOMPOSISI TOKEN --}}
                <div>
                    <label class="form-label">Komposisi Token (jumlah slot di wheel)</label>
                    <div class="wheel-token-grid">
                        @foreach([1, 2, 3, 5, 10, 20, 30, 100] as $val)
                        <div class="wheel-token-item">
                            <span class="wheel-token-label">x{{ $val }}</span>
                            <input type="number" value="0" min="0"
                                class="form-control wheel-token-input calc-token-input"
                                data-tokenval="{{ $val }}"
                                oninput="calcToken()">
                        </div>
                        @endforeach
                    </div>
                </div>

                <input type="hidden" id="avg-actual-token" value="{{ $avgActualTokenPerSpin ?? '' }}">

                {{-- ITEM HADIAH --}}
                <div style="margin-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label class="form-label" style="margin: 0;">Item Hadiah</label>
                        <button type="button" onclick="addCalcItem()" class="btn btn-secondary btn-sm">+ Item</button>
                    </div>
                    <div id="calc-items-container"></div>
                    <p class="task-meta" style="margin-top: 0.4rem; font-size: 0.7rem;">
                        Isi "Token Tukar" = berapa token dibutuhkan untuk menukar item itu di toko. Isi "Slot" = berapa banyak item ini muncul di wheel.
                    </p>
                </div>

                <div class="calc-result">
                    <p class="task-meta" style="margin-bottom: 0.5rem;">
                        Total bobot: <span id="calc-total-bobot">0</span> ·
                        E(token/spin): <span id="calc-expected-token">0.00</span>
                    </p>

                    <div id="calc-droprate-list"></div>
                </div>

                {{-- RIWAYAT SESI COMPLETED --}}
                @if($completedTokenSessions->count() > 0)
                <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-subtle);">
                    <p class="task-meta" style="font-weight: 600; margin-bottom: 0.5rem;">📜 Riwayat Sesi Sebelumnya</p>
                    @foreach($completedTokenSessions as $hist)
                    <div class="session-history-item">
                        <span class="task-title" style="font-size: 0.8rem;">{{ $hist->item_name }}</span>
                        <span class="task-meta" style="font-size: 0.75rem;">
                            {{ $hist->spent_diamond }} dm · {{ $hist->current_token }} token
                            @if($hist->starting_token > 0)
                            (awal: {{ $hist->starting_token }})
                            @endif
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- TOKEN TOWER --}}
        <div id="tab-tower" class="calc-section" style="display:none;">
            <div class="widget-card">
                <h4 class="widget-title">Token Tower</h4>
                <p class="task-meta" style="margin-bottom: 1rem;">Harga: 1x = 19dm · 5x = 79dm · Target: 5 Token</p>

                <div class="form-group">
                    <label class="form-label">Tingkat Keberuntungan
                        <span id="tower-luck-label" class="badge badge-info">0%</span>
                    </label>
                    <input type="range" id="tower-luck" min="0" max="100" value="0" step="10"
                        oninput="calcTower()" class="form-range">
                    <div class="luck-desc">
                        <span class="task-meta">Worst Case (Pity)</span>
                        <span class="task-meta">Best Case</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Drop Rate Spin Shard (%)</label>
                    <input type="number" id="tower-shard-rate" value="80" min="0" max="100" class="form-control" oninput="calcTower()">
                </div>

                <div class="calc-result">
                    <p class="task-meta" style="margin-bottom: 0.5rem; font-weight: 600;">Rincian per Token (Pity Maksimal):</p>
                    <div class="stat-grid">
                        @php $towerPity = [20, 35, 50, 80, 100]; @endphp
                        @foreach($towerPity as $i => $pity)
                        <div class="stat-item">
                            <span class="stat-number tower-pity-display" data-pity="{{ $pity }}" data-idx="{{ $i+1 }}">{{ $pity }}</span>
                            <span class="stat-label">Token {{ $i+1 }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="calc-total" style="margin-top: 1rem;">
                        <span class="task-meta">Total Spin (setelah luck):</span>
                        <span id="tower-total-spin" class="stat-number">285x</span>
                    </div>
                    <div class="calc-total">
                        <span class="task-meta">Shard Terkumpul:</span>
                        <span id="tower-shard-total" class="stat-number">228</span>
                    </div>
                    <div class="calc-total">
                        <span class="task-meta">Spin Gratis dari Shard:</span>
                        <span id="tower-free-spin" class="stat-number">76x</span>
                    </div>
                    <div class="calc-total">
                        <span class="task-meta">Spin Dibayar:</span>
                        <span id="tower-paid-spin" class="stat-number">209x</span>
                    </div>
                    <div class="calc-total">
                        <span class="task-meta">Estimasi Diamond:</span>
                        <span id="tower-total-diamond" class="stat-number" style="color: var(--accent-primary);">~3315 dm</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    const fadedBase = [9, 19, 39, 69, 99, 199, 399, 799];
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
    let calcItemIndex = 0;

    function switchTab(tab) {
        document.getElementById('tab-faded').style.display = tab === 'faded' ? 'block' : 'none';
        document.getElementById('tab-token').style.display = tab === 'token' ? 'block' : 'none';
        document.getElementById('tab-tower').style.display = tab === 'tower' ? 'block' : 'none';

        const tabs = ['faded', 'token', 'tower'];
        document.querySelectorAll('.tab-btn').forEach((btn, i) => {
            btn.className = tabs[i] === tab ? 'btn btn-primary tab-btn' : 'btn btn-secondary tab-btn';
        });

        if (tab === 'tower') calcTower();
    }

    const fadedDiscounted = [5, 15, 29, 49, 69, 99, 299, 699];

    function calcFaded() {
        const hasDiscount = document.getElementById('faded-has-discount').checked;
        const target = parseInt(document.getElementById('faded-target').value);

        let total = 0;
        document.querySelectorAll('.faded-price').forEach(el => {
            const base = parseInt(el.dataset.base);
            const spin = parseInt(el.dataset.spin);
            const idx = spin - 1;
            const discounted = hasDiscount ? fadedDiscounted[idx] : base;
            el.textContent = discounted + ' dm';
            if (spin <= target) total += discounted;
        });

        document.getElementById('faded-total').textContent = total + ' dm';
    }

    function addCalcItem() {
        const container = document.getElementById('calc-items-container');
        const div = document.createElement('div');
        div.className = 'calc-item-row';
        div.dataset.idx = calcItemIndex;
        div.innerHTML = `
            <input type="text" class="form-control calc-item-name" placeholder="Nama item..." style="flex: 1;">
            <select class="form-control calc-item-rarity" style="width: 100px;">
                <option value="epic">Epic</option>
                <option value="legendary">Legendary</option>
                <option value="artifact">Artifact</option>
            </select>
            <input type="number" class="form-control calc-item-token" placeholder="Token Tukar" min="1" style="width: 90px;" oninput="calcToken()">
            <input type="number" class="form-control calc-item-slot" placeholder="Slot" value="1" min="1" style="width: 60px;" oninput="calcToken()">
            <button type="button" onclick="this.parentElement.remove(); calcToken();" class="btn btn-danger btn-sm">×</button>
        `;
        container.appendChild(div);
        calcItemIndex++;
        calcToken();
    }

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

    function calcToken() {
        let tokenSlots = [];
        document.querySelectorAll('.calc-token-input').forEach(input => {
            const count = parseInt(input.value) || 0;
            const val = parseInt(input.dataset.tokenval);
            if (count > 0) tokenSlots.push({
                val,
                count,
                weight: tokenBaseWeight[val] * count
            });
        });

        let itemRows = [];
        document.querySelectorAll('.calc-item-row').forEach(row => {
            const name = row.querySelector('.calc-item-name').value || 'Item';
            const tokenReq = parseInt(row.querySelector('.calc-item-token').value) || 0;
            const slot = parseInt(row.querySelector('.calc-item-slot').value) || 0;
            if (tokenReq > 0 && slot > 0) itemRows.push({
                name,
                tokenReq,
                slot
            });
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

        document.getElementById('calc-total-bobot').textContent = totalBobot.toFixed(0);
        document.getElementById('calc-expected-token').textContent = expectedToken.toFixed(2);

        let dropRateHtml = '<p class="task-meta" style="font-weight:600; margin-bottom:0.3rem;">Drop Rate Token:</p>';

        tokenSlots.forEach(t => {
            const rate = totalBobot > 0 ? (t.weight / totalBobot * 100) : 0;
            dropRateHtml += `
                <div class="session-stat">
                    <span class="task-meta">Token x${t.val}</span>
                    <span class="task-title">${rate.toFixed(1)}%</span>
                </div>`;
        });

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

        document.getElementById('calc-droprate-list').innerHTML = dropRateHtml;
    }

    function calcTower() {
        const luck = parseInt(document.getElementById('tower-luck').value);
        const shardRate = parseInt(document.getElementById('tower-shard-rate').value) / 100;

        document.getElementById('tower-luck-label').textContent = luck + '%';

        const towerPity = [20, 35, 50, 80, 100];
        let totalSpin = 0;

        document.querySelectorAll('.tower-pity-display').forEach((el, i) => {
            const pity = towerPity[i];
            const effectiveSpin = Math.ceil(pity * (1 - luck / 100));
            el.textContent = effectiveSpin + 'x';
            totalSpin += effectiveSpin;
        });

        const shardTotal = Math.floor(totalSpin * shardRate);
        const freeSpin = Math.floor(shardTotal / 3);
        const paidSpin = Math.max(0, totalSpin - freeSpin);

        const fiveSpins = Math.floor(paidSpin / 5);
        const oneSpins = paidSpin % 5;
        const totalDiamond = (fiveSpins * 79) + (oneSpins * 19);

        document.getElementById('tower-total-spin').textContent = totalSpin + 'x';
        document.getElementById('tower-shard-total').textContent = shardTotal;
        document.getElementById('tower-free-spin').textContent = freeSpin + 'x';
        document.getElementById('tower-paid-spin').textContent = paidSpin + 'x';
        document.getElementById('tower-total-diamond').textContent = '~' + totalDiamond + ' dm';
    }

    calcFaded();
    calcToken();
    calcTower();
</script>
@endpush