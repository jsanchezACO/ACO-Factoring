<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ListarFactura extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('listar_facturas', function (Blueprint $table) {
            $table->id();
            $table->integer('Idtransaccion');
            $table->string('NIT',16);
            $table->string('RazonSocial',100);
            $table->string('Sucursal',3);
            $table->integer('Cuenta');
            $table->string('CO');
            $table->string('Prefijo',10);
            $table->string('Factura',10);
            $table->string('FechaFactura',8);
            $table->string('Vencimiento',8);
            $table->string('PP',8);
            $table->float('ValorFactura');
            $table->float('Impuesto');
            $table->float('Base');
            $table->string('Moneda',4);
            $table->Integer('Documento');
            $table->Integer('rowid_sa');
            $table->string('DB');
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
        Schema::dropIfExists('listar_facturas');
    }
}
