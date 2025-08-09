<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('middle_name')->nullable()->after('last_name');
            $table->integer('age')->nullable()->after('middle_name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('age');
            $table->string('photo')->nullable()->after('gender');
            $table->string('company_name')->nullable()->after('photo');
            $table->string('position')->nullable()->after('company_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name', 
                'middle_name',
                'age',
                'gender',
                'photo',
                'company_name',
                'position'
            ]);
        });
    }
};
