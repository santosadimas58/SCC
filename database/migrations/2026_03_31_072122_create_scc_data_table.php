<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scc_data', function (Blueprint $table) {
            $table->id();
            $table->float('vpv')->comment('Tegangan panel surya (V)');
            $table->float('ipv')->comment('Arus panel surya (A)');
            $table->float('vbat')->comment('Tegangan baterai (V)');
            $table->float('ibat')->comment('Arus baterai (A)');
            $table->float('soc')->comment('State of Charge (%)');
            $table->float('duty_cycle')->comment('Duty cycle PWM (%)');
            $table->string('fase')->comment('Bulk / Absorption / Float');
            $table->string('label_e')->comment('Label fuzzy error: NB/NS/ZO/PS/PB');
            $table->string('label_de')->comment('Label fuzzy delta error');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scc_data');
    }
};

