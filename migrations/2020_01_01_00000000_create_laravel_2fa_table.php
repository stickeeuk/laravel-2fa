<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaravel2faTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('laravel-2fa.table_name'), function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('user');
            $table->boolean('enabled')->default(false);
            $table->text('data')->nullable();
            $table->timestamps();

            $table->unique(['user_type', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('laravel-2fa.table_name'));
    }
}
