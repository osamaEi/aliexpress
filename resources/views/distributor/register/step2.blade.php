<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() == 'ar' ? 'تسجيل متجر جديد - الخطوة 2' : 'Distributor Registration - Step 2' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #561C04;
            --secondary-color: #7a2805;
            --success-color: #10b981;
            --distributor-color: #561C04;
            --distributor-secondary: #7a2805;
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

        .sidebar {
            background: linear-gradient(135deg, var(--distributor-color) 0%, var(--distributor-secondary) 100%);
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
            margin-bottom: 30px;
            position: relative;
        }

        .progress-step::before {
            content: '';
            position: absolute;
            inset-inline-start: 22px;
            top: 50px;
            width: 2px;
            height: calc(100% + 30px);
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
            margin-inline-end: 15px;
            transition: all 0.3s;
        }

        .progress-step.active .step-number {
            background: white;
            color: var(--distributor-color);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }

        .progress-step.completed .step-number {
            background: #10b981;
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
            margin-inline-end: 6px;
            color: var(--distributor-color);
        }

        .form-label .required {
            color: #ef4444;
            margin-inline-start: 4px;
        }

        .form-label .optional {
            color: #10b981;
            font-size: 12px;
            margin-inline-start: 4px;
        }

        .text-danger {
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
        }

        .btn-continue {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--distributor-color) 0%, var(--distributor-secondary) 100%);
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
            background: #f3f4f6;
            color: #333;
            border: none;
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
            background: #e5e7eb;
            color: #333;
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
                min-width: 100px;
            }

            .progress-step::before {
                display: none;
            }

            .step-number {
                margin-left: 0;
                margin-right: 0;
                margin-bottom: 10px;
            }

            .content-area {
                padding: 30px 25px;
            }
        }

        .language-switcher {
            position: absolute;
            top: 20px;
            inset-inline-end: 20px;
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
            color: var(--distributor-color);
        }

        .lang-btn.active {
            background: linear-gradient(135deg, var(--distributor-color) 0%, var(--distributor-secondary) 100%);
            color: white;
            border-color: var(--distributor-color);
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

        .distributor-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 50px;
            margin-top: 15px;
            font-size: 14px;
        }

        /* Upload Styles */
        .upload-wrapper {
            margin-bottom: 25px;
        }

        .upload-area {
            border: 2px dashed #e5e7eb;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: #f9fafb;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
        }

        .upload-area:hover {
            border-color: var(--distributor-color);
            background: #fef3f2;
        }

        .upload-area.dragover {
            border-color: var(--distributor-color);
            background: #fef3f2;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 48px;
            color: var(--distributor-color);
            margin-bottom: 15px;
        }

        .upload-text h4 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .upload-text p {
            font-size: 13px;
            color: #666;
            margin: 0;
        }

        .file-preview {
            display: none;
            margin-top: 20px;
            text-align: center;
        }

        .file-preview.active {
            display: block;
        }

        .file-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 10px;
            background: white;
        }

        .file-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            background: #f3f4f6;
            border-radius: 10px;
            margin-top: 10px;
        }

        .file-info i {
            font-size: 24px;
            color: var(--distributor-color);
        }

        .file-info span {
            font-size: 14px;
            color: #333;
        }

        .remove-file {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 10px;
            padding: 8px 16px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .remove-file:hover {
            background: #dc2626;
        }

        .upload-input {
            display: none;
        }

        .document-section {
            background: #f8fafc;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .document-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }

        .document-section-title i {
            color: var(--distributor-color);
            font-size: 24px;
        }
    </style>
</head>
<body>
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
        <div class="sidebar">
            <div class="logo-wrapper">
                <img src="{{ asset('logo/logo10.png') }}" alt="Logo">
        
                <h3>{{ app()->getLocale() == 'ar' ? 'تسجيل متجر جديد' : 'Distributor Registration' }}</h3>
                <div class="distributor-badge">
                    <i class="ri-truck-line"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'حساب متجر' : 'Distributor Account' }}</span>
                </div>
            </div>

            <div class="progress-sidebar">
                <div class="progress-step completed">
                    <div class="step-number"><i class="ri-check-line"></i></div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'المعلومات الأساسية' : 'Basic Information' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'بيانات المتجر والتواصل' : 'Store and contact details' }}</p>
                    </div>
                </div>

                <div class="progress-step active">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'المستندات' : 'Documents' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'السجل التجاري والشعار' : 'Commercial register & logo' }}</p>
                    </div>
                </div>

                <div class="progress-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'الإعدادات' : 'Settings' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'العملة والتواصل الاجتماعي' : 'Currency & social media' }}</p>
                    </div>
                </div>

                <div class="progress-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'التحقق' : 'Verification' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'تأكيد البريد الإلكتروني' : 'Email confirmation' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-area">
            <div class="content-header">
                <h2>{{ app()->getLocale() == 'ar' ? 'مستندات النشاط التجاري' : 'Business Documents' }}</h2>
                <p>{{ app()->getLocale() == 'ar' ? 'قم برفع المستندات المطلوبة وشعار متجرك' : 'Upload required documents and your store logo' }}</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    <strong>{{ app()->getLocale() == 'ar' ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors:' }}</strong>
                    <ul class="mb-0 mt-2" style="padding-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('distributor.register.step2.process') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Store Logo -->
                <div class="upload-wrapper">
                    <label class="form-label">
                        <i class="ri-image-line"></i>
                        {{ app()->getLocale() == 'ar' ? 'شعار المتجر' : 'Store Logo' }}
                        <span class="required">*</span>
                    </label>
                    <div class="upload-area" id="logoUploadArea">
                        <i class="ri-upload-cloud-2-line upload-icon"></i>
                        <div class="upload-text">
                            <h4>{{ app()->getLocale() == 'ar' ? 'اضغط لرفع الشعار أو اسحبه هنا' : 'Click to upload logo or drag it here' }}</h4>
                            <p>{{ app()->getLocale() == 'ar' ? 'PNG, JPG, JPEG حتى 2MB' : 'PNG, JPG, JPEG up to 2MB' }}</p>
                        </div>
                        <input type="file" id="logo_file" name="logo" accept="image/png,image/jpeg,image/jpg" class="upload-input" required>
                    </div>
                    <div class="file-preview" id="logoPreview">
                        <img id="logoImage" src="" alt="Logo Preview">
                        <br>
                        <button type="button" class="remove-file" id="removeLogo">
                            <i class="ri-delete-bin-line"></i>
                            <span>{{ app()->getLocale() == 'ar' ? 'إزالة الشعار' : 'Remove Logo' }}</span>
                        </button>
                    </div>
                    @error('logo')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Business Documents Section -->
                <div class="document-section">
                    <div class="document-section-title">
                        <i class="ri-file-text-line"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'مستندات النشاط التجاري' : 'Business Activity Documents' }}</span>
                    </div>

                    <!-- Commercial Register -->
                    <div class="upload-wrapper">
                        <label class="form-label">
                            <i class="ri-file-list-3-line"></i>
                            {{ app()->getLocale() == 'ar' ? 'السجل التجاري' : 'Commercial Register' }}
                            <span class="required">*</span>
                        </label>
                        <div class="upload-area" id="commercialUploadArea">
                            <i class="ri-file-upload-line upload-icon"></i>
                            <div class="upload-text">
                                <h4>{{ app()->getLocale() == 'ar' ? 'اضغط لرفع السجل التجاري' : 'Click to upload commercial register' }}</h4>
                                <p>{{ app()->getLocale() == 'ar' ? 'PDF, PNG, JPG حتى 5MB' : 'PDF, PNG, JPG up to 5MB' }}</p>
                            </div>
                            <input type="file" id="commercial_file" name="commercial_register" accept=".pdf,image/png,image/jpeg,image/jpg" class="upload-input" required>
                        </div>
                        <div class="file-preview" id="commercialPreview">
                            <div class="file-info">
                                <i class="ri-file-text-line"></i>
                                <span id="commercialFileName"></span>
                            </div>
                            <button type="button" class="remove-file" id="removeCommercial">
                                <i class="ri-delete-bin-line"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'إزالة الملف' : 'Remove File' }}</span>
                            </button>
                        </div>
                        @error('commercial_register')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Freelance Document -->
                    <div class="upload-wrapper">
                        <label class="form-label">
                            <i class="ri-file-user-line"></i>
                            {{ app()->getLocale() == 'ar' ? 'وثيقة العمل الحر' : 'Freelance Document' }}
                            <span class="optional">({{ app()->getLocale() == 'ar' ? 'اختياري' : 'Optional' }})</span>
                        </label>
                        <div class="upload-area" id="freelanceUploadArea">
                            <i class="ri-file-upload-line upload-icon"></i>
                            <div class="upload-text">
                                <h4>{{ app()->getLocale() == 'ar' ? 'اضغط لرفع وثيقة العمل الحر' : 'Click to upload freelance document' }}</h4>
                                <p>{{ app()->getLocale() == 'ar' ? 'PDF, PNG, JPG حتى 5MB' : 'PDF, PNG, JPG up to 5MB' }}</p>
                            </div>
                            <input type="file" id="freelance_file" name="freelance_document" accept=".pdf,image/png,image/jpeg,image/jpg" class="upload-input">
                        </div>
                        <div class="file-preview" id="freelancePreview">
                            <div class="file-info">
                                <i class="ri-file-text-line"></i>
                                <span id="freelanceFileName"></span>
                            </div>
                            <button type="button" class="remove-file" id="removeFreelance">
                                <i class="ri-delete-bin-line"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'إزالة الملف' : 'Remove File' }}</span>
                            </button>
                        </div>
                        @error('freelance_document')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn-continue">
                    <span>{{ app()->getLocale() == 'ar' ? 'التالي' : 'Next' }}</span>
                    <i class="{{ app()->getLocale() == 'ar' ? 'ri-arrow-left-line' : 'ri-arrow-right-line' }}"></i>
                </button>

                <a href="{{ route('distributor.register.step1') }}" class="btn-back">
                    <i class="{{ app()->getLocale() == 'ar' ? 'ri-arrow-right-line' : 'ri-arrow-left-line' }}"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'رجوع' : 'Back' }}</span>
                </a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Logo Upload
        setupUploadArea('logoUploadArea', 'logo_file', 'logoPreview', 'logoImage', 'removeLogo', true);

        // Commercial Register Upload
        setupUploadArea('commercialUploadArea', 'commercial_file', 'commercialPreview', 'commercialFileName', 'removeCommercial', false);

        // Freelance Document Upload
        setupUploadArea('freelanceUploadArea', 'freelance_file', 'freelancePreview', 'freelanceFileName', 'removeFreelance', false);

        function setupUploadArea(areaId, inputId, previewId, displayId, removeId, isImage) {
            const uploadArea = document.getElementById(areaId);
            const fileInput = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            const display = document.getElementById(displayId);
            const removeBtn = document.getElementById(removeId);

            uploadArea.addEventListener('click', () => fileInput.click());

            fileInput.addEventListener('change', (e) => handleFile(e.target.files[0], uploadArea, preview, display, isImage, fileInput));

            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                const file = e.dataTransfer.files[0];
                if (file) {
                    fileInput.files = e.dataTransfer.files;
                    handleFile(file, uploadArea, preview, display, isImage, fileInput);
                }
            });

            removeBtn.addEventListener('click', () => {
                fileInput.value = '';
                preview.classList.remove('active');
                uploadArea.style.display = 'block';
            });
        }

        function handleFile(file, uploadArea, preview, display, isImage, input) {
            if (!file) return;

            const maxSize = isImage ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('{{ app()->getLocale() == "ar" ? "حجم الملف كبير جداً" : "File size is too large" }}');
                input.value = '';
                return;
            }

            if (isImage) {
                if (!['image/png', 'image/jpeg', 'image/jpg'].includes(file.type)) {
                    alert('{{ app()->getLocale() == "ar" ? "يرجى اختيار صورة صالحة" : "Please select a valid image" }}');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    display.src = e.target.result;
                    preview.classList.add('active');
                    uploadArea.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                display.textContent = file.name;
                preview.classList.add('active');
                uploadArea.style.display = 'none';
            }
        }
    </script>
</body>
</html>
