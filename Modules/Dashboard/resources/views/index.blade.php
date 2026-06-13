<x-dashboard::layouts.master>

    <div style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.2rem; color: #555;">Selamat datang di MyHub</h2>
        <p style="color: #888; font-size: 0.9rem;">Pilih portal yang ingin kamu buka</p>
    </div>

    <div style="
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1.5rem;
    ">

        <a href="/todos" style="text-decoration: none;">
            <div style="background: white; border-radius: 12px; padding: 1.5rem; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s;">
                <div style="font-size: 2.5rem;">✅</div>
                <p style="margin-top: 0.5rem; font-weight: 600; color: #333;">Todo</p>
                <p style="font-size: 0.75rem; color: #888;">Manajemen tugas</p>
            </div>
        </a>

        <a href="/notes" style="text-decoration: none;">
            <div style="background: white; border-radius: 12px; padding: 1.5rem; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="font-size: 2.5rem;">📝</div>
                <p style="margin-top: 0.5rem; font-weight: 600; color: #333;">Notes</p>
                <p style="font-size: 0.75rem; color: #888;">Catatan harian</p>
            </div>
        </a>

        <a href="/aquaria" style="text-decoration: none;">
            <div style="background: white; border-radius: 12px; padding: 1.5rem; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="font-size: 2.5rem;">🐠</div>
                <p style="margin-top: 0.5rem; font-weight: 600; color: #333;">Aquarium</p>
                <p style="font-size: 0.75rem; color: #888;">Monitor akuarium</p>
            </div>
        </a>

        <a href="/calendars" style="text-decoration: none;">
            <div style="background: white; border-radius: 12px; padding: 1.5rem; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="font-size: 2.5rem;">📅</div>
                <p style="margin-top: 0.5rem; font-weight: 600; color: #333;">Calendar</p>
                <p style="font-size: 0.75rem; color: #888;">Jadwal & agenda</p>
            </div>
        </a>

        <a href="/freefires" style="text-decoration: none;">
            <div style="background: white; border-radius: 12px; padding: 1.5rem; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="font-size: 2.5rem;">🎮</div>
                <p style="margin-top: 0.5rem; font-weight: 600; color: #333;">Free Fire</p>
                <p style="font-size: 0.75rem; color: #888;">Game tracker</p>
            </div>
        </a>

    </div>

</x-dashboard::layouts.master>