<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBloqueadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bloqueados', function (Blueprint $table) {
            $table->id();
            $table->string('NIT',16);
            $table->integer('Transaccion');
            $table->Integer('Documento');
            $table->integer('estado');
            $table->Integer('Procesar');
            $table->string('CIA');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bloqueados');
    }
}
