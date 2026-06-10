<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scc_data', function (Blueprint $table) {
            $table->string('load_name')->nullable()->after('label_de');
            $table->string('load_status')->nullable()->after('load_name');
            $table->float('load_power')->nullable()->after('load_status');
            $table->float('load_current')->nullable()->after('load_power');
            $table->float('net_power')->nullable()->after('load_current');
            $table->string('load_reason')->nullable()->after('net_power');
        });
    }

    public function down(): void
    {
        Schema::table('scc_data', function (Blueprint $table) {
            $table->dropColumn([
                'load_name',
                'load_status',
                'load_power',
                'load_current',
                'net_power',
                'load_reason',
            ]);
        });
    }
};
