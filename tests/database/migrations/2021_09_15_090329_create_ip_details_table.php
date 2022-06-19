<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIpDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ip_details', function (Blueprint $table) {
            $table->string('ip');
            $table->string('continent')->nullable();
            $table->text('security')->nullable();
            $table->string('country')->nullable();
            $table->string('timezone')->nullable();
            $table->string('internetProvider')->nullable();
            $table->timestamp('visited_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ip_details', function (Blueprint $table) {
            $table->dropIfExists('ip_details');
        });
    }
}
