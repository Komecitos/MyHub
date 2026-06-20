@extends('layouts.app')

@section('topbar')
{{-- Dashboard tidak perlu topbar, kosongkan --}}
@endsection

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

@push('styles')
<style>
    .top-bar {
        display: none;
    }

    .back-btn {
        display: none;
    }

    .portal-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(220px, 1fr)) !important;
        gap: 2rem;
        width: 100%;
        max-width: 1100px;
        justify-items: stretch;
        justify-content: center;
    }

    .dashboard-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 1rem;
    }

    .dashboard-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }
</style>
@endpush

<div class="dashboard-wrapper">
    <div class="dashboard-header">
        <h1>Selamat datang di MyHub</h1>
        <h3>Pilih portal yang ingin kamu buka</h3>
    </div>

    <style>
        .portal-grid {
            justify-content: center;
        }
    </style>

    <div class="portal-grid">

        <a href="/todos" class="portal-card">
            <div class="portal-card-content">
                <span class="portal-icon">
                    <img src="{{ asset('assets/images/todo_logo.png') }}" alt="Todo">
                </span>
                <p class="portal-title">Todo</p>
                <p class="portal-desc">Manajemen tugas</p>
            </div>
        </a>

        <a href="/aquaria" class="portal-card">
            <div class="portal-card-content">
                <span class="portal-icon">🐠</span>
                <p class="portal-title">Aquarium</p>
                <p class="portal-desc">Monitor akuarium</p>
            </div>
        </a>

        <a href="/freefires" class="portal-card">
            <div class="portal-card-content">
                <span class="portal-icon">
                    <img src="{{ asset('assets/images/ff_logo.webp') }}" alt="Free Fire">
                </span>
                <p class="portal-title">Free Fire</p>
                <p class="portal-desc">Game tracker</p>
            </div>
        </a>

    </div>
</div>

@endsection