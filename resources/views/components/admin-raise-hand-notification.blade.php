{{-- File: resources/views/components/admin-raise-hand-notification.blade.php --}}
<div class="admin-notification-container">
    {{-- Badge Notifikasi untuk Admin --}}
    <a href="{{ route('admin.raiseHand.index') }}" class="position-relative text-decoration-none text-dark mx-2">
        <i class="fas fa-hands-helping fa-lg"></i>
        @php
            use App\Models\HandRaise;
            $raiseHandCount = HandRaise::where('is_raised', true)->count();
        @endphp
        <span
            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $raiseHandCount > 0 ? '' : 'd-none' }}"
            id="raiseHandCount">
            {{ $raiseHandCount }}
        </span>
    </a>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            // Untuk admin - update notifikasi real-time
            function updateRaiseHandNotifications() {
                $.ajax({
                    url: '{{ route("admin.raiseHand.count") }}',
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        const countBadge = $('#raiseHandCount');

                        if (response.success && response.count > 0) {
                            countBadge.text(response.count).removeClass('d-none');
                        } else {
                            countBadge.addClass('d-none');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error fetching raise hand count:', error);

                        // Fallback: tetap coba update setiap 30 detik jika ada error
                        setTimeout(updateRaiseHandNotifications, 30000);
                    },
                    complete: function () {
                        // Update setiap 10 detik
                        setTimeout(updateRaiseHandNotifications, 10000);
                    }
                });
            }

            // Jalankan pertama kali setelah delay kecil
            setTimeout(updateRaiseHandNotifications, 2000);

            // Juga update saat halaman menjadi visible kembali (ketika user kembali ke tab)
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    updateRaiseHandNotifications();
                }
            });
        });

        let lastId = null;

        // 🔹 Ubah interval dari 1000 (1 detik) → 2000 (2 detik)
        setInterval(() => {
            fetch('/api/raise-hand/latest')
                .then(res => res.json())
                .then(data => {
                    if (data && data.id !== lastId) {
                        lastId = data.id;

                        // tampilkan popup Swal
                        Swal.fire({
                            title: 'Permintaan Bantuan Baru!',
                            html: `<strong>${data.name}</strong> dari <em>${data.school}</em>`,
                            icon: 'info',
                            timer: 1000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    }
                })
                .catch(err => console.error(err));
        }, 15000); // cek tiap 15 detik
    </script>
@endpush
