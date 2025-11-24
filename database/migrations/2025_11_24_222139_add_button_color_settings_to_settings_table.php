<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Success Button Colors
        Setting::set('btn_success_color', '#28a745', 'color', 'Default color for success buttons');
        Setting::set('btn_success_hover_color', '#218838', 'color', 'Hover color for success buttons');
        Setting::set('btn_success_active_color', '#1e7e34', 'color', 'Active color for success buttons');

        // Warning Button Colors
        Setting::set('btn_warning_color', '#ffc107', 'color', 'Default color for warning buttons');
        Setting::set('btn_warning_hover_color', '#e0a800', 'color', 'Hover color for warning buttons');
        Setting::set('btn_warning_active_color', '#d39e00', 'color', 'Active color for warning buttons');

        // Danger Button Colors
        Setting::set('btn_danger_color', '#dc3545', 'color', 'Default color for danger buttons');
        Setting::set('btn_danger_hover_color', '#c82333', 'color', 'Hover color for danger buttons');
        Setting::set('btn_danger_active_color', '#bd2130', 'color', 'Active color for danger buttons');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove button color settings
        Setting::remove('btn_success_color');
        Setting::remove('btn_success_hover_color');
        Setting::remove('btn_success_active_color');

        Setting::remove('btn_warning_color');
        Setting::remove('btn_warning_hover_color');
        Setting::remove('btn_warning_active_color');

        Setting::remove('btn_danger_color');
        Setting::remove('btn_danger_hover_color');
        Setting::remove('btn_danger_active_color');
    }
};
