<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('flag')->nullable();
            $table->string('phone_code', 10)->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_source')->default(false)->comment('Is this a product source country');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Insert default countries
        DB::table('countries')->insert([
            [
                'code' => 'AE',
                'name' => 'United Arab Emirates',
                'name_ar' => 'الإمارات العربية المتحدة',
                'flag' => '🇦🇪',
                'phone_code' => '+971',
                'currency_code' => 'AED',
                'is_active' => true,
                'is_source' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SA',
                'name' => 'Saudi Arabia',
                'name_ar' => 'المملكة العربية السعودية',
                'flag' => '🇸🇦',
                'phone_code' => '+966',
                'currency_code' => 'SAR',
                'is_active' => true,
                'is_source' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'CN',
                'name' => 'China (AliExpress)',
                'name_ar' => 'الصين (علي إكسبريس)',
                'flag' => '🇨🇳',
                'phone_code' => '+86',
                'currency_code' => 'USD',
                'is_active' => true,
                'is_source' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'KW',
                'name' => 'Kuwait',
                'name_ar' => 'الكويت',
                'flag' => '🇰🇼',
                'phone_code' => '+965',
                'currency_code' => 'KWD',
                'is_active' => true,
                'is_source' => false,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'BH',
                'name' => 'Bahrain',
                'name_ar' => 'البحرين',
                'flag' => '🇧🇭',
                'phone_code' => '+973',
                'currency_code' => 'BHD',
                'is_active' => true,
                'is_source' => false,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'QA',
                'name' => 'Qatar',
                'name_ar' => 'قطر',
                'flag' => '🇶🇦',
                'phone_code' => '+974',
                'currency_code' => 'QAR',
                'is_active' => true,
                'is_source' => false,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'OM',
                'name' => 'Oman',
                'name_ar' => 'عُمان',
                'flag' => '🇴🇲',
                'phone_code' => '+968',
                'currency_code' => 'OMR',
                'is_active' => true,
                'is_source' => false,
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'EG',
                'name' => 'Egypt',
                'name_ar' => 'مصر',
                'flag' => '🇪🇬',
                'phone_code' => '+20',
                'currency_code' => 'EGP',
                'is_active' => true,
                'is_source' => false,
                'sort_order' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
