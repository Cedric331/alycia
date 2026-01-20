<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('rencontre_primary_label')->nullable()->after('action_label');
            $table->string('rencontre_secondary_label')->nullable()->after('rencontre_primary_label');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['rencontre_primary_label', 'rencontre_secondary_label']);
        });
    }
};

