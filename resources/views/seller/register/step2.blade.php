<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() == 'ar' ? 'تسجيل بائع جديد - الخطوة 2' : 'Seller Registration - Step 2' }}</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('logo/logo.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #561C04;
            --secondary-color: #7a2805;
            --success-color: #10b981;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom right, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .main-container {
            max-width: 1100px;
            width: 100%;
            display: grid;
            grid-template-columns: 400px 1fr;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(86, 28, 4, 0.12);
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Left Sidebar */
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 50px 40px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            top: -80px;
            left: -80px;
        }

        .sidebar::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -60px;
            right: -60px;
        }

        .logo-wrapper {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            z-index: 2;
        }

        .logo-wrapper img {
            max-width: 160px;
            height: auto;
            margin-bottom: 20px;
        }

        .logo-wrapper h3 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .progress-sidebar {
            position: relative;
            z-index: 2;
        }

        .progress-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 35px;
            position: relative;
        }

        .progress-step::before {
            content: '';
            position: absolute;
            left: 22px;
            top: 50px;
            width: 2px;
            height: calc(100% + 35px);
            background: rgba(255, 255, 255, 0.2);
        }

        .progress-step:last-child::before {
            display: none;
        }

        .step-number {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
            margin-left: 15px;
            transition: all 0.3s;
        }

        .progress-step.active .step-number {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }

        .progress-step.completed .step-number {
            background: #db9a0e;
            color: white;
        }

        .step-content h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .step-content p {
            font-size: 13px;
            opacity: 0.8;
            margin: 0;
        }

        .progress-step.active .step-content {
            opacity: 1;
        }

        .progress-step:not(.active) .step-content {
            opacity: 0.6;
        }

        /* Right Content */
        .content-area {
            padding: 50px 60px;
        }

        .content-header {
            margin-bottom: 40px;
        }

        .content-header h2 {
            font-size: 30px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .content-header p {
            color: #666;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .form-label i {
            margin-left: 6px;
            color: var(--primary-color);
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 13px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background: white;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(86, 28, 4, 0.08);
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #ef4444;
        }

        .text-danger {
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
        }

        .btn-continue {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(86, 28, 4, 0.25);
        }

        .btn-back {
            width: 100%;
            padding: 15px;
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-back:hover {
            background: var(--primary-color);
            color: white;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: none;
        }

        .alert-danger {
            background: #fee;
            color: #c00;
        }

        @media (max-width: 992px) {
            .main-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                padding: 30px 25px;
            }

            .progress-sidebar {
                display: flex;
                gap: 20px;
                overflow-x: auto;
            }

            .progress-step {
                flex-direction: column;
                align-items: center;
                text-align: center;
                margin-bottom: 0;
                min-width: 120px;
            }

            .progress-step::before {
                display: none;
            }

            .step-number {
                margin-left: 0;
                margin-bottom: 10px;
            }

            .content-area {
                padding: 30px 25px;
            }
        }

        /* Language Switcher */
        .language-switcher {
            position: absolute;
            top: 20px;
            {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
            background: white;
            padding: 8px 12px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .lang-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: 2px solid transparent;
            border-radius: 50px;
            background: transparent;
            color: #666;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .lang-btn:hover {
            background: #f8f9fa;
            color: var(--primary-color);
        }

        .lang-btn.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-color: var(--primary-color);
        }

        .lang-btn img {
            width: 20px;
            height: 20px;
        }

        .sidebar-footer {
            margin-top: 30px;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .sidebar-footer img {
            max-width: 200px;
            height: auto;
            opacity: 0.9;
        }

        /* Category Cards */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .category-card {
            position: relative;
            cursor: pointer;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            background: white;
        }

        .category-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(86, 28, 4, 0.12);
        }

        .category-card.selected {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(86, 28, 4, 0.2);
        }

        .category-card.selected::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(86, 28, 4, 0.08);
            pointer-events: none;
        }

        .category-card.selected .category-card-check {
            display: flex;
        }

        .category-card-check {
            display: none;
            position: absolute;
            top: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
            background: var(--primary-color);
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            z-index: 2;
        }

        .category-card input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .category-card-img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            background: #f3f4f6;
        }

        .category-card-name {
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .category-card.selected .category-card-name {
            color: var(--primary-color);
        }

        /* Subcategories */
        .sub-categories-wrapper {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sub-categories-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 12px;
        }

        .sub-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
        }

        .sub-category-item {
            position: relative;
            cursor: pointer;
        }

        .sub-category-item input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .sub-category-label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            transition: all 0.3s;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
        }

        .sub-category-label i {
            font-size: 16px;
            color: white;
            opacity: 0;
            transition: all 0.3s;
        }

        .sub-category-item:hover .sub-category-label {
            border-color: var(--primary-color);
        }

        .sub-category-item input[type="checkbox"]:checked + .sub-category-label {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }

        .sub-category-item input[type="checkbox"]:checked + .sub-category-label i {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            }
            .sub-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Language Switcher -->
    <div class="language-switcher">
        <a href="{{ route('lang.switch', 'en') }}" class="lang-btn {{ app()->getLocale() == 'en' ? 'active' : '' }}">
            <img src="https://flagcdn.com/w20/gb.png" alt="English" style="width: 20px; height: 15px;">
            <span>English</span>
        </a>
        <a href="{{ route('lang.switch', 'ar') }}" class="lang-btn {{ app()->getLocale() == 'ar' ? 'active' : '' }}">
            <img src="https://flagcdn.com/w20/ae.png" alt="العربية" style="width: 20px; height: 15px;">
            <span>العربية</span>
        </a>
    </div>
    <div class="main-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-wrapper">
                <img src="{{ asset('logo/logo10.png') }}" alt="Logo">

                <!-- Footer in Sidebar -->
          

                <h3>{{ app()->getLocale() == 'ar' ? 'تسجيل البائع' : 'Seller Registration' }}</h3>
            </div>

            <div class="progress-sidebar">
                <div class="progress-step completed">
                    <div class="step-number"><i class="ri-check-line"></i></div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'المعلومات الأساسية' : 'Basic Information' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'البيانات الشخصية والتواصل' : 'Personal and contact details' }}</p>
                    </div>
                </div>

                <div class="progress-step active">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'النشاط التجاري' : 'Business Activity' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'حدد مجال عملك' : 'Select your business field' }}</p>
                    </div>
                </div>

                <div class="progress-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'تحقق البريد' : 'Email Verification' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'تأكيد البريد الإلكتروني' : 'Email confirmation' }}</p>
                    </div>
                </div>

                <div class="progress-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'تحقق واتساب' : 'WhatsApp Verification' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'تأكيد رقم الهاتف' : 'Phone confirmation' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="content-header">
                <h2>{{ app()->getLocale() == 'ar' ? 'حدد نشاطك التجاري' : 'Select Your Business Activity' }}</h2>
                <p>{{ app()->getLocale() == 'ar' ? 'اختر المجال الذي تعمل به لتحسين تجربتك' : 'Choose your field of work to improve your experience' }}</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>{{ app()->getLocale() == 'ar' ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors:' }}</strong>
                    <ul style="margin: 10px 0 0 0; padding-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('seller.register.step2.process') }}" method="POST">
                @csrf

                <!-- Categories Selection -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="ri-briefcase-line"></i>
                        {{ app()->getLocale() == 'ar' ? 'اختر الفئات' : 'Select Categories' }}
                    </label>
                    <p style="font-size: 13px; color: #666; margin-bottom: 15px;">
                        {{ app()->getLocale() == 'ar' ? 'اختر الفئات الرئيسية وسيتم إظهار الفئات الفرعية أسفلها' : 'Select main categories and subcategories will appear below them' }}
                    </p>

                    <!-- Main Categories Grid with Photos -->
                    <div class="categories-grid">
                        @foreach($mainCategories as $category)
                            <div class="category-card {{ in_array($category->id, old('main_categories', [])) ? 'selected' : '' }}"
                                 data-category-id="{{ $category->id }}"
                                 onclick="toggleMainCategory(this, {{ $category->id }})">
                                <div class="category-card-check">
                                    <i class="ri-check-line"></i>
                                </div>
                                <input type="checkbox"
                                       name="main_categories[]"
                                       value="{{ $category->id }}"
                                       class="main-category-checkbox"
                                       data-category-id="{{ $category->id }}"
                                       {{ in_array($category->id, old('main_categories', [])) ? 'checked' : '' }}>
                                @if($category->photo || $category->image)
                                    <img src="{{ asset('storage/' . ($category->photo ?? $category->image)) }}" alt="{{ $category->name }}" class="category-card-img" style="object-fit: contain;">
                                @else
                                    <div class="category-card-img" style="display: flex; align-items: center; justify-content: center; background: #f3f4f6;">
                                        <i class="ri-folder-line" style="font-size: 32px; color: #ccc;"></i>
                                    </div>
                                @endif
                                <div class="category-card-name">
                                    {{ app()->getLocale() == 'ar' ? ($category->name_ar ?: $category->name) : $category->name }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Subcategories Section (shown when main category selected) -->
                    <!-- @foreach($mainCategories as $category)
                        @if(isset($subCategories[$category->id]) && count($subCategories[$category->id]) > 0)
                            <div class="sub-categories-wrapper" id="sub-cat-{{ $category->id }}" style="display: none;">
                                <div class="sub-categories-label">
                                    {{ app()->getLocale() == 'ar' ? 'الفئات الفرعية لـ' : 'Subcategories of' }}
                                    "{{ app()->getLocale() == 'ar' ? ($category->name_ar ?: $category->name) : $category->name }}":
                                </div>
                                <div class="sub-grid">
                                    @foreach($subCategories[$category->id] as $subCat)
                                        <label class="sub-category-item">
                                            <input type="checkbox"
                                                   name="sub_categories[]"
                                                   value="{{ $subCat->id }}"
                                                   class="sub-category-checkbox"
                                                   data-parent-id="{{ $category->id }}"
                                                   {{ in_array($subCat->id, old('sub_categories', [])) ? 'checked' : '' }}>
                                            <span class="sub-category-label">
                                                <i class="ri-check-line"></i>
                                                {{ app()->getLocale() == 'ar' ? ($subCat->name_ar ?: $subCat->name) : $subCat->name }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach -->

                    @error('main_categories')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                    @error('sub_categories')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-continue">
                    {{ app()->getLocale() == 'ar' ? 'المتابعة' : 'Continue' }}
                    <i class="{{ app()->getLocale() == 'ar' ? 'ri-arrow-left-line' : 'ri-arrow-right-line' }}"></i>
                </button>

                <a href="{{ route('seller.register.step1') }}" class="btn-back">
                    <i class="{{ app()->getLocale() == 'ar' ? 'ri-arrow-right-line' : 'ri-arrow-left-line' }}"></i>
                    {{ app()->getLocale() == 'ar' ? 'الرجوع' : 'Back' }}
                </a>
            </form>
        </div>
    </div>

      <div style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 100; display: flex; align-items: center; gap: 8px;">
        <span style="color: #808080; font-size: 14px; font-weight: 400;">{{ app()->getLocale() == 'ar' ? 'بواسطة' : 'BY' }}</span>
        <img src="{{ asset('footer.png') }}"
             alt="EVORQ Logo"
             style="height: 45px;"
           >
        <span style="color: #808080; font-size: 14px; font-weight: 400; letter-spacing: 2px;">{{ app()->getLocale() == 'ar' ? 'إيفورك للتقنية' : 'EVORQ TECHNOLOGIES' }}</span>
    </div>

    <script>
        function toggleMainCategory(card, categoryId) {
            const checkbox = card.querySelector('.main-category-checkbox');
            const isChecked = !checkbox.checked;
            checkbox.checked = isChecked;

            const subCategoryGroup = document.getElementById('sub-cat-' + categoryId);

            if (isChecked) {
                card.classList.add('selected');
                if (subCategoryGroup) {
                    subCategoryGroup.style.display = 'block';
                    // Auto-select all subcategories
                    const subCheckboxes = subCategoryGroup.querySelectorAll('.sub-category-checkbox');
                    subCheckboxes.forEach(sub => { sub.checked = true; });
                }
            } else {
                card.classList.remove('selected');
                if (subCategoryGroup) {
                    subCategoryGroup.style.display = 'none';
                    const subCheckboxes = subCategoryGroup.querySelectorAll('.sub-category-checkbox');
                    subCheckboxes.forEach(sub => { sub.checked = false; });
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const checkedCards = document.querySelectorAll('.category-card.selected');
            checkedCards.forEach(card => {
                const categoryId = card.dataset.categoryId;
                const subCategoryGroup = document.getElementById('sub-cat-' + categoryId);
                if (subCategoryGroup) {
                    subCategoryGroup.style.display = 'block';
                    const subCheckboxes = subCategoryGroup.querySelectorAll('.sub-category-checkbox');
                    subCheckboxes.forEach(sub => { sub.checked = true; });
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
