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
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        width: 100%;
        max-width: 1100px;
        justify-content: center;
    }

    .portal-card {
        flex: 0 1 220px;
        max-width: 250px;
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