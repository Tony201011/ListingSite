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

            // Core fields
            $table->string('postcode', 4)->index();        // Always 4 digits (store as string)
            $table->string('suburb', 150)->index();        // Suburb / locality / university name
            $table->string('state', 10)->index();          // ACT, NSW, VIC, etc.

            // Geo fields (used for geo search)
            $table->decimal('latitude', 10, 7)->nullable()->index();
            $table->decimal('longitude', 10, 7)->nullable()->index();

            // Extra fields from Excel
            $table->string('postcode_type', 50)->nullable()->index();
            // e.g. "Delivery Area", "PO Box"

            $table->string('electoral_district', 150)->nullable()->index();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('postcodes');
    }
}
