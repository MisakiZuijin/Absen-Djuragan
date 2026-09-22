<nav class="fixed top-0 right-0 left-0 md:left-64 z-30 bg-white/80 backdrop-blur-sm border-b border-slate-200 transition-all duration-300 ease-in-out">
    <div class="flex justify-between items-center p-4">
        <div class="flex-1"></div>

        @auth
            <script>
                window.__raiseHandSoundUrl = "{{ asset('sounds/notification.wav') }}";
            </script>
            <div class="flex items-center gap-4">
                {{-- Notification Area untuk Admin --}}
                @if(auth()->user()->role_id == 1)
                <div class="flex items-center gap-1 mr-4">
                    <a href="{{ route('admin.raiseHand.index') }}"
                       class="relative p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
                       title="Permintaan Raise Hand">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3.5M3 16.5h18"/>
                        </svg>
                        @php
                            $raiseHandCount = \App\Models\HandRaise::where('is_raised', true)->count();
                        @endphp
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center {{ $raiseHandCount > 0 ? '' : 'hidden' }}"
                              id="raiseHandBadge">
                            {{ $raiseHandCount > 0 ? $raiseHandCount : '0' }}
                        </span>
                    </a>
                    <button type="button"
                            onclick="window.testNotificationSound(event)"
                            class="p-2 text-gray-400 hover:text-blue-600 hover:bg-gray-100 rounded-full transition-colors duration-200"
                            title="Test & Aktifkan Suara Notifikasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        </svg>
                    </button>
                    <div class="w-px h-6 bg-gray-300 ml-1"></div>
                </div>
                @elseif(auth()->user()->role_id == 6)
                {{-- Notification Area untuk Assistant Admin --}}
                <div class="flex items-center gap-1 mr-4">
                    <a href="{{ route('assistant.raisehand.list') }}"
                       class="relative p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
                       title="Permintaan Raise Hand">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3.5M3 16.5h18"/>
                        </svg>
                        @php
                            $assistantRaiseHandCount = \App\Models\HandRaise::where('is_raised', true)->count();
                        @endphp
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center {{ $assistantRaiseHandCount > 0 ? '' : 'hidden' }}"
                              id="assistantRaiseHandBadge">
                            {{ $assistantRaiseHandCount > 0 ? $assistantRaiseHandCount : '0' }}
                        </span>
                    </a>
                    <button type="button"
                            onclick="window.testNotificationSound(event)"
                            class="p-2 text-gray-400 hover:text-blue-600 hover:bg-gray-100 rounded-full transition-colors duration-200"
                            title="Test & Aktifkan Suara Notifikasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        </svg>
                    </button>
                    <div class="w-px h-6 bg-gray-300 ml-1"></div>
                </div>
                @endif

                <a href="{{ route('profile.pengaturan.view') }}" class="flex items-center space-x-3 text-black no-underline hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                    <div class="flex flex-col items-end">
                        <span class="text-right font-semibold">{{ optional(auth()->user()->profile)->full_name ?? auth()->user()->name ?? 'Nama Pengguna' }}</span>
                        <span class="text-right text-sm text-gray-500">
                            @switch(auth()->user()->role_id)
                                @case(1) Admin @break
                                @case(2) HR @break
                                @case(3) Intern @break
                                @case(5) Outsider @break
                                @case(6) Assistant Admin @break
                                @default User
                            @endswitch
                        </span>
                    </div>
                    <img src="{{ optional(auth()->user()->profile)->photo ? asset('storage/' . auth()->user()->profile->photo) : asset('img/profile.jpg') }}"
                         alt="Foto Profil"
                         class="w-12 h-12 rounded-full object-cover border-2 border-slate-200 ring-2 ring-white shadow-sm">
                </a>
            </div>
        @endauth

        @guest
            <div class="flex items-center gap-4">
                <a href="{{ route('login.view') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">Login</a>
            </div>
        @endguest
    </div>
</nav>