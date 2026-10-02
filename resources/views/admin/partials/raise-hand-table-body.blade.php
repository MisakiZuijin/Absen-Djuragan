@php
$tab = $activeTab ?? 'question';

if (!isset($formatShortTime)) {
$formatShortTime = function ($date) {
if (!$date) return '-';
$now = \Carbon\Carbon::now();
$diffSec = abs($now->diffInSeconds($date));
if ($diffSec < 60) return max(1, $diffSec) . 's' ;
    $diffMin=abs($now->diffInMinutes($date));
    if ($diffMin < 60) return $diffMin . 'm' ;
        $diffHours=abs($now->diffInHours($date));
        if ($diffHours < 24) return $diffHours . 'j' ;
            $diffDays=abs($now->diffInDays($date));
            if ($diffDays < 30) return $diffDays . 'd' ;
                if ($diffDays < 365) return floor($diffDays / 30) . 'bln' ;
                return floor($diffDays / 365) . 'thn' ;
                };
                }
                @endphp

                @forelse($handRaises as $index=> $handRaise)
                @php
                $userName = $handRaise->user->profile->full_name ?? $handRaise->user->name ?? 'Peserta';
                $userSchool = $handRaise->user->intern->school->name ?? 'Sekolah tidak diketahui';
                $userPhone = $handRaise->user->profile->phone ?? '-';
                $userDivision = $handRaise->user->intern->division->name ?? null;
                $projectName = $handRaise->project?->nameProject?->name ?? $handRaise->user->intern->detailProject->last()?->project->nameProject->name ?? null;
                $initial = strtoupper(substr($userName, 0, 1));
                $isToday = $handRaise->presentation_date && \Carbon\Carbon::parse($handRaise->presentation_date)->isToday();
                $cleanNotes = addslashes(str_replace(["\r", "\n", "'"], [' ', ' ', ' '], $handRaise->notes ?? $handRaise->reason ?? ''));
                $cleanResponse = addslashes(str_replace(["\r", "\n", "'"], [' ', ' ', ' '], $handRaise->admin_response ?? $handRaise->performance_notes ?? ''));
                $shortCreatedAt = $formatShortTime($handRaise->created_at);
                $internShiftText = $handRaise->shift_text;
                $isAssistantAdmin = auth()->check() && (int) auth()->user()->role_id === 6;
                @endphp

                @if($tab === 'presentation')
                <!-- Item Card Minimalis & Ringkas untuk Penjadwalan Presentasi -->
                <div class="raise-hand-card bg-white rounded-2xl border {{ $isToday && $handRaise->is_raised ? 'border-rose-300 ring-2 ring-rose-200/80 shadow-xs' : 'border-slate-200 hover:border-slate-300 shadow-2xs' }} transition-all duration-150 p-4 sm:p-4.5 space-y-3"
                    data-name="{{ strtolower($userName) }}"
                    data-school="{{ strtolower($userSchool) }}"
                    data-phone="{{ $userPhone }}"
                    data-type="{{ $handRaise->type ?? 'presentation' }}"
                    data-status="{{ $handRaise->status ?? 'pending' }}"
                    data-notes="{{ strtolower($handRaise->notes ?? $handRaise->reason ?? '') }}"
                    data-id="{{ $handRaise->id }}">
                    
                    <!-- 1. Header: Peserta & Status -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Avatar -->
                            <div class="relative shrink-0">
                                <div class="w-9 h-9 rounded-xl {{ $isToday && $handRaise->is_raised ? 'bg-rose-600 ring-2 ring-rose-200' : 'bg-slate-800' }} flex items-center justify-center text-white font-extrabold text-xs shadow-xs">
                                    {{ $initial }}
                                </div>
                                @if($handRaise->is_raised)
                                    @if($handRaise->status === 'urgent' || $isToday)
                                    <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white shadow-xs"></div>
                                    @else
                                    <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-amber-400 rounded-full border-2 border-white shadow-xs"></div>
                                    @endif
                                @else
                                <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-500 rounded-full border-2 border-white shadow-xs"></div>
                                @endif
                            </div>

                            <!-- Nama & Info Instansi -->
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-sm text-slate-900 user-name">{{ $userName }}</span>
                                    @if($userDivision)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/80">
                                        {{ $userDivision }}
                                    </span>
                                    @endif
                                    <span class="text-[11px] text-slate-400">
                                        • Diajukan {{ $shortCreatedAt }} lalu
                                    </span>
                                </div>

                                <div class="flex items-center gap-3 text-xs text-slate-500 mt-0.5 flex-wrap">
                                    <span class="flex items-center gap-1 truncate max-w-[250px]">
                                        <i class="fa-solid fa-graduation-cap text-slate-400 text-[11px] shrink-0"></i>
                                        <span class="truncate">{{ $userSchool }}</span>
                                    </span>
                                    @if($userPhone && $userPhone !== '-')
                                    <span class="flex items-center gap-1 text-slate-400">
                                        <i class="fa-solid fa-phone text-[10px]"></i>
                                        <span class="text-slate-500">{{ $userPhone }}</span>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge (Kanan) -->
                        <div class="flex items-center shrink-0 self-start sm:self-center">
                            @if(!in_array($handRaise->status, ['accepted', 'ready', 'needs_revision', 'rescheduled']))
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs whitespace-nowrap">
                                <i class="fa-solid fa-hourglass-start text-amber-600 text-[10px]"></i>
                                <span>Menunggu Persetujuan</span>
                            </span>
                            @elseif($handRaise->status === 'rescheduled')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-300 shadow-2xs whitespace-nowrap">
                                <i class="fa-solid fa-clock-rotate-left text-blue-600 text-[10px]"></i>
                                <span>Jadwal Diubah</span>
                            </span>
                            @elseif($handRaise->status === 'accepted')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 shadow-2xs whitespace-nowrap">
                                <i class="fa-solid fa-calendar-check text-emerald-600 text-[10px]"></i>
                                <span>Jadwal Diterima</span>
                            </span>
                            @elseif($handRaise->status === 'needs_revision')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-orange-50 text-orange-900 border border-orange-300 shadow-2xs whitespace-nowrap">
                                <i class="fa-solid fa-triangle-exclamation text-orange-600 text-[10px]"></i>
                                <span>Perlu Perbaikan</span>
                            </span>
                            @elseif($handRaise->status === 'ready')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-300 shadow-2xs whitespace-nowrap">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-[10px]"></i>
                                <span>Lulus Valid</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap">
                                <i class="fa-regular fa-clock text-blue-500 text-[10px]"></i>
                                <span>Terjadwal</span>
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- 2. Ringkasan Judul, Jadwal & Detail Tags -->
                    <div class="space-y-2.5">
                        <!-- Judul Materi & Link Tugas -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div class="flex items-start gap-2 min-w-0">
                                <i class="fa-solid fa-chalkboard-user text-slate-400 text-xs mt-1 shrink-0"></i>
                                <div class="min-w-0">
                                    <div class="font-bold text-xs text-slate-900 leading-snug">
                                        {{ $handRaise->notes ?? $handRaise->reason ?? 'Presentasi Modul Magang' }}
                                    </div>
                                </div>
                            </div>

                            @php
                            $isFigma = str_contains(strtolower($gitRepoUrl ?? ''), 'figma.com');
                            $isGit = str_contains(strtolower($gitRepoUrl ?? ''), 'github.com') || str_contains(strtolower($gitRepoUrl ?? ''), 'gitlab.com');
                            $hasTaskLink = !empty($gitRepoUrl) && ($isProgrammerUser || $isUiUxUser);
                            @endphp

                            @if($hasTaskLink)
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ $gitRepoUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold transition shadow-2xs {{ $isFigma || $isUiUxUser ? 'bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200' : 'bg-slate-900 text-white hover:bg-black' }}"
                                    title="Buka Link Tugas ({{ $gitRepoUrl }})">
                                    @if($isFigma || $isUiUxUser)
                                    <i class="fa-brands fa-figma text-purple-600 text-xs"></i>
                                    @else
                                    <i class="fa-brands fa-github text-emerald-400 text-xs"></i>
                                    @endif
                                    <span>Link Tugas</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[8px] opacity-70"></i>
                                </a>
                            </div>
                            @endif
                        </div>

                        <!-- Baris Jadwal Pelaksanaan & Shift (Compact Chips) -->
                        <div class="flex items-center gap-2 flex-wrap text-xs bg-slate-50/80 p-2.5 rounded-xl border border-slate-200/70">
                            <!-- Tanggal -->
                            <div class="flex items-center gap-1.5 font-bold {{ $isToday ? 'text-rose-600' : 'text-slate-800' }}">
                                <i class="fa-regular fa-calendar {{ $isToday ? 'text-rose-500' : 'text-slate-400' }} text-xs shrink-0"></i>
                                <span>{{ $handRaise->presentation_date ? $handRaise->presentation_date->format('d M Y') : '-' }}</span>
                            </div>

                            @if($isToday)
                            <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-rose-600 text-white tracking-wide shadow-2xs">
                                HARI INI
                            </span>
                            @endif

                            <span class="text-slate-300">•</span>

                            <!-- Jam -->
                            @if(!empty($handRaise->scheduled_time))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <i class="fa-solid fa-clock text-indigo-500 text-[9px]"></i>
                                <span>{{ substr($handRaise->scheduled_time, 0, 5) }} WIB</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-amber-50 text-amber-800 border border-amber-200 italic">
                                <i class="fa-regular fa-clock text-amber-600 text-[9px]"></i>
                                <span>Jam belum diatur</span>
                            </span>
                            @endif

                            <span class="text-slate-300">•</span>

                            <!-- Mode -->
                            @if($handRaise->presentation_mode === 'online')
                                @php
                                $adminMeetUrl = $handRaise->meet_url ?: ($handRaise->user?->intern?->division?->meet_url ?? null);
                                $isApproved = in_array($handRaise->status, ['accepted', 'ready', 'needs_revision']);
                                @endphp
                                @if($adminMeetUrl && $isApproved)
                                <a href="{{ $adminMeetUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-sky-50 text-sky-700 hover:bg-sky-100 hover:text-sky-900 border border-sky-200 transition shadow-2xs group/meet"
                                    title="Buka Ruang Google Meet: {{ $adminMeetUrl }}">
                                    <i class="fa-solid fa-video text-sky-500 text-[10px]"></i>
                                    <span>Online (Google Meet)</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[8px] text-sky-400 group-hover/meet:text-sky-700"></i>
                                </a>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                    <i class="fa-solid fa-video text-sky-500 text-[9px]"></i>
                                    <span>Online (GMeet{{ !$isApproved ? ' - Disiapkan' : '' }})</span>
                                </span>
                                @endif
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-200/80 text-slate-700 border border-slate-300">
                                <i class="fa-solid fa-building text-slate-500 text-[9px]"></i>
                                <span>Tatap Muka Langsung</span>
                            </span>
                            @endif

                            <!-- Shift -->
                            @if($internShiftText)
                            <span class="text-slate-300">•</span>
                            <span class="text-[11px] text-slate-600 flex items-center gap-1 font-medium">
                                <i class="fa-regular fa-clock text-amber-600 text-[10px]"></i>
                                <span>Shift: <strong class="text-amber-950 font-bold">{{ $internShiftText }}</strong></span>
                            </span>
                            @endif
                        </div>

                        <!-- Catatan Revisi / Evaluasi (Jika ada) -->
                        @if(!empty($handRaise->performance_notes))
                        <div class="p-2 bg-amber-50 rounded-xl border border-amber-200 text-xs space-y-0.5">
                            <span class="text-[10px] font-bold text-amber-800 flex items-center gap-1">
                                <i class="fa-solid fa-clipboard-list text-amber-600"></i> Catatan Revisi Pemagang:
                            </span>
                            <p class="text-[11px] text-amber-950 leading-snug whitespace-pre-line">{{ trim($handRaise->performance_notes) }}</p>
                        </div>
                        @elseif(!empty($handRaise->admin_response))
                        <div class="p-2 bg-orange-50/80 rounded-xl border border-orange-200 text-xs space-y-0.5">
                            <span class="text-[10px] font-bold text-orange-800 flex items-center gap-1">
                                <i class="fa-solid fa-clipboard-check text-orange-600"></i> Catatan Evaluasi Mentor:
                            </span>
                            <p class="text-[11px] text-orange-950 italic leading-snug">"{{ trim($handRaise->admin_response) }}"</p>
                        </div>
                        @endif
                    </div>

                    <!-- 3. Footer: Tombol Aksi Lengkap & Ringkas -->
                    <div class="pt-2.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <!-- Hint -->
                        <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                            @if(!$isAssistantAdmin)
                                @if(!in_array($handRaise->status, ['accepted', 'ready', 'needs_revision']))
                                <i class="fa-solid fa-info-circle text-amber-500 text-xs shrink-0"></i>
                                <span>Konfirmasi tanggal dan jam sebelum memulai presentasi.</span>
                                @else
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs shrink-0"></i>
                                <span>Jadwal telah disetujui. Tindak lanjut penyelesaian atau revisi dilakukan oleh pemagang.</span>
                                @endif
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
                            @if($isAssistantAdmin)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200" title="Uji presentasi dan penilaian dilakukan oleh Admin / Pembimbing Utama">
                                <i class="fa-solid fa-lock text-amber-600 text-xs"></i>
                                <span>Hak Akses Admin</span>
                            </span>
                            @else
                                @if(!in_array($handRaise->status, ['accepted', 'ready', 'needs_revision']))
                                <button type="button"
                                    class="btn-trigger-approve-presentation inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
                                    data-id="{{ $handRaise->id }}"
                                    data-name="{{ $userName }}"
                                    data-shift="{{ $internShiftText ?? 'Belum Diatur' }}"
                                    data-title="{{ $cleanNotes }}"
                                    data-mode="{{ $handRaise->presentation_mode ?? 'offline' }}"
                                    data-date="{{ $handRaise->presentation_date ? $handRaise->presentation_date->format('Y-m-d') : date('Y-m-d') }}"
                                    data-time="{{ $handRaise->scheduled_time ? substr($handRaise->scheduled_time, 0, 5) : '' }}"
                                    data-notes="{{ addslashes($handRaise->admin_response ?? '') }}"
                                    data-status="{{ $handRaise->status ?? 'pending' }}"
                                    onclick="if(window.openApprovePresentationModal) { window.openApprovePresentationModal(this.dataset.id, this.dataset.name, this.dataset.shift, this.dataset.title, this.dataset.mode, this.dataset.date, this.dataset.time, this.dataset.notes, this.dataset.status); }"
                                    title="Konfirmasi Jadwal Presentasi">
                                    <i class="fa-solid fa-calendar-check text-xs"></i>
                                    <span>Konfirmasi Jadwal</span>
                                </button>
                                @else
                                <button type="button"
                                    class="btn-trigger-approve-presentation inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
                                    data-id="{{ $handRaise->id }}"
                                    data-name="{{ $userName }}"
                                    data-shift="{{ $internShiftText ?? 'Belum Diatur' }}"
                                    data-title="{{ $cleanNotes }}"
                                    data-mode="{{ $handRaise->presentation_mode ?? 'offline' }}"
                                    data-date="{{ $handRaise->presentation_date ? $handRaise->presentation_date->format('Y-m-d') : date('Y-m-d') }}"
                                    data-time="{{ $handRaise->scheduled_time ? substr($handRaise->scheduled_time, 0, 5) : '' }}"
                                    data-notes="{{ addslashes($handRaise->admin_response ?? '') }}"
                                    data-status="{{ $handRaise->status ?? 'accepted' }}"
                                    onclick="if(window.openApprovePresentationModal) { window.openApprovePresentationModal(this.dataset.id, this.dataset.name, this.dataset.shift, this.dataset.title, this.dataset.mode, this.dataset.date, this.dataset.time, this.dataset.notes, this.dataset.status); }"
                                    title="Ubah / Atur Ulang Jadwal Presentasi">
                                    <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                                    <span>Ubah Jadwal</span>
                                </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                @elseif($tab === 'question')
                @php
                $thread = $handRaise->conversation_thread;
                $messagesCount = count($thread);
                $isResponded = ($handRaise->status === 'responded') || !empty($handRaise->admin_response);
                $isWaitingFollowUp = ($handRaise->status === 'pending') && ($messagesCount > 1);
                @endphp
                <!-- Item Card untuk Bertanya / Bantuan Kendala -->
                <div class="raise-hand-card bg-white rounded-2xl border {{ $isWaitingFollowUp ? 'border-amber-300 hover:border-amber-400' : ($isResponded ? 'border-emerald-200 hover:border-emerald-300' : 'border-slate-200 hover:border-blue-300') }} shadow-2xs transition-all duration-150 p-4 sm:p-4.5 space-y-3"
                    data-name="{{ strtolower($userName) }}"
                    data-school="{{ strtolower($userSchool) }}"
                    data-phone="{{ $userPhone }}"
                    data-type="{{ $handRaise->type ?? 'question' }}"
                    data-status="{{ $handRaise->status ?? 'pending' }}"
                    data-notes="{{ strtolower($handRaise->notes ?? $handRaise->reason ?? '') }}"
                    data-id="{{ $handRaise->id }}">

                    <!-- 1. Header: Peserta & Status -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Avatar -->
                            <div class="relative shrink-0">
                                <div class="w-9 h-9 rounded-xl {{ $isWaitingFollowUp ? 'bg-amber-600' : ($isResponded ? 'bg-emerald-600' : 'bg-blue-600') }} flex items-center justify-center text-white font-extrabold text-xs shadow-xs">
                                    {{ $initial }}
                                </div>
                                @if($handRaise->is_raised)
                                    @if($isWaitingFollowUp)
                                    <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-amber-400 rounded-full border-2 border-white shadow-xs animate-pulse"></div>
                                    @elseif($isResponded)
                                    <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-400 rounded-full border-2 border-white shadow-xs"></div>
                                    @else
                                    <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-blue-500 rounded-full border-2 border-white shadow-xs"></div>
                                    @endif
                                @else
                                <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-gray-400 rounded-full border-2 border-white shadow-xs"></div>
                                @endif
                            </div>

                            <!-- Nama & Info Instansi -->
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-sm text-slate-900 user-name">{{ $userName }}</span>
                                    @if($userDivision)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
                                        {{ $userDivision }}
                                    </span>
                                    @endif
                                    <span class="text-[11px] text-slate-400">
                                        • Diajukan {{ $shortCreatedAt }} lalu
                                    </span>
                                </div>

                                <div class="flex items-center gap-3 text-xs text-slate-500 mt-0.5 flex-wrap">
                                    <span class="flex items-center gap-1 truncate max-w-[250px]">
                                        <i class="fa-solid fa-graduation-cap text-slate-400 text-[11px] shrink-0"></i>
                                        <span class="truncate">{{ $userSchool }}</span>
                                    </span>
                                    @if($userPhone && $userPhone !== '-')
                                    <span class="flex items-center gap-1 text-slate-400">
                                        <i class="fa-solid fa-phone text-[10px]"></i>
                                        <span class="text-slate-500">{{ $userPhone }}</span>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge (Kanan) -->
                        <div class="flex items-center gap-2 shrink-0 self-start sm:self-center">
                            @if($messagesCount > 1)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/80">
                                <i class="fa-solid fa-comment-dots text-slate-500 text-[9px]"></i>
                                <span>{{ $messagesCount }} Pesan</span>
                            </span>
                            @endif

                            @if($isWaitingFollowUp)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs whitespace-nowrap">
                                <i class="fa-solid fa-comments text-amber-600 animate-pulse text-[10px]"></i>
                                <span>Tanya Lagi</span>
                            </span>
                            @elseif($isResponded)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 shadow-2xs whitespace-nowrap">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-[10px]"></i>
                                <span>Sudah Ditanggapi</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-300 shadow-2xs whitespace-nowrap">
                                <i class="fa-solid fa-hand-paper text-blue-600 animate-pulse text-[10px]"></i>
                                <span>Menunggu Bantuan</span>
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- 2. Ringkasan & Pertanyaan / Kendala -->
                    <div class="space-y-2.5">
                        @if($internShiftText)
                        <div class="flex items-center gap-2 flex-wrap text-xs bg-slate-50/80 px-3 py-2 rounded-xl border border-slate-200/70">
                            <span class="text-[11px] text-slate-600 flex items-center gap-1 font-medium">
                                <i class="fa-regular fa-clock text-amber-600 text-[11px]"></i>
                                <span>Shift Kerja: <strong class="text-amber-950 font-bold">{{ $internShiftText }}</strong></span>
                            </span>
                        </div>
                        @endif

                        <!-- Kotak Detail Pertanyaan / Kendala (Full Width - Riwayat ada di Popup Chat) -->
                        <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 text-xs text-slate-800 space-y-1">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1 text-[11px] font-bold text-blue-700 uppercase tracking-wider">
                                    <i class="fa-solid fa-circle-question text-[10px]"></i>
                                    <span>Detail Pertanyaan / Kendala:</span>
                                </div>
                                @if($messagesCount > 1)
                                <span class="inline-flex items-center gap-1 text-[10px] text-blue-700 font-semibold">
                                    <i class="fa-solid fa-comments text-blue-500 text-[10px]"></i>
                                    <span>Percakapan di popup chat</span>
                                </span>
                                @endif
                            </div>
                            <p class="whitespace-pre-line text-slate-800 font-medium text-xs leading-relaxed break-words">{{ trim($handRaise->notes ?? $handRaise->reason ?? 'Tidak ada keterangan detail') }}</p>
                        </div>
                    </div>

                    <!-- 3. Footer: Tombol Aksi -->
                    <div class="pt-2.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <!-- Hint -->
                        <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                            @if(!$isAssistantAdmin)
                                @if($isWaitingFollowUp)
                                <i class="fa-solid fa-comments text-amber-500 text-xs shrink-0"></i>
                                <span>Pemagang bertanya lagi. Harap berikan balasan solusi lanjutan.</span>
                                @elseif($isResponded)
                                <i class="fa-solid fa-circle-info text-emerald-500 text-xs shrink-0"></i>
                                <span>Tanggapan telah terkirim. Menunggu pemagang membaca atau Anda dapat menyelesaikannya.</span>
                                @else
                                <i class="fa-solid fa-info-circle text-blue-500 text-xs shrink-0"></i>
                                <span>Bantu pemagang menyelesaikan kendala teknis atau berikan tanggapan solusi.</span>
                                @endif
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
                            @if($isAssistantAdmin)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200" title="Bantuan teknis dan penyelesaian dilakukan oleh Admin / Pembimbing Utama">
                                <i class="fa-solid fa-lock text-amber-600 text-xs"></i>
                                <span>Hak Akses Admin</span>
                            </span>
                            @else
                            @php
                            $questionConfirmRoute = route('admin.raiseHand.confirm', $handRaise->id);
                            @endphp

                            <!-- Tombol Beri Tanggapan / Balas Pertanyaan / Buka Diskusi (Chat Modal) -->
                            <button type="button"
                                class="btn-trigger-process inline-flex items-center gap-1.5 px-3.5 py-1.5 {{ $isWaitingFollowUp ? 'bg-amber-600 hover:bg-amber-700' : ($isResponded ? 'bg-amber-500 hover:bg-amber-600' : 'bg-blue-600 hover:bg-blue-700') }} text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
                                data-id="{{ $handRaise->id }}"
                                data-name="{{ $userName }}"
                                data-school="{{ $userSchool }}"
                                data-type="question"
                                data-notes="{{ $cleanNotes }}"
                                data-response="{{ $cleanResponse }}"
                                data-thread="{{ base64_encode(json_encode($thread)) }}"
                                onclick="event.stopPropagation(); if(window.openChatQuestionModal) { window.openChatQuestionModal(this.dataset.id, this.dataset.name, this.dataset.school, this.dataset.notes, this.dataset.thread); } else if(window.openProcessModal) { window.openProcessModal(this.dataset.id, this.dataset.name, this.dataset.type, this.dataset.notes, this.dataset.response, this.dataset.thread); }"
                                title="{{ $isWaitingFollowUp ? 'Balas pertanyaan lanjutan pemagang' : ($isResponded ? 'Buka ruang diskusi / edit tanggapan' : 'Beri tanggapan / solusi ke pemagang') }}">
                                <i class="fa-solid {{ $isWaitingFollowUp ? 'fa-comments' : ($isResponded ? 'fa-comments' : 'fa-reply') }} text-xs"></i>
                                <span>{{ $isWaitingFollowUp ? 'Balas Pertanyaan' : ($isResponded ? 'Buka Diskusi' : 'Beri Tanggapan') }}</span>
                            </button>

                            <!-- Tombol Langsung Selesai -->
                            <form action="{{ $questionConfirmRoute }}" method="POST" class="inline-block m-0 p-0" onsubmit="return confirm('Selesaikan bantuan / pertanyaan dari {{ addslashes($userName) }}?');">
                                @csrf
                                <input type="hidden" name="action" value="complete_question">
                                <input type="hidden" name="tab" value="question">
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer whitespace-nowrap"
                                    title="Selesaikan bantuan/pertanyaan">
                                    <i class="fa-solid fa-check text-xs"></i>
                                    <span>Selesai</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                @elseif($tab === 'new_task')
                <!-- Item Card untuk Permintaan Tugas Baru -->
                <div class="raise-hand-card bg-white rounded-2xl border border-slate-200 hover:border-purple-300 shadow-2xs transition-all duration-150 p-4 sm:p-4.5 space-y-3"
                    data-name="{{ strtolower($userName) }}"
                    data-school="{{ strtolower($userSchool) }}"
                    data-phone="{{ $userPhone }}"
                    data-type="{{ $handRaise->type ?? 'new_task' }}"
                    data-status="{{ $handRaise->status ?? 'pending' }}"
                    data-notes="{{ strtolower($handRaise->notes ?? $handRaise->reason ?? '') }}"
                    data-id="{{ $handRaise->id }}">

                    <!-- 1. Header: Peserta & Status -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Avatar -->
                            <div class="relative shrink-0">
                                <div class="w-9 h-9 rounded-xl bg-purple-600 flex items-center justify-center text-white font-extrabold text-xs shadow-xs">
                                    {{ $initial }}
                                </div>
                                <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-purple-400 rounded-full border-2 border-white shadow-xs"></div>
                            </div>

                            <!-- Nama & Info Instansi -->
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-sm text-slate-900 user-name">{{ $userName }}</span>
                                    @if($userDivision)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200/80">
                                        {{ $userDivision }}
                                    </span>
                                    @endif
                                    <span class="text-[11px] text-slate-400">
                                        • Diajukan {{ $shortCreatedAt }} lalu
                                    </span>
                                </div>

                                <div class="flex items-center gap-3 text-xs text-slate-500 mt-0.5 flex-wrap">
                                    <span class="flex items-center gap-1 truncate max-w-[250px]">
                                        <i class="fa-solid fa-graduation-cap text-slate-400 text-[11px] shrink-0"></i>
                                        <span class="truncate">{{ $userSchool }}</span>
                                    </span>
                                    @if($userPhone && $userPhone !== '-')
                                    <span class="flex items-center gap-1 text-slate-400">
                                        <i class="fa-solid fa-phone text-[10px]"></i>
                                        <span class="text-slate-500">{{ $userPhone }}</span>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge (Kanan) -->
                        <div class="flex items-center shrink-0 self-start sm:self-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-800 border border-purple-300 shadow-2xs whitespace-nowrap">
                                <i class="fa-solid fa-hourglass-half text-purple-600 text-[10px]"></i>
                                <span>Menunggu Tugas</span>
                            </span>
                        </div>
                    </div>

                    <!-- 2. Ringkasan & Permintaan Siswa -->
                    <div class="space-y-2.5">
                        @if($internShiftText)
                        <div class="flex items-center gap-2 flex-wrap text-xs bg-slate-50/80 px-3 py-2 rounded-xl border border-slate-200/70">
                            <span class="text-[11px] text-slate-600 flex items-center gap-1 font-medium">
                                <i class="fa-regular fa-clock text-amber-600 text-[11px]"></i>
                                <span>Shift Kerja: <strong class="text-amber-950 font-bold">{{ $internShiftText }}</strong></span>
                            </span>
                        </div>
                        @endif

                        <!-- Permintaan Tugas Siswa (Full Width) -->
                        <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100 text-xs text-slate-800 space-y-1">
                            <div class="flex items-center gap-1 text-[11px] font-bold text-purple-700 uppercase tracking-wider">
                                <i class="fa-solid fa-user-pen text-[10px]"></i>
                                <span>Laporan Siswa / Permintaan Tugas:</span>
                            </div>
                            <p class="whitespace-pre-line text-slate-800 font-medium text-xs leading-relaxed break-words">{{ trim($handRaise->notes ?? $handRaise->reason ?? 'Tugas sebelumnya telah selesai, meminta modul / tugas baru.') }}</p>
                        </div>
                    </div>

                    <!-- 3. Footer: Tombol Aksi -->
                    <div class="pt-2.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <!-- Hint -->
                        <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                            @if(!$isAssistantAdmin)
                            <i class="fa-solid fa-circle-info text-purple-500 text-xs shrink-0"></i>
                            <span>Tambahkan project/tugas di menu <strong>Setting Project</strong>, lalu klik Selesai untuk menyudahi permintaan ini.</span>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
                            @if($isAssistantAdmin)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200" title="Pemberian tugas baru dan penyelesaian dilakukan oleh Admin / Pembimbing Utama">
                                <i class="fa-solid fa-lock text-amber-600 text-xs"></i>
                                <span>Hak Akses Admin</span>
                            </span>
                            @else
                            @php
                            $taskConfirmRoute = route('admin.raiseHand.confirm', $handRaise->id);
                            @endphp

                            <!-- Link Shortcut ke Setting Project (Hanya untuk Admin) -->
                            <a href="{{ route('admin.pengaturan.project', ['intern_id' => $handRaise->user?->intern?->id, 'action' => 'assign_task', 'raise_id' => $handRaise->id]) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold rounded-xl border border-purple-200 transition cursor-pointer whitespace-nowrap shadow-2xs"
                                title="Langsung Berikan Project ke Pemagang Ini">
                                <i class="fa-solid fa-folder-plus text-[11px] text-purple-600"></i>
                                <span>Beri Tugas Sekarang</span>
                            </a>

                            <!-- Tombol Selesai -->
                            <form action="{{ $taskConfirmRoute }}" method="POST" class="inline-block m-0 p-0" onsubmit="return confirm('Selesaikan permintaan tugas dari {{ addslashes($userName) }}?');">
                                @csrf
                                <input type="hidden" name="action" value="complete_task">
                                <input type="hidden" name="tab" value="new_task">
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer whitespace-nowrap"
                                    title="Selesaikan permintaan tugas">
                                    <i class="fa-solid fa-check text-xs"></i>
                                    <span>Selesai</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                @else
                <tr class="raise-hand-row group hover:bg-slate-50/70 transition-all duration-150 {{ $isToday && $handRaise->type === 'presentation' && $handRaise->is_raised ? 'bg-red-50/30' : '' }}"
                    data-name="{{ strtolower($userName) }}"
                    data-school="{{ strtolower($userSchool) }}"
                    data-phone="{{ $userPhone }}"
                    data-type="{{ $handRaise->type ?? 'question' }}"
                    data-status="{{ $handRaise->status ?? 'pending' }}"
                    data-notes="{{ strtolower($handRaise->notes ?? $handRaise->reason ?? '') }}"
                    data-id="{{ $handRaise->id }}">

                    <!-- Index -->
                    <td class="py-3 px-3 text-center align-top">
                        <div class="w-6 h-6 mx-auto rounded-lg bg-gray-100 border border-gray-200/70 flex items-center justify-center text-[11px] font-bold text-gray-700 shadow-2xs">
                            {{ ($handRaises instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) ? ($handRaises->firstItem() + $index) : ($index + 1) }}
                        </div>
                    </td>

                    <!-- Peserta Info -->
                    <td class="py-3 px-3 align-top">
                        <div class="flex items-start gap-2.5">
                            <div class="relative flex-shrink-0 mt-0.5">
                                <div class="w-8 h-8 rounded-lg {{ $isToday && $handRaise->type === 'presentation' && $handRaise->is_raised ? 'bg-red-600 ring-2 ring-red-200' : 'bg-slate-800' }} flex items-center justify-center text-white font-extrabold text-xs shadow-xs">
                                    {{ $initial }}
                                </div>
                                @if($handRaise->is_raised)
                                @if($handRaise->status === 'urgent' || $isToday)
                                <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white shadow-xs"></div>
                                @else
                                <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-amber-400 rounded-full border-2 border-white shadow-xs"></div>
                                @endif
                                @else
                                <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-500 rounded-full border-2 border-white shadow-xs"></div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1 space-y-0.5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-xs text-gray-900 user-name leading-tight">{{ $userName }}</span>
                                    @if($userDivision)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $userDivision }}
                                    </span>
                                    @endif
                                </div>

                                <div class="text-[11px] text-gray-500 user-school flex items-center gap-1 truncate">
                                    <i class="fa-solid fa-graduation-cap text-gray-400 text-[10px] shrink-0"></i>
                                    <span class="truncate">{{ $userSchool }}</span>
                                </div>

                                @php
                                $gitRepoUrl = $handRaise->project?->repository_url
                                ?? $handRaise->user->intern?->detailProject?->last()?->project?->repository_url;
                                $userDivLower = strtolower($userDivision ?? '');
                                $userDivId = (int) ($handRaise->user->intern?->division_id ?? 0);
                                $isProgrammerUser = ($userDivId === 4)
                                || str_contains($userDivLower, 'programmer')
                                || str_contains($userDivLower, 'program');
                                $isUiUxUser = ($userDivId === 1)
                                || str_contains($userDivLower, 'ui/ux')
                                || str_contains($userDivLower, 'ui / ux')
                                || (str_contains($userDivLower, 'ui') && str_contains($userDivLower, 'ux'));
                                @endphp

                                <div class="text-[10px] text-gray-400 flex items-center gap-1.5 flex-wrap pt-0.5">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-phone text-[8px] text-gray-400"></i>
                                        <span>{{ $userPhone }}</span>
                                    </span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1" title="{{ $handRaise->created_at ? $handRaise->created_at->format('d M Y, H:i') : '' }}">
                                        <i class="fa-regular fa-clock text-[8px] text-gray-400"></i>
                                        <span>{{ $shortCreatedAt }}</span>
                                    </span>
                                    @if($internShiftText && $tab !== 'presentation' && ($handRaise->type ?? '') !== 'presentation')
                                    <span>•</span>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-50 text-amber-900 border border-amber-200" title="Shift: {{ $internShiftText }}">
                                        <i class="fa-regular fa-clock text-amber-600 text-[8px]"></i>
                                        <span>Shift: <strong class="font-bold text-amber-950">{{ $internShiftText }}</strong></span>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- Detail / Materi / Permintaan -->
                    <td class="py-3 px-3 align-top">
                        <div class="text-xs text-gray-900 font-medium leading-relaxed space-y-1">
                            @if($handRaise->type === 'presentation')
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fa-solid fa-chalkboard-user text-[9px]"></i> Presentasi
                                </span>
                                @if($handRaise->status === 'needs_revision')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> Ada Revisi
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Selesai Valid
                                </span>
                                @endif
                            </div>
                            <p class="whitespace-pre-line font-bold text-gray-900">{{ $handRaise->notes ?? $handRaise->reason ?? '-' }}</p>
                            @if($handRaise->presentation_date)
                            <div class="text-[11px] text-gray-500 flex items-center gap-2 flex-wrap">
                                <span>Jadwal: <strong>{{ $handRaise->presentation_date->format('d M Y') }}</strong></span>
                                <span>•</span>
                                <span>Mode: <strong>{{ ucfirst($handRaise->presentation_mode ?? 'Offline') }}</strong></span>
                                @if($handRaise->presentation_mode === 'online')
                                @php
                                $modalMeetUrl = $handRaise->meet_url ?: ($handRaise->user?->intern?->division?->meet_url ?? null);
                                @endphp
                                @if($modalMeetUrl)
                                <span>•</span>
                                <a href="{{ $modalMeetUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-bold underline">
                                    <i class="fa-solid fa-video text-[10px]"></i> Link GMeet
                                </a>
                                @endif
                                @endif
                            </div>
                            @endif
                            @elseif($handRaise->type === 'new_task')
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                    <i class="fa-solid fa-list-check text-[9px]"></i> Tugas Baru
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Selesai
                                </span>
                            </div>
                            @if($projectName)
                            <div class="text-xs font-bold text-emerald-900 flex items-center gap-1 pt-0.5">
                                <i class="fa-solid fa-list-check text-emerald-600 text-[10px]"></i>
                                <span>Judul Tugas: <strong>{{ $projectName }}</strong></span>
                            </div>
                            @endif
                            <p class="whitespace-pre-line text-gray-800 break-words text-xs leading-relaxed">{{ $handRaise->notes ?? $handRaise->reason ?? '-' }}</p>
                            @else
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    <i class="fa-solid fa-comments text-[9px]"></i> Bantuan Pertanyaan
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Terjawab
                                </span>
                            </div>
                            <p class="whitespace-pre-line text-gray-800 break-words text-xs leading-relaxed">{{ $handRaise->notes ?? $handRaise->reason ?? '-' }}</p>
                            @endif
                        </div>
                    </td>

                    <!-- Waktu & Petugas -->
                    <td class="py-3 px-3 align-top">
                        <div class="space-y-1">
                            <div class="text-xs font-semibold text-gray-800 flex items-center gap-1.5" title="{{ $handRaise->resolved_at ? $handRaise->resolved_at->format('d M Y, H:i') : '' }}">
                                <i class="fa-regular fa-calendar-check text-emerald-600 shrink-0"></i>
                                <span class="whitespace-nowrap">{{ $handRaise->resolved_at ? $handRaise->resolved_at->format('d M Y, H:i') : ($handRaise->updated_at ? $handRaise->updated_at->format('d M Y, H:i') : '-') }}</span>
                                @php
                                $resolvedTime = $handRaise->resolved_at ?? $handRaise->updated_at;
                                $shortResolved = $resolvedTime ? $formatShortTime($resolvedTime) : null;
                                @endphp
                                @if($shortResolved && $shortResolved !== '-')
                                <span class="text-[10px] text-gray-400 font-normal shrink-0">({{ $shortResolved }})</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-gray-500 flex items-center gap-1.5 truncate">
                                <i class="fa-solid fa-user-shield text-gray-400 shrink-0"></i>
                                <span class="truncate">Oleh: <strong>{{ $handRaise->resolver->name ?? 'Admin' }}</strong></span>
                            </div>
                        </div>
                    </td>
                </tr>
                @endif

                @empty
                @if($tab === 'presentation')
                <div class="w-full py-16 px-4 text-center bg-white rounded-2xl border border-dashed border-slate-300 shadow-2xs">
                    <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-amber-500 border border-amber-100">
                        <i class="fa-solid fa-chalkboard-user text-2xl"></i>
                    </div>
                    <p class="font-bold text-base text-gray-700">
                        Tidak ada jadwal presentasi aktif
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Pengajuan presentasi dari pemagang akan muncul di sini untuk dikonfirmasi dan direview.
                    </p>
                </div>
                @elseif($tab === 'question')
                <div class="w-full py-16 px-4 text-center bg-white rounded-2xl border border-dashed border-slate-300 shadow-2xs">
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-blue-500 border border-blue-100">
                        <i class="fa-solid fa-comments text-2xl"></i>
                    </div>
                    <p class="font-bold text-base text-gray-700">
                        Tidak ada pertanyaan aktif saat ini
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Semua peserta terpantau lancar dan tidak memiliki kendala teknis.
                    </p>
                </div>
                @elseif($tab === 'new_task')
                <div class="w-full py-16 px-4 text-center bg-white rounded-2xl border border-dashed border-slate-300 shadow-2xs">
                    <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-purple-500 border border-purple-100">
                        <i class="fa-solid fa-list-check text-2xl"></i>
                    </div>
                    <p class="font-bold text-base text-gray-700">
                        Tidak ada permintaan tugas baru
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Permintaan tugas baru dari pemagang yang telah menyelesaikan tugas sebelumnya akan muncul di sini.
                    </p>
                </div>
                @else
                <tr>
                    <td colspan="4" class="text-center py-16 text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mb-3 text-emerald-500 border border-emerald-100">
                                <i class="fa-solid fa-circle-check text-2xl"></i>
                            </div>
                            <p class="font-bold text-base text-gray-700">
                                @if(($historySubTab ?? '') === 'new_task')
                                Belum ada riwayat tugas baru selesai
                                @elseif(($historySubTab ?? '') === 'presentation')
                                Belum ada riwayat presentasi selesai
                                @else
                                Belum ada riwayat bantuan selesai
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                Riwayat sesi yang telah diselesaikan akan tercatat di sini.
                            </p>
                        </div>
                    </td>
                </tr>
                @endif
                @endforelse

                @once
                <script>
                    if (typeof window.toggleRowDropdown === 'undefined') {
                        window.toggleRowDropdown = function(event, btn) {
                            if (event) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                            const container = btn.closest('.dropdown-action-container');
                            if (!container) return;
                            const menu = container.querySelector('.dropdown-menu-list');
                            if (!menu) return;

                            const isCurrentlyOpen = !menu.classList.contains('hidden');

                            // Tutup semua dropdown lain yang terbuka dan bersihkan z-index
                            document.querySelectorAll('.dropdown-menu-list').forEach(m => m.classList.add('hidden'));
                            document.querySelectorAll('.raise-hand-card, .raise-hand-row, .dropdown-action-container').forEach(el => {
                                el.style.zIndex = '';
                            });

                            if (!isCurrentlyOpen) {
                                // Naikkan z-index parent card / row / container
                                const card = btn.closest('.raise-hand-card') || btn.closest('.raise-hand-row');
                                if (card) {
                                    card.style.zIndex = '50';
                                    card.style.position = 'relative';
                                }
                                container.style.zIndex = '60';

                                // Deteksi posisi di layar untuk smart placement (atas vs bawah)
                                const rect = btn.getBoundingClientRect();
                                const spaceBelow = window.innerHeight - rect.bottom;
                                const dropdownHeight = 175;

                                if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                                    menu.classList.remove('top-full', 'mt-1.5');
                                    menu.classList.add('bottom-full', 'mb-1.5');
                                } else {
                                    menu.classList.remove('bottom-full', 'mb-1.5');
                                    menu.classList.add('top-full', 'mt-1.5');
                                }

                                menu.classList.remove('hidden');
                            }
                        };

                        document.addEventListener('click', function(e) {
                            if (!e.target.closest('.dropdown-action-container')) {
                                document.querySelectorAll('.dropdown-menu-list').forEach(m => m.classList.add('hidden'));
                                document.querySelectorAll('.raise-hand-card, .raise-hand-row, .dropdown-action-container').forEach(el => {
                                    el.style.zIndex = '';
                                });
                            }
                        });
                    }

                    if (typeof window.handleCompletePresentation === 'undefined') {
                        window.handleCompletePresentation = function(event, status, el) {
                            if (status !== 'ready' && status !== 'needs_revision') {
                                if (event) {
                                    event.preventDefault();
                                    event.stopPropagation();
                                }

                                const form = el.tagName === 'FORM' ? el : el.closest('form');
                                const btn = form ? form.querySelector('button[type="submit"]') : el;
                                const dropdownContainer = el.closest('.dropdown-action-container');
                                const container = dropdownContainer || form || el.parentElement;
                                const row = el.closest('tr') || el.closest('.raise-hand-card') || el.closest('.bg-white');
                                const preReviewBtn = row ? row.querySelector('.btn-trigger-pre-review') : null;

                                if (!container) return false;

                                const existingNotes = container.querySelectorAll('.pres-status-warning-note');
                                existingNotes.forEach(n => n.remove());

                                const note = document.createElement('div');
                                note.className = 'pres-status-warning-note absolute -top-10 right-0 z-[100] px-3 py-1 bg-rose-600 text-white text-[11px] font-bold rounded-xl shadow-xl flex items-center gap-1.5 whitespace-nowrap pointer-events-none transition-all duration-200 transform scale-95 opacity-0';
                                note.innerHTML = `
                    <i class="fa-solid fa-circle-exclamation text-xs text-amber-200"></i>
                    <span>Harus mengubah status terlebih dahulu!</span>
                    <div class="absolute -bottom-1 right-6 w-2 h-2 bg-rose-600 transform rotate-45"></div>
                `;

                                container.classList.add('relative');
                                container.appendChild(note);

                                requestAnimationFrame(() => {
                                    note.classList.remove('scale-95', 'opacity-0');
                                    note.classList.add('scale-100', 'opacity-100');
                                });

                                if (btn) btn.classList.add('ring-2', 'ring-rose-500');
                                if (preReviewBtn) preReviewBtn.classList.add('ring-2', 'ring-sky-400', 'animate-pulse');

                                setTimeout(() => {
                                    note.classList.remove('scale-100', 'opacity-100');
                                    note.classList.add('scale-95', 'opacity-0');
                                    if (btn) btn.classList.remove('ring-2', 'ring-rose-500');
                                    if (preReviewBtn) preReviewBtn.classList.remove('ring-2', 'ring-sky-400', 'animate-pulse');
                                    setTimeout(() => {
                                        if (note.parentNode) note.parentNode.removeChild(note);
                                    }, 200);
                                }, 1500);

                                return false;
                            }
                            return true;
                        };
                    }
                </script>
                @endonce