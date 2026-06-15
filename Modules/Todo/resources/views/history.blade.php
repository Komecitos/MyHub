@extends('layouts.app')

@section('topbar')
<a href="{{ route('todo.index') }}" class="btn btn-secondary">Timeline</a>
<a href="{{ route('todo.history') }}" class="btn btn-primary">Riwayat</a>
@endsection

@section('content')

<div class="page-header">
    <h2 class="title">Riwayat</h2>
</div>

{{-- FILTER TOMBOL --}}
<div class="filter-bar">
    <a href="{{ route('todo.history') }}"
        class="btn btn-sm {{ !request('action') ? 'btn-primary' : 'btn-secondary' }}">
        All
    </a>
    @foreach(['created' => 'Dibuat', 'updated' => 'Diedit', 'completed' => 'Selesai', 'recovered' => 'Recovered', 'deleted' => 'Dihapus'] as $key => $label)
    <a href="{{ route('todo.history', ['action' => $key]) }}"
        class="btn btn-sm {{ request('action') === $key ? 'btn-primary' : 'btn-secondary' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

@if($logs->isEmpty())
<p class="empty-state">Belum ada aktivitas tercatat.</p>
@else
<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Aktivitas</th>
                <th>Tugas</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            @php
            $badge = match($log->action) {
            'created' => ['label' => 'Dibuat', 'class' => 'badge-info', 'row' => 'row-created'],
            'updated' => ['label' => 'Diedit', 'class' => 'badge-warning', 'row' => 'row-updated'],
            'completed' => ['label' => 'Selesai', 'class' => 'badge-success', 'row' => 'row-completed'],
            'recovered' => ['label' => 'Recovered', 'class' => 'badge-secondary', 'row' => 'row-recovered'],
            'deleted' => ['label' => 'Dihapus', 'class' => 'badge-danger', 'row' => 'row-deleted'],
            default => ['label' => $log->action,'class' => 'badge-secondary', 'row' => ''],
            };
            @endphp
            <tr class="{{ $badge['row'] }}">
                <td class="task-meta text-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}</td>
                <td><span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span></td>
                <td class="task-title">{{ $log->todo_title }}</td>
                <td class="task-meta">{{ $log->description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="pagination-wrapper">
    {{ $logs->links() }}
</div>
@endif

@endsection