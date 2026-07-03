<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('location');
            $table->date('birth_date')->nullable()->after('bio');
            $table->string('zip_code', 10)->nullable()->after('birth_date');
            $table->string('street')->nullable()->after('zip_code');
            $table->string('number', 20)->nullable()->after('street');
            $table->string('complement')->nullable()->after('number');
            $table->string('neighborhood')->nullable()->after('complement');
            $table->string('city')->nullable()->after('neighborhood');
            $table->string('state', 2)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio', 'birth_date', 'zip_code', 'street', 'number',
                'complement', 'neighborhood', 'city', 'state',
            ]);
        });
    }
};
