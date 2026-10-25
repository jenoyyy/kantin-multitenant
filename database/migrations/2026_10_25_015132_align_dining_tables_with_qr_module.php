<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->string('code', 30)->nullable()->after('canteen_id');
            $table->string('zone', 60)->nullable()->after('label');
        });

        // Lebarkan label dan ubah status dari enum menjadi string biasa
        // (change() jalan di MySQL maupun SQLite, tidak memakai sintaks khusus MySQL).
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->string('label', 60)->change();
            $table->string('status', 20)->default('active')->change();
        });

        // available/occupied -> active
        DB::table('dining_tables')
            ->whereIn('status', ['available', 'occupied'])
            ->update(['status' => 'active']);

        // Isi code untuk meja yang sudah ada: M01, M02, ...
        foreach (DB::table('dining_tables')->whereNull('code')->get() as $row) {
            DB::table('dining_tables')->where('id', $row->id)->update([
                'code' => 'M'.str_pad((string) $row->id, 2, '0', STR_PAD_LEFT),
            ]);
        }

        Schema::table('dining_tables', function (Blueprint $table) {
            $table->unique(['canteen_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->dropUnique(['canteen_id', 'code']);
        });

        Schema::table('dining_tables', function (Blueprint $table) {
            $table->dropColumn(['code', 'zone']);
        });

        DB::table('dining_tables')->where('status', 'active')->update(['status' => 'available']);

        Schema::table('dining_tables', function (Blueprint $table) {
            $table->string('label', 20)->change();
            $table->enum('status', ['available', 'occupied', 'inactive'])->default('available')->change();
        });
    }
};
