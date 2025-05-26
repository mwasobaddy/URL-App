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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_version_id')->nullable()->after('plan_id')->constrained();
        });

        // Create initial version for all existing plans
        $plans = DB::table('plans')->get();
        foreach ($plans as $plan) {
            DB::table('plan_versions')->insert([
                'plan_id' => $plan->id,
                'version' => '1.0.0',
                'name' => $plan->name,
                'description' => $plan->description,
                'monthly_price' => $plan->monthly_price,
                'yearly_price' => $plan->yearly_price,
                'features' => $plan->features,
                'is_active' => true,
                'valid_from' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Get the inserted version ID
            $versionId = DB::table('plan_versions')
                ->where('plan_id', $plan->id)
                ->value('id');
                
            // Update existing subscriptions to use this version
            DB::table('subscriptions')
                ->where('plan_id', $plan->id)
                ->update(['plan_version_id' => $versionId]);
        }
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
