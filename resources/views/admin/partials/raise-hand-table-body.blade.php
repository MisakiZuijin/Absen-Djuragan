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
                @endphp

                <tr class="raise-hand-row group hover:bg-slate-50/70 transition-all duration-150 {{ $isToday && $handRaise->type === 'presentation' && $handRaise->is_raised ? 'bg-red-50/30' : '' }}"
                    data-name="{{ strtolower($userName) }}"
                    data-school="{{ strtolower($userSchool) }}"
                    data-phone="{{ $userPhone }}"
                    data-type="{{ $handRaise->type ?? 'question' }}"
                    data-status="{{ $handRaise->status ?? 'pending' }}"
                    data-notes="{{ strtolower($handRaise->notes ?? $handRaise->reason ?? '') }}"
                    data-id="{{ $handRaise->id }}">

                    <!-- Index -->
                    <td class="py-4 px-4 text-center align-top">
                        <div class="w-7 h-7 mx-auto rounded-lg bg-gray-100 border border-gray-200/70 flex items-center justify-center text-xs font-bold text-gray-700 shadow-2xs">
                            {{ $index + 1 }}
                        </div>
                    </td>

                    <!-- Peserta Info -->
                    <td class="py-4 px-4 align-top">
                        <div class="flex items-start gap-3">
                            <div class="relative flex-shrink-0">
                                <div class="w-10 h-10 rounded-xl {{ $isToday && $handRaise->type === 'presentation' && $handRaise->is_raised ? 'bg-red-600 ring-2 ring-red-200' : 'bg-slate-800' }} flex items-center justify-center text-white font-extrabold text-sm shadow-xs">
                                    {{ $initial }}
                                </div>
                                @if($handRaise->is_raised)
                                @if($handRaise->status === 'urgent' || $isToday)
                                <div class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-red-500 rounded-full border-2 border-white shadow-xs"></div>
                                @else
                                <div class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-amber-400 rounded-full border-2 border-white shadow-xs"></div>
                                @endif
                                @else
                                <div class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-emerald-500 rounded-full border-2 border-white shadow-xs"></div>
                                @endif
                            </div>

                            <div class="min-w-0 space-y-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-extrabold text-xs text-gray-900 user-name leading-tight">{{ $userName }}</span>
                                    @if($userDivision)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $userDivision }}
                                    </span>
                                    @endif
                                </div>

                                <div class="text-xs text-gray-500 user-school flex items-center gap-1.5">
                                    <i class="fa-solid fa-graduation-cap text-gray-400 text-[11px] flex-shrink-0"></i>
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
                                {{-- Nama project sebelumnya dihilangkan dari kolom Peserta Info agar tidak dobel/bocor di permintaan tugas & presentasi --}}

                                {{-- Link tugas dihilangkan dari Peserta Info agar tidak duplikat di presentasi & tidak muncul di history --}}

                                <div class="text-[11px] text-gray-400 flex items-center gap-2 pt-0.5">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-phone text-[9px]"></i>
                                        <span>{{ $userPhone }}</span>
                                    </span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1" title="{{ $handRaise->created_at ? $handRaise->created_at->format('d M Y, H:i') : '' }}">
                                        <i class="fa-regular fa-clock text-[9px]"></i>
                                        <span>{{ $shortCreatedAt }}</span>
                                    </span>
                                </div>

                                @if($internShiftText && $tab !== 'presentation' && ($handRaise->type ?? '') !== 'presentation')
                                <div class="pt-1">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-amber-50 text-amber-900 border border-amber-200/90 shadow-2xs"
                                        title="Jadwal Shift Pemagang: {{ $internShiftText }}">
                                        <i class="fa-regular fa-clock text-amber-600 text-[10px]"></i>
                                        <span>Shift: <strong class="font-bold text-amber-950">{{ $internShiftText }}</strong></span>
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>

                    @if($tab === 'question')
                    <!-- Catatan Pertanyaan -->
                    <td class="py-4 px-4 align-top">
                        <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 text-xs text-gray-800 leading-relaxed max-w-lg space-y-1">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-blue-700">
                                <i class="fa-solid fa-circle-question"></i>
                                <span>Detail Pertanyaan / Kendala:</span>
                            </div>
                            <p class="whitespace-pre-line text-gray-800 font-medium">
                                {{ $handRaise->notes ?? $handRaise->reason ?? 'Tidak ada keterangan detail' }}
                            </p>
                        </div>
                    </td>

                    <!-- Status -->
                    <td class="py-4 px-4 align-top">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-900 border border-amber-200 shadow-2xs">
                            <i class="fa-solid fa-hand-paper text-amber-600 animate-pulse"></i>
                            <span>Menunggu Bantuan</span>
                        </span>
                    </td>

                    <!-- Aksi: Hanya Tombol Selesai (Tanpa Modal) -->
                    <td class="py-4 px-4 align-top text-right">
                        <form action="{{ route('admin.raiseHand.confirm', $handRaise->id) }}" method="POST" class="inline-block m-0 p-0" onsubmit="return confirm('Selesaikan bantuan / pertanyaan dari {{ addslashes($userName) }}?');">
                            @csrf
                            <input type="hidden" name="action" value="complete_question">
                            <input type="hidden" name="tab" value="question">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer whitespace-nowrap"
                                title="Selesaikan bantuan/pertanyaan peserta">
                                <i class="fa-solid fa-check"></i>
                                <span>Selesai</span>
                            </button>
                        </form>
                    </td>

                    @elseif($tab === 'new_task')
                    <!-- Keterangan Permintaan Tugas -->
                    <td class="py-4 px-4 align-top">
                        <div class="space-y-2 max-w-lg">
                            <div class="p-3 bg-purple-50/60 rounded-xl border border-purple-100 text-xs text-gray-800 leading-relaxed space-y-1">
                                <div class="flex items-center gap-1.5 text-[11px] font-bold text-purple-700">
                                    <i class="fa-solid fa-list-check"></i>
                                    <span>Rincian Tugas & Permintaan:</span>
                                </div>
                                <p class="whitespace-pre-line text-gray-800 font-medium">
                                    {{ $handRaise->notes ?? $handRaise->reason ?? 'Tugas sebelumnya selesai, menunggu instruksi tugas berikutnya' }}
                                </p>
                            </div>

                            @if(!empty($handRaise->admin_response))
                            <div class="p-3 bg-emerald-50/80 rounded-xl border border-emerald-200 text-xs text-gray-800 space-y-1">
                                <div class="flex items-center justify-between text-[10px] font-bold text-emerald-800">
                                    <span class="flex items-center gap-1"><i class="fa-solid fa-clipboard-list"></i> Tugas yang Diberikan:</span>
                                </div>
                                <p class="text-[11px] text-gray-700 line-clamp-2 leading-relaxed italic">
                                    "{{ $handRaise->admin_response }}"
                                </p>
                            </div>
                            @endif
                        </div>
                    </td>

                    <!-- Status -->
                    <td class="py-4 px-4 align-top">
                        @if($handRaise->status === 'in_progress')
                        <div class="space-y-1">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                                <span>Tugas Diberikan</span>
                            </span>
                            <span class="block text-[10px] text-gray-500 font-medium pl-1">Sedang dikerjakan</span>
                        </div>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200 shadow-2xs">
                            <i class="fa-solid fa-hourglass-half text-purple-600"></i>
                            <span>Menunggu Tugas</span>
                        </span>
                        @endif
                    </td>

                    <!-- Aksi -->
                    <td class="py-4 px-4 align-top text-right">
                        <div class="flex items-center justify-end gap-2 flex-wrap">
                            @if($handRaise->status === 'in_progress')
                            <!-- Button 1: Edit Tugas -->
                            <button type="button"
                                class="btn-trigger-edit-task inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer whitespace-nowrap"
                                data-id="{{ $handRaise->id }}"
                                data-name="{{ $userName }}"
                                data-notes="{{ $cleanNotes }}"
                                data-response="{{ $cleanResponse }}"
                                data-division="{{ addslashes($userDivision ?? '') }}"
                                data-project-title="{{ addslashes($projectName ?? '') }}"
                                onclick="if(window.openEditTaskModal) { window.openEditTaskModal(this.dataset.id, this.dataset.name, this.dataset.notes, this.dataset.response, this.dataset.division, this.dataset.projectTitle); }"
                                title="Edit arahan atau instruksi tugas">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Edit Tugas</span>
                            </button>

                            <!-- Button 2: Selesai -->
                            <form method="POST" action="{{ route('admin.raiseHand.confirm', $handRaise->id) }}" class="inline m-0 p-0">
                                @csrf
                                <input type="hidden" name="action" value="complete_task">
                                <input type="hidden" name="tab" value="new_task">
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer whitespace-nowrap"
                                    title="Selesaikan sesi raise hand tugas">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Selesai</span>
                                </button>
                            </form>
                            @else
                            <!-- Button: Beri Tugas -->
                            <button type="button"
                                class="btn-trigger-give-task inline-flex items-center gap-1.5 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer whitespace-nowrap"
                                data-id="{{ $handRaise->id }}"
                                data-name="{{ $userName }}"
                                data-notes="{{ $cleanNotes }}"
                                data-division="{{ addslashes($userDivision ?? '') }}"
                                onclick="if(window.openGiveTaskModal) { window.openGiveTaskModal(this.dataset.id, this.dataset.name, this.dataset.notes, this.dataset.division); }"
                                title="Beri instruksi tugas baru">
                                <i class="fa-solid fa-plus-circle"></i>
                                <span>Beri Tugas</span>
                            </button>
                            @endif
                        </div>
                    </td>

                    @elseif($tab === 'presentation')
                    <!-- Materi Presentasi -->
                    <td class="py-4 px-4 align-top">
                        <div class="space-y-1.5 max-w-md">
                            <div class="font-bold text-xs text-gray-900 leading-snug">
                                {{ $handRaise->notes ?? $handRaise->reason ?? 'Presentasi Modul Magang' }}
                            </div>
                            <div class="text-[11px] text-gray-400">
                                Diajukan: {{ $handRaise->created_at ? $handRaise->created_at->format('d M Y, H:i') : '-' }}
                            </div>

                            @php
                            $isFigma = str_contains(strtolower($gitRepoUrl ?? ''), 'figma.com');
                            $isGit = str_contains(strtolower($gitRepoUrl ?? ''), 'github.com') || str_contains(strtolower($gitRepoUrl ?? ''), 'gitlab.com');
                            // Link tugas hanya untuk divisi Programmer atau UI/UX
                            $hasTaskLink = !empty($gitRepoUrl) && ($isProgrammerUser || $isUiUxUser);
                            @endphp

                            @if($hasTaskLink)
                            <div class="pt-1.5 flex items-center gap-2 flex-wrap">
                                <a href="{{ $gitRepoUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-2xs {{ $isFigma || $isUiUxUser ? 'bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200' : 'bg-gray-900 text-white hover:bg-black' }}"
                                    title="Buka Link Tugas ({{ $gitRepoUrl }})">
                                    @if($isFigma || $isUiUxUser)
                                    <i class="fa-brands fa-figma text-purple-600 text-xs"></i>
                                    @else
                                    <i class="fa-brands fa-github text-emerald-400 text-xs"></i>
                                    @endif
                                    <span>Link Tugas</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] opacity-70"></i>
                                </a>
                                <span class="text-[11px] text-gray-400 truncate max-w-[180px] font-normal" title="{{ $gitRepoUrl }}">
                                    {{ $gitRepoUrl }}
                                </span>
                            </div>
                            @endif

                            @if(!empty($handRaise->performance_notes))
                            <div class="mt-1.5 p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-xs space-y-0.5">
                                <span class="text-[10px] font-bold text-amber-800 flex items-center gap-1">
                                    <i class="fa-solid fa-clipboard-list text-amber-600"></i> Catatan Revisi Pemagang:
                                </span>
                                <p class="text-[11px] text-amber-950 line-clamp-3 leading-relaxed whitespace-pre-line">
                                    {{ $handRaise->performance_notes }}
                                </p>
                            </div>
                            @elseif(!empty($handRaise->admin_response))
                            <div class="mt-1.5 p-2.5 bg-orange-50/80 rounded-xl border border-orange-200 text-xs space-y-0.5">
                                <span class="text-[10px] font-bold text-orange-800 flex items-center gap-1">
                                    <i class="fa-solid fa-clipboard-check"></i> Catatan Evaluasi:
                                </span>
                                <p class="text-[11px] text-orange-950 line-clamp-2 italic leading-relaxed">
                                    "{{ $handRaise->admin_response }}"
                                </p>
                            </div>
                            @endif
                        </div>
                    </td>

                    <!-- Jadwal & Mode -->
                    <td class="py-4 px-4 align-top">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-1.5 text-xs font-bold {{ $isToday ? 'text-red-600' : 'text-gray-800' }}">
                                <i class="fa-regular fa-calendar {{ $isToday ? 'text-red-500' : 'text-gray-400' }}"></i>
                                <span>{{ $handRaise->presentation_date ? $handRaise->presentation_date->format('d M Y') : '-' }}</span>
                                @if($isToday)
                                <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-red-600 text-white tracking-wide">HARI INI</span>
                                @endif
                            </div>
                            @if($internShiftText)
                            <div class="text-[11px] text-gray-600 flex items-center gap-1.5 font-medium">
                                <i class="fa-solid fa-business-time text-gray-400 text-[10px]"></i>
                                <span>Shift: <strong class="text-gray-800 font-semibold">{{ $internShiftText }}</strong></span>
                            </div>
                            @endif
                            <div>
                                @if($handRaise->presentation_mode === 'online')
                                @php
                                $adminMeetUrl = $handRaise->meet_url ?: ($handRaise->user?->intern?->division?->meet_url ?? null);
                                @endphp
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                        <i class="fa-solid fa-video text-sky-500 text-[10px]"></i> Online (GMeet)
                                    </span>
                                    @if($adminMeetUrl)
                                    <a href="{{ $adminMeetUrl }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-xs"
                                        title="Buka Ruang Google Meet: {{ $adminMeetUrl }}">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i> Buka Meet
                                    </a>
                                    @endif
                                </div>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-50 text-slate-700 border border-slate-200">
                                    <i class="fa-solid fa-building text-slate-500 text-[10px]"></i> Tatap Muka
                                </span>
                                @endif
                            </div>
                        </div>
                    </td>

                    <!-- Urgensi & Status Review -->
                    <td class="py-4 px-4 align-top">
                        <div class="space-y-1">
                            @if($handRaise->status === 'needs_revision')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-[11px]"></i>
                                <span>Perlu Perbaikan</span>
                            </span>
                            @elseif($handRaise->status === 'ready')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-[11px]"></i>
                                <span>Sudah Presentasi</span>
                            </span>
                            @elseif($isToday || $handRaise->status === 'urgent')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-extrabold bg-red-100 text-red-700 border border-red-300 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                <span>URGENT HARI INI</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                <i class="fa-regular fa-clock text-blue-500 text-[11px]"></i>
                                <span>Terjadwal</span>
                            </span>
                            @endif
                        </div>
                    </td>

                    <!-- Aksi: Status Presentasi & Selesai -->
                    <td class="py-4 px-4 align-top text-right">
                        <div class="flex items-center justify-end gap-2 flex-wrap">
                            <!-- Button 1: Sudah Presentasi (Popup Pilihan: Selesai Valid atau Ada Catatan Revisi) -->
                            <button type="button"
                                class="btn-trigger-pre-review inline-flex items-center gap-1.5 px-3.5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer whitespace-nowrap"
                                data-id="{{ $handRaise->id }}"
                                data-name="{{ $userName }}"
                                data-title="{{ $cleanNotes }}"
                                data-status="{{ $handRaise->status ?? 'pending' }}"
                                onclick="if(window.openPrePresentationModal) { window.openPrePresentationModal(this.dataset.id, this.dataset.name, this.dataset.title, this.dataset.status); }"
                                title="Pemeriksaan status presentasi: Selesai Valid atau Ada Revisi">
                                <i class="fa-solid fa-chalkboard-user"></i>
                                <span>Sudah Presentasi</span>
                            </button>

                            <!-- Button 2: Selesai (Hanya jika status sudah lulus / revisi) -->
                            <form method="POST" action="{{ route('admin.raiseHand.confirm', $handRaise->id) }}" class="inline m-0 p-0 relative" data-status="{{ $handRaise->status ?? '' }}" onsubmit="return handleCompletePresentation(event, this.dataset.status, this);">
                                @csrf
                                <input type="hidden" name="action" value="complete_presentation">
                                <input type="hidden" name="tab" value="presentation">
                                <button type="submit"
                                    onclick="return handleCompletePresentation(event, this.form.dataset.status, this);"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer whitespace-nowrap"
                                    title="Selesaikan presentasi dan pindahkan ke riwayat">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Selesai</span>
                                </button>
                            </form>
                        </div>
                    </td>

                    @elseif($tab === 'history')
                    <!-- Detail / Materi / Permintaan -->
                    <td class="py-4 px-4 align-top">
                        <div class="text-xs text-gray-900 font-medium leading-relaxed max-w-sm space-y-1.5">
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
                            <p class="whitespace-pre-line text-gray-800">{{ $handRaise->notes ?? $handRaise->reason ?? '-' }}</p>
                            @else
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    <i class="fa-solid fa-comments text-[9px]"></i> Bantuan Pertanyaan
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Terjawab
                                </span>
                            </div>
                            <p class="whitespace-pre-line text-gray-800">{{ $handRaise->notes ?? $handRaise->reason ?? '-' }}</p>
                            @endif
                        </div>
                    </td>

                    <!-- Waktu & Petugas -->
                    <td class="py-4 px-4 align-top">
                        <div class="space-y-1">
                            <div class="text-xs font-semibold text-gray-800 flex items-center gap-1.5" title="{{ $handRaise->resolved_at ? $handRaise->resolved_at->format('d M Y, H:i') : '' }}">
                                <i class="fa-regular fa-calendar-check text-emerald-600"></i>
                                <span>{{ $handRaise->resolved_at ? $handRaise->resolved_at->format('d M Y, H:i') : ($handRaise->updated_at ? $handRaise->updated_at->format('d M Y, H:i') : '-') }}</span>
                                @php
                                $resolvedTime = $handRaise->resolved_at ?? $handRaise->updated_at;
                                $shortResolved = $resolvedTime ? $formatShortTime($resolvedTime) : null;
                                @endphp
                                @if($shortResolved && $shortResolved !== '-')
                                <span class="text-[10px] text-gray-400 font-normal">({{ $shortResolved }})</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-gray-500 flex items-center gap-1.5">
                                <i class="fa-solid fa-user-shield text-gray-400"></i>
                                <span>Oleh: <strong>{{ $handRaise->resolver->name ?? 'Admin' }}</strong></span>
                            </div>
                        </div>
                    </td>

                    <!-- Tanggapan & Evaluasi -->
                    <td class="py-4 px-4 align-top">
                        @php
                        $responseNote = $handRaise->admin_response ?? $handRaise->performance_notes;
                        @endphp

                        <div class="space-y-2">
                            @if($handRaise->type === 'presentation' && $handRaise->performance_rating !== null)
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $handRaise->performance_rating >= 80 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($handRaise->performance_rating >= 60 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-red-100 text-red-800 border border-red-200') }}">
                                    <i class="fa-solid fa-star text-amber-500 mr-1 text-[10px]"></i>
                                    Nilai: {{ number_format($handRaise->performance_rating, 0) }} / 100
                                </span>
                            </div>
                            @endif

                            @if($responseNote)
                            <div class="p-2.5 bg-emerald-50/70 border border-emerald-200 rounded-xl text-xs text-gray-800 space-y-1">
                                <div class="text-[10px] font-bold text-emerald-800 flex items-center justify-between">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-reply"></i>
                                        {{ $handRaise->type === 'presentation' ? 'Catatan Evaluasi:' : ($handRaise->type === 'new_task' ? 'Instruksi Tugas:' : 'Tanggapan Solusi:') }}
                                    </span>
                                    <button type="button"
                                        class="btn-trigger-detail text-blue-600 hover:text-blue-800 font-bold underline text-[10px] cursor-pointer"
                                        data-name="{{ $userName }}"
                                        data-rating="{{ $handRaise->performance_rating !== null ? number_format($handRaise->performance_rating, 0) : '' }}"
                                        data-response="{{ $cleanResponse }}"
                                        data-title="{{ $cleanNotes }}"
                                        onclick="if(window.viewEvaluationDetails) { window.viewEvaluationDetails(this.dataset.name, this.dataset.rating, this.dataset.response, this.dataset.title); }"
                                        title="Lihat Selengkapnya">
                                        Detail
                                    </button>
                                </div>
                                <p class="text-[11px] text-gray-700 line-clamp-2 italic leading-relaxed">"{{ $responseNote }}"</p>
                            </div>
                            @else
                            <span class="text-xs text-gray-400 italic flex items-center gap-1">
                                <i class="fa-solid fa-check text-emerald-500"></i> Telah diselesaikan
                            </span>
                            @endif
                        </div>
                    </td>
                    @endif
                </tr>

                @empty
                <tr>
                    <td colspan="{{ $tab === 'presentation' ? 6 : 5 }}" class="text-center py-16 text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mb-3 text-emerald-500 border border-emerald-100">
                                <i class="fa-solid fa-circle-check text-2xl"></i>
                            </div>
                            <p class="font-bold text-base text-gray-700">
                                @if($tab === 'question')
                                Tidak ada pertanyaan aktif saat ini
                                @elseif($tab === 'new_task')
                                Tidak ada permintaan tugas baru
                                @elseif($tab === 'presentation')
                                Tidak ada jadwal presentasi aktif
                                @else
                                @if(($historySubTab ?? '') === 'new_task')
                                Belum ada riwayat tugas baru selesai
                                @elseif(($historySubTab ?? '') === 'presentation')
                                Belum ada riwayat presentasi selesai
                                @else
                                Belum ada riwayat bantuan selesai
                                @endif
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                @if($tab !== 'history')
                                Semua peserta terpantau lancar dan tidak memiliki kendala.
                                @else
                                Riwayat sesi yang telah diselesaikan akan tercatat di sini.
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse

                @once
                <script>
                    if (typeof window.handleCompletePresentation === 'undefined') {
                        window.handleCompletePresentation = function(event, status, el) {
                            if (status !== 'ready' && status !== 'needs_revision') {
                                if (event) {
                                    event.preventDefault();
                                    event.stopPropagation();
                                }

                                const form = el.tagName === 'FORM' ? el : el.closest('form');
                                const btn = form ? form.querySelector('button[type="submit"]') : el;
                                const container = form || el.parentElement;
                                const row = el.closest('tr');
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
                                }, 1000);

                                return false;
                            }
                            return true;
                        };
                    }
                </script>
                @endonce