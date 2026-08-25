<aside class="fixed top-0 left-0 z-40 w-64 h-screen bg-gray-900 text-gray-300">
    <div class="flex items-center justify-center h-24 border-b border-gray-800">
        {{-- Ganti dengan path logo Anda --}}
        <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="h-10">
    </div>

    <nav class="mt-4">
        {{-- 1. Tautan ke Dashboard --}}
        <a href="{{ route('assistant.dashboard') }}"
           class="flex items-center px-6 py-3 transition-colors duration-200
                  {{ request()->routeIs('assistant.dashboard') ? 'bg-gray-700 text-white' : 'hover:bg-gray-700' }}">
            {{-- Ikon Dashboard (Rumah) --}}
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="mx-3 font-medium">Dashboard</span>
        </a>

        {{-- 2. Tautan ke Halaman Raise Hand --}}
        <a href="{{ route('assistant.raisehand.list') }}"
           class="flex items-center px-6 py-3 mt-2 transition-colors duration-200
                  {{ request()->routeIs('assistant.raisehand*') ? 'bg-gray-700 text-white' : 'hover:bg-gray-700' }}">
            {{-- Ikon Raise Hand --}}
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0zM10 11.5v-2a1.5 1.5 0 013 0v2m0 0v-2a1.5 1.5 0 013 0v2m0 0v-2.5a1.5 1.5 0 013 0v2.5m-6-13a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 5.414 7.414 8 6 6.586a1 1 0 010-1.414l4-4z"></path></svg>
            <span class="mx-3 font-medium">Raise Hand</span>
        </a>

        {{-- 3. Tautan Izin (BARU DITAMBAHKAN IKON & DISARANKAN MENGGUNAKAN ROUTE 'assistant') --}}

        {{-- Tautan ke Halaman Izin Keluar --}}
        <a href="{{ route('assistant.izin.leave.index') }}"
   class="flex items-center px-6 py-3 mt-2 transition-colors duration-200
          {{ request()->routeIs('assistant.izin.keluar*') ? 'bg-gray-700 text-white' : 'hover:bg-gray-700' }}">
    {{-- Ikon Izin Keluar (Pintu Keluar) --}}
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"></path></svg>
    <span class="mx-3 font-medium">Izin Keluar</span>
</a>

        {{-- Tautan ke Halaman Izin Shalat --}}
        <a href="{{ route('assistant.izin.prayer.index') }}"
   class="flex items-center px-6 py-3 mt-2 transition-colors duration-200
          {{ request()->routeIs('assistant.izin.shalat*') ? 'bg-gray-700 text-white' : 'hover:bg-gray-700' }}">
    {{-- Ikon Izin Shalat (Jam) --}}
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <span class="mx-3 font-medium">Izin Shalat</span>
</a>

        {{-- Tautan ke Halaman Izin Toilet --}}
       <a href="{{ route('assistant.izin.toilet.index') }}"
   class="flex items-center px-6 py-3 mt-2 transition-colors duration-200
          {{ request()->routeIs('assistant.izin.toilet*') ? 'bg-gray-700 text-white' : 'hover:bg-gray-700' }}">
    {{-- Ikon Izin Toilet (Orang) --}}
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.663M12 10.375a4.125 4.125 0 100-8.25 4.125 4.125 0 000 8.25zM10.125 5.25h3.75M12 3v2.25z"></path></svg>
    <span class="mx-3 font-medium">Izin Toilet</span>
</a>


        {{-- 4. Tautan ke Halaman Log Aktivitas --}}
        <a href="{{ route('assistant.logactivity') }}"
           class="flex items-center px-6 py-3 mt-2 transition-colors duration-200
                  {{ request()->routeIs('assistant.logactivity*') ? 'bg-gray-700 text-white' : 'hover:bg-gray-700' }}">
            {{-- Ikon Log Aktivitas --}}
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            <span class="mx-3 font-medium">Persetujuan Log</span>
        </a>
    </nav>

    {{-- Tombol Logout di bagian bawah sidebar --}}
    <div class="absolute bottom-0 w-full border-t border-gray-800">
        <a href="{{ route('logout.action') }}" class="flex items-center w-full px-6 py-4 transition-colors duration-200 text-gray-300 hover:bg-red-700 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            <span class="mx-3 font-medium">Log Out</span>
        </a>
    </div>
</aside>