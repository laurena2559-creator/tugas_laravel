<?php

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
        Schema::table('items', function (Blueprint $table) {
            // Tambahkan kolom untuk jumlah barang rusak dan catatan
            if (!Schema::hasColumn('items', 'damaged_count')) {
                $table->integer('damaged_count')->default(0)->after('stock');
            }

            if (!Schema::hasColumn('items', 'notes')) {
                $table->text('notes')->nullable()->after('damaged_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Hapus kolom jika ada
            if (Schema::hasColumn('items', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('items', 'damaged_count')) {
                $table->dropColumn('damaged_count');
            }
        });
    }
};
