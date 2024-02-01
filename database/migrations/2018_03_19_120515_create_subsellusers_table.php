<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubsellusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subsellusers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('subseller_username');
            $table->string('subseller_email');
            $table->string('subseller_password');
            $table->string('salt');
            $table->string('activated');
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
        Schema::dropIfExists('subsellusers');
    }
}
