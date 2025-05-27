<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('version');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('yearly_price', 10, 2);
            $table->json('features');
            $table->boolean('is_active')->default(true);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->string('paypal_monthly_plan_id')->nullable();
            $table->string('paypal_yearly_plan_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['plan_id', 'version']);
        });

        // Add version reference to subscriptions table
        // Initial versions will be created in a seeder instead
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_version_id')->nullable()->after('plan_id')->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['plan_version_id']);
            $table->dropColumn('plan_version_id');
        });

        Schema::dropIfExists('plan_versions');
    }
};
