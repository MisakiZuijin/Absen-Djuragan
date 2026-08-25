<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_broadcast_images_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kode ini sudah benar.
        Schema::create('broadcast_images', function (Blueprint $table) {
            $table->id(); // Membuat primary key 'id'
            
            // Membuat foreign key ke tabel 'broadcasts'
            // 'onDelete('cascade')' sangat penting: jika pengumuman dihapus,
            // semua gambarnya juga akan otomatis terhapus dari tabel ini.
            $table->foreignId('broadcast_id')->constrained()->onDelete('cascade');
            
            $table->string('image'); // Kolom untuk menyimpan nama file gambar
            $table->timestamps(); // Membuat kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kode ini juga sudah benar untuk proses rollback.
        Schema::dropIfExists('broadcast_images');
    }
};