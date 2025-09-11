<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id(); // int(11) auto increment
            $table->string('api_name', 225);             // NOT NULL
            $table->text('api_url');                     // NOT NULL
            $table->string('api_key', 225);              // NOT NULL
            $table->integer('api_type');                 // int(11) NOT NULL
            $table->double('api_limit')->default(0);     // default 0
            $table->enum('currency', ['INR','USD'])->nullable();
            $table->enum('api_alert', ['1','2'])->default('2');
            $table->enum('status', ['1','2'])->default('2');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
