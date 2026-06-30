<?php

use App\Models\City;
use App\Models\District;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Backfill Arabic names for the seeded cities and districts so they display
     * in Arabic on the order page and in the store/distributor screens.
     */
    public function up(): void
    {
        $map = $this->translations();

        foreach (City::whereNull('name_ar')->orWhere('name_ar', '')->get() as $city) {
            if (isset($map[$city->name])) {
                $city->update(['name_ar' => $map[$city->name]]);
            }
        }

        foreach (District::whereNull('name_ar')->orWhere('name_ar', '')->get() as $district) {
            if (isset($map[$district->name])) {
                $district->update(['name_ar' => $map[$district->name]]);
            }
        }
    }

    public function down(): void
    {
        // No-op: leave Arabic names in place on rollback.
    }

    /**
     * English -> Arabic for every seeded city and district.
     */
    private function translations(): array
    {
        return [
            // ── Cities ──
            'Dubai' => 'دبي', 'Abu Dhabi' => 'أبوظبي', 'Sharjah' => 'الشارقة', 'Ajman' => 'عجمان',
            'Ras Al Khaimah' => 'رأس الخيمة', 'Fujairah' => 'الفجيرة', 'Umm Al Quwain' => 'أم القيوين',
            'Riyadh' => 'الرياض', 'Jeddah' => 'جدة', 'Mecca' => 'مكة المكرمة', 'Medina' => 'المدينة المنورة',
            'Dammam' => 'الدمام', 'Khobar' => 'الخبر', 'Tabuk' => 'تبوك',
            'Doha' => 'الدوحة', 'Al Rayyan' => 'الريان', 'Al Wakrah' => 'الوكرة', 'Al Khor' => 'الخور', 'Lusail' => 'لوسيل',
            'Manama' => 'المنامة', 'Muharraq' => 'المحرق', 'Riffa' => 'الرفاع', 'Isa Town' => 'مدينة عيسى',
            'Hamad Town' => 'مدينة حمد', 'Sitra' => 'سترة',

            // ── Districts — UAE ──
            'Deira' => 'ديرة', 'Bur Dubai' => 'بر دبي', 'Jumeirah' => 'جميرا', 'Al Barsha' => 'البرشاء',
            'Business Bay' => 'الخليج التجاري', 'Dubai Marina' => 'دبي مارينا', 'Al Qusais' => 'القصيص',
            'International City' => 'المدينة العالمية', 'Downtown Dubai' => 'وسط مدينة دبي', 'Mirdif' => 'مردف',
            'Al Khalidiyah' => 'الخالدية', 'Al Mushrif' => 'المشرف', 'Khalifa City' => 'مدينة خليفة',
            'Al Reem Island' => 'جزيرة الريم', 'Mussafah' => 'مصفح', 'Al Bateen' => 'البطين', 'Al Maryah Island' => 'جزيرة المارية',
            'Al Majaz' => 'المجاز', 'Al Nahda' => 'النهدة', 'Al Qasimia' => 'القاسمية', 'Muweilah' => 'مويلح',
            'Al Taawun' => 'التعاون', 'Al Khan' => 'الخان',
            'Al Nuaimiya' => 'النعيمية', 'Al Rashidiya' => 'الراشدية', 'Al Jurf' => 'الجرف', 'Al Mowaihat' => 'المويهات',
            'Al Nakheel' => 'النخيل', 'Al Hamra' => 'الحمراء', 'Khuzam' => 'خزام', 'Al Dhait' => 'الظيت',
            'Al Faseel' => 'الفصيل', 'Sakamkam' => 'سكمكم', 'Merashid' => 'مراشد',
            'Al Salama' => 'السلامة', 'Al Raas' => 'الراس', 'Al Maidan' => 'الميدان',

            // ── Districts — KSA ──
            'Al Olaya' => 'العليا', 'Al Malaz' => 'الملز', 'Al Naseem' => 'النسيم', 'Al Murabba' => 'المربع',
            'King Fahd District' => 'حي الملك فهد', 'Al Wurud' => 'الورود', 'Al Sahafa' => 'الصحافة',
            'Al Yasmin' => 'الياسمين', 'Al Narjis' => 'النرجس', 'Al Aqiq' => 'العقيق',
            'Al Rawdah' => 'الروضة', 'Al Salamah' => 'السلامة', 'Al Andalus' => 'الأندلس', 'Al Faisaliyah' => 'الفيصلية',
            'Al Naeem' => 'النعيم', 'Al Shati' => 'الشاطئ', 'Al Bawadi' => 'البوادي',
            'Al Aziziyah' => 'العزيزية', 'Al Shawqiyah' => 'الشوقية', 'Al Hindawiyah' => 'الهنداوية', 'Al Awali' => 'العوالي',
            'Quba' => 'قباء', 'Al Haram' => 'الحرم', 'Al Khalidiyah' => 'الخالدية',
            'Al Mazruiyah' => 'المزروعية', 'Al Adamah' => 'الأدامة',
            'Al Aqrabiyah' => 'العقربية', 'Al Rakah' => 'الراكة', 'Al Thuqbah' => 'الثقبة', 'Al Hizam' => 'الحزام',
            'Al Wadi' => 'الوادي',

            // ── Districts — Qatar ──
            'West Bay' => 'الخليج الغربي', 'Al Sadd' => 'السد', 'Al Dafna' => 'الدفنة', 'Msheireb' => 'مشيرب',
            'Najma' => 'نجمة', 'Al Mansoura' => 'المنصورة', 'Old Airport' => 'المطار القديم', 'Bin Mahmoud' => 'بن محمود',
            'Al Gharrafa' => 'الغرافة', 'Muaither' => 'معيذر', 'Al Wajba' => 'الوجبة', 'New Al Rayyan' => 'الريان الجديد',
            'Al Wukair' => 'الوكير', 'Mesaieed' => 'مسيعيد', 'Barwa City' => 'مدينة بروة',
            'Al Khor City' => 'مدينة الخور', 'Al Thakhira' => 'الذخيرة',
            'Marina District' => 'حي المارينا', 'Fox Hills' => 'فوكس هيلز', 'Energy City' => 'مدينة الطاقة',

            // ── Districts — Bahrain ──
            'Juffair' => 'الجفير', 'Adliya' => 'العدلية', 'Seef' => 'السيف', 'Gudaibiya' => 'القضيبية',
            'Hoora' => 'الحورة', 'Diplomatic Area' => 'المنطقة الدبلوماسية', 'Salmaniya' => 'السلمانية',
            'Arad' => 'عراد', 'Hidd' => 'الحد', 'Busaiteen' => 'البسيتين', 'Galali' => 'قلالي',
            'East Riffa' => 'الرفاع الشرقي', 'West Riffa' => 'الرفاع الغربي', 'Riffa Views' => 'رفاع فيوز', 'Hajiyat' => 'الهجيات',
            'Block 801' => 'مجمع 801', 'Block 802' => 'مجمع 802', 'Block 803' => 'مجمع 803',
            'Roundabout 1' => 'دوار 1', 'Roundabout 9' => 'دوار 9', 'Roundabout 17' => 'دوار 17',
            'Wadyan' => 'وادان', 'Mahazza' => 'المحزة', 'Sufala' => 'سفالة',
        ];
    }
};
