<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostcodesTable extends Migration
{
    public function up()
    {
        Schema::create('postcodes', function (Blueprint $table) {
            $table->id();
            $table->string('state', 10)->index();          // e.g. ACT, NSW, VIC
            $table->string('city_region', 100)->nullable(); // City or region name
            $table->string('suburb', 100)->index();        // Suburb/locality name
            $table->string('postcode', 10)->index();       // Postcode as string (preserve leading zeros)
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('postcodes');
    }
}
