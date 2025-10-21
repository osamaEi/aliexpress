<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دليل الربط مع AliExpress للدروبشيبينغ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
        }

        .header-section h1 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 2.5rem;
        }

        .header-section p {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .step-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            margin-left: 20px;
            float: right;
        }

        .step-content {
            overflow: hidden;
        }

        .step-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
        }

        .step-description {
            color: #4a5568;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .code-block {
            background: #1a202c;
            color: #68d391;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
            direction: ltr;
            text-align: left;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
        }

        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .warning-box {
            background: #fff3cd;
            border-right: 4px solid #ffc107;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
        }

        .success-box {
            background: #d4edda;
            border-right: 4px solid #28a745;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
        }

        .info-box {
            background: #d1ecf1;
            border-right: 4px solid #17a2b8;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            padding: 12px 0;
            padding-right: 30px;
            position: relative;
        }

        .feature-list li:before {
            content: "\ea52";
            font-family: 'remixicon';
            position: absolute;
            right: 0;
            color: #28a745;
            font-weight: bold;
        }

        .table-custom {
            margin: 20px 0;
        }

        .table-custom th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
        }

        .screenshot-placeholder {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
            color: #6c757d;
        }

        .footer-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
        }

        .quick-links {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }

        @media print {
            body {
                background: white;
            }
            .step-card {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1>
                <i class="ri-shopping-bag-3-line"></i>
                دليل الربط مع AliExpress للدروبشيبينغ
            </h1>
            <p>دليل شامل خطوة بخطوة لإعداد وربط متجرك الإلكتروني مع منصة AliExpress</p>
            <div class="mt-3">
                <span class="badge bg-primary fs-6">تاريخ التحديث: {{ now()->format('Y-m-d') }}</span>
            </div>
        </div>

        <!-- Introduction -->
        <div class="step-card">
            <h3 class="text-center mb-4">
                <i class="ri-information-line"></i>
                مقدمة
            </h3>
            <p class="step-description">
                يتيح لك هذا الدليل ربط متجرك الإلكتروني مع منصة AliExpress للاستفادة من نموذج الدروبشيبينغ (Dropshipping)،
                حيث يمكنك بيع المنتجات في متجرك دون الحاجة لتخزينها، وعند استلام طلب من عميل، يتم شحن المنتج مباشرة من
                المورد في AliExpress إلى العميل.
            </p>

            <h5 class="mt-4 mb-3">ما ستتعلمه في هذا الدليل:</h5>
            <ul class="feature-list">
                <li>التسجيل في برنامج AliExpress للمطورين</li>
                <li>إنشاء تطبيق والحصول على مفاتيح API</li>
                <li>ضبط إعدادات التطبيق في متجرك</li>
                <li>الحصول على Access Token من خلال OAuth</li>
                <li>اختبار الاتصال مع API</li>
                <li>استيراد المنتجات وإدارتها</li>
            </ul>

            <div class="success-box mt-4">
                <h6><i class="ri-lightbulb-line"></i> فوائد الربط مع AliExpress:</h6>
                <ul class="mb-0">
                    <li>الوصول إلى ملايين المنتجات</li>
                    <li>عدم الحاجة لتخزين البضائع</li>
                    <li>تحديث الأسعار والمخزون تلقائياً</li>
                    <li>شحن مباشر من المورد للعميل</li>
                    <li>هامش ربح مرن</li>
                </ul>
            </div>
        </div>

        <!-- Step 1: Register -->
        <div class="step-card">
            <div class="step-number">1</div>
            <div class="step-content">
                <h3 class="step-title">التسجيل في منصة AliExpress للمطورين</h3>
                <p class="step-description">
                    الخطوة الأولى هي التسجيل في منصة AliExpress Open Platform للحصول على صلاحيات الوصول إلى API.
                </p>

                <h5 class="mt-4">الخطوات التفصيلية:</h5>
                <ol class="pe-4">
                    <li class="mb-3">
                        <strong>زيارة منصة المطورين:</strong>
                        <div class="code-block">
https://openservice.aliexpress.com/
                        </div>
                    </li>
                    <li class="mb-3">
                        <strong>إنشاء حساب:</strong>
                        <p>قم بالتسجيل باستخدام بريدك الإلكتروني أو حساب AliExpress الخاص بك</p>
                    </li>
                    <li class="mb-3">
                        <strong>تفعيل الحساب:</strong>
                        <p>تحقق من بريدك الإلكتروني وقم بتفعيل الحساب من خلال الرابط المرسل</p>
                    </li>
                </ol>

                <div class="info-box">
                    <i class="ri-information-line"></i>
                    <strong>ملاحظة مهمة:</strong> قد تحتاج إلى تقديم معلومات عن نشاطك التجاري أو موقعك الإلكتروني.
                </div>

                <a href="https://openservice.aliexpress.com/" target="_blank" class="action-btn mt-3">
                    <i class="ri-external-link-line"></i>
                    انتقل إلى منصة المطورين
                </a>
            </div>
        </div>

        <!-- Step 2: Create App -->
        <div class="step-card">
            <div class="step-number">2</div>
            <div class="step-content">
                <h3 class="step-title">إنشاء تطبيق والحصول على API Keys</h3>
                <p class="step-description">
                    بعد التسجيل، تحتاج إلى إنشاء تطبيق جديد للحصول على مفاتيح API (App Key & App Secret).
                </p>

                <h5 class="mt-4">الخطوات:</h5>
                <ol class="pe-4">
                    <li class="mb-3">
                        <strong>الدخول إلى لوحة التحكم:</strong>
                        <p>بعد تسجيل الدخول، انتقل إلى "My Apps" أو "تطبيقاتي"</p>
                    </li>
                    <li class="mb-3">
                        <strong>إنشاء تطبيق جديد:</strong>
                        <p>اضغط على "Create App" وقم بملء البيانات المطلوبة:</p>
                        <ul>
                            <li>اسم التطبيق (مثلاً: My Dropshipping Store)</li>
                            <li>نوع التطبيق: Web Application</li>
                            <li>وصف التطبيق</li>
                            <li>رابط الموقع الإلكتروني</li>
                        </ul>
                    </li>
                    <li class="mb-3">
                        <strong>الحصول على المفاتيح:</strong>
                        <p>بعد إنشاء التطبيق، ستحصل على:</p>
                        <ul>
                            <li><code>App Key</code> (مفتاح التطبيق)</li>
                            <li><code>App Secret</code> (الرمز السري)</li>
                        </ul>
                    </li>
                </ol>

                <div class="warning-box">
                    <i class="ri-alert-line"></i>
                    <strong>تحذير هام:</strong> احتفظ بالمفاتيح في مكان آمن ولا تشاركها مع أحد. هذه المفاتيح تشبه كلمة المرور!
                </div>

                <div class="screenshot-placeholder">
                    <i class="ri-image-line" style="font-size: 48px;"></i>
                    <p class="mt-3 mb-0">سيظهر هنا App Key و App Secret بعد إنشاء التطبيق</p>
                </div>
            </div>
        </div>

        <!-- Step 3: Join Dropshipping Program -->
        <div class="step-card">
            <div class="step-number">3</div>
            <div class="step-content">
                <h3 class="step-title">الانضمام إلى برنامج الدروبشيبينغ</h3>
                <p class="step-description">
                    للوصول إلى API الخاص بالدروبشيبينغ، يجب الانضمام إلى برنامج AliExpress Dropshipping.
                </p>

                <h5 class="mt-4">الخطوات:</h5>
                <ol class="pe-4">
                    <li class="mb-3">
                        <strong>زيارة صفحة البرنامج:</strong>
                        <div class="code-block">
https://ds.aliexpress.com/
                        </div>
                    </li>
                    <li class="mb-3">
                        <strong>التقديم للبرنامج:</strong>
                        <p>املأ نموذج التقديم وقدم معلومات عن متجرك</p>
                    </li>
                    <li class="mb-3">
                        <strong>انتظار الموافقة:</strong>
                        <p>قد يستغرق الأمر من 1-3 أيام عمل للحصول على الموافقة</p>
                    </li>
                </ol>

                <a href="https://ds.aliexpress.com/" target="_blank" class="action-btn mt-3">
                    <i class="ri-external-link-line"></i>
                    الانضمام لبرنامج الدروبشيبينغ
                </a>
            </div>
        </div>

        <!-- Step 4: Configure .env -->
        <div class="step-card">
            <div class="step-number">4</div>
            <div class="step-content">
                <h3 class="step-title">ضبط إعدادات التطبيق في متجرك</h3>
                <p class="step-description">
                    الآن حان الوقت لإضافة مفاتيح API إلى ملف الإعدادات في متجرك الإلكتروني.
                </p>

                <h5 class="mt-4">الخطوات:</h5>
                <ol class="pe-4">
                    <li class="mb-3">
                        <strong>فتح ملف .env:</strong>
                        <p>افتح ملف <code>.env</code> الموجود في المجلد الرئيسي لمتجرك</p>
                    </li>
                    <li class="mb-3">
                        <strong>إضافة المفاتيح:</strong>
                        <p>أضف الأسطر التالية وقم بتعديلها بمفاتيحك الفعلية:</p>
                        <div class="code-block">
# AliExpress API Configuration
ALIEXPRESS_API_KEY=your_app_key_here
ALIEXPRESS_API_SECRET=your_app_secret_here
ALIEXPRESS_TRACKING_ID=
ALIEXPRESS_ACCESS_TOKEN=
ALIEXPRESS_API_URL=https://api-sg.aliexpress.com/sync
                        </div>
                    </li>
                    <li class="mb-3">
                        <strong>حفظ الملف:</strong>
                        <p>احفظ التغييرات في ملف .env</p>
                    </li>
                    <li class="mb-3">
                        <strong>مسح الذاكرة المؤقتة:</strong>
                        <p>قم بتشغيل الأمر التالي في Terminal:</p>
                        <div class="code-block">
php artisan config:clear
                        </div>
                    </li>
                </ol>

                <div class="info-box">
                    <i class="ri-information-line"></i>
                    <strong>ملاحظة:</strong> لا تقلق بشأن <code>ACCESS_TOKEN</code> الآن، سنحصل عليه في الخطوة التالية.
                </div>
            </div>
        </div>

        <!-- Step 5: OAuth Flow -->
        <div class="step-card">
            <div class="step-number">5</div>
            <div class="step-content">
                <h3 class="step-title">الحصول على Access Token عبر OAuth</h3>
                <p class="step-description">
                    Access Token هو المفتاح الذي يسمح لمتجرك بالتواصل مع AliExpress API. سنحصل عليه عبر عملية OAuth الآمنة.
                </p>

                <h5 class="mt-4">الطريقة الأولى - الأسهل (مُوصى بها):</h5>
                <ol class="pe-4">
                    <li class="mb-3">
                        <strong>افتح صفحة المصادقة:</strong>
                        <div class="code-block">
{{ url('/aliexpress-oauth-start') }}
                        </div>
                        <a href="{{ url('/aliexpress-oauth-start') }}" class="action-btn mt-2">
                            <i class="ri-key-2-line"></i>
                            ابدأ عملية المصادقة الآن
                        </a>
                    </li>
                    <li class="mb-3">
                        <strong>اضغط على "Authorize with AliExpress":</strong>
                        <p>ستظهر لك صفحة بزر كبير، اضغط عليه</p>
                    </li>
                    <li class="mb-3">
                        <strong>سجل دخول إلى AliExpress:</strong>
                        <p>ستنتقل إلى صفحة AliExpress للتسجيل</p>
                    </li>
                    <li class="mb-3">
                        <strong>وافق على الصلاحيات:</strong>
                        <p>اقرأ الصلاحيات المطلوبة ثم اضغط "Authorize"</p>
                    </li>
                    <li class="mb-3">
                        <strong>احصل على الـ Token تلقائياً:</strong>
                        <p>سيتم إعادة توجيهك تلقائياً وستظهر لك صفحة تحتوي على <code>Access Token</code></p>
                    </li>
                    <li class="mb-3">
                        <strong>انسخ الـ Token:</strong>
                        <p>استخدم زر "Copy" لنسخ الـ Access Token</p>
                    </li>
                    <li class="mb-3">
                        <strong>أضف الـ Token إلى .env:</strong>
                        <div class="code-block">
ALIEXPRESS_ACCESS_TOKEN=your_token_here
                        </div>
                    </li>
                    <li class="mb-3">
                        <strong>امسح الذاكرة المؤقتة:</strong>
                        <div class="code-block">
php artisan config:clear
                        </div>
                    </li>
                </ol>

                <div class="success-box mt-4">
                    <i class="ri-checkbox-circle-line"></i>
                    <strong>مبروك!</strong> لقد أتممت عملية الربط بنجاح. الآن يمكنك البدء في استخدام AliExpress API.
                </div>

                <h5 class="mt-5">الطريقة الثانية - يدوياً (للمتقدمين):</h5>
                <div class="warning-box">
                    <i class="ri-tools-line"></i>
                    <strong>للمحترفين فقط:</strong> استخدم هذه الطريقة إذا كنت ترغب في فهم كيفية عمل OAuth يدوياً.
                </div>
                <ol class="pe-4">
                    <li class="mb-2">قم بإعداد Redirect URI في لوحة تحكم AliExpress</li>
                    <li class="mb-2">قم ببناء رابط OAuth وزيارته في المتصفح</li>
                    <li class="mb-2">احصل على Authorization Code من الـ URL</li>
                    <li class="mb-2">استخدم صفحة اختبار Token لتبديل الـ Code بـ Access Token</li>
                </ol>
            </div>
        </div>

        <!-- Step 6: Test Connection -->
        <div class="step-card">
            <div class="step-number">6</div>
            <div class="step-content">
                <h3 class="step-title">اختبار الاتصال مع API</h3>
                <p class="step-description">
                    الآن دعنا نتأكد من أن كل شيء يعمل بشكل صحيح عن طريق اختبار الاتصال مع AliExpress API.
                </p>

                <h5 class="mt-4">الخطوات:</h5>
                <ol class="pe-4">
                    <li class="mb-3">
                        <strong>افتح صفحة الاختبار:</strong>
                        <div class="code-block">
{{ url('/test-aliexpress') }}
                        </div>
                        <a href="{{ url('/test-aliexpress') }}" class="action-btn mt-2">
                            <i class="ri-test-tube-line"></i>
                            اختبر الاتصال الآن
                        </a>
                    </li>
                    <li class="mb-3">
                        <strong>تحقق من النتيجة:</strong>
                        <p>إذا ظهرت رسالة "Successfully connected to AliExpress API" باللون الأخضر، فالاتصال ناجح!</p>
                    </li>
                    <li class="mb-3">
                        <strong>في حالة وجود خطأ:</strong>
                        <ul>
                            <li>تأكد من صحة API Key و Secret</li>
                            <li>تأكد من وجود Access Token</li>
                            <li>تأكد من تشغيل <code>php artisan config:clear</code></li>
                            <li>تحقق من اتصال الإنترنت</li>
                        </ul>
                    </li>
                </ol>

                <div class="info-box">
                    <i class="ri-lightbulb-line"></i>
                    <strong>نصيحة:</strong> يمكنك أيضاً اختبار جميع الوظائف عبر زيارة:
                    <a href="{{ url('/test-aliexpress-all') }}">{{ url('/test-aliexpress-all') }}</a>
                </div>
            </div>
        </div>

        <!-- Step 7: Import Products -->
        <div class="step-card">
            <div class="step-number">7</div>
            <div class="step-content">
                <h3 class="step-title">استيراد وإدارة المنتجات</h3>
                <p class="step-description">
                    الآن يمكنك البدء في استيراد المنتجات من AliExpress إلى متجرك!
                </p>

                <h5 class="mt-4">وظائف متاحة:</h5>
                <div class="table-responsive table-custom">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>الوظيفة</th>
                                <th>الوصف</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>البحث عن المنتجات</strong></td>
                                <td>ابحث عن منتجات في AliExpress باستخدام كلمات مفتاحية</td>
                            </tr>
                            <tr>
                                <td><strong>عرض تفاصيل المنتج</strong></td>
                                <td>احصل على معلومات كاملة عن أي منتج (السعر، الصور، الوصف، إلخ)</td>
                            </tr>
                            <tr>
                                <td><strong>استيراد المنتجات</strong></td>
                                <td>أضف المنتجات إلى متجرك بنقرة واحدة</td>
                            </tr>
                            <tr>
                                <td><strong>تحديث الأسعار</strong></td>
                                <td>قم بمزامنة أسعار المنتجات تلقائياً</td>
                            </tr>
                            <tr>
                                <td><strong>إدارة المخزون</strong></td>
                                <td>تحديث حالة المخزون (متوفر/غير متوفر)</td>
                            </tr>
                            <tr>
                                <td><strong>حساب تكلفة الشحن</strong></td>
                                <td>احسب تكلفة الشحن لأي دولة</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h5 class="mt-4">خطوات استيراد منتج:</h5>
                <ol class="pe-4">
                    <li class="mb-2">ابحث عن المنتج المطلوب في AliExpress</li>
                    <li class="mb-2">انسخ Product ID من رابط المنتج</li>
                    <li class="mb-2">استخدم وظيفة الاستيراد في لوحة التحكم</li>
                    <li class="mb-2">عدّل السعر وأضف هامش الربح الخاص بك</li>
                    <li class="mb-2">انشر المنتج في متجرك</li>
                </ol>

                <div class="success-box">
                    <i class="ri-money-dollar-circle-line"></i>
                    <strong>نصيحة للربح:</strong> أضف هامش ربح بنسبة 20-50% على سعر المنتج الأصلي حسب المنافسة في السوق.
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="step-card">
            <h3 class="text-center mb-4">
                <i class="ri-tools-line"></i>
                حل المشاكل الشائعة
            </h3>

            <div class="accordion" id="troubleshootingAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#problem1">
                            <i class="ri-error-warning-line me-2"></i>
                            خطأ: "AliExpress API credentials not configured"
                        </button>
                    </h2>
                    <div id="problem1" class="accordion-collapse collapse show" data-bs-parent="#troubleshootingAccordion">
                        <div class="accordion-body">
                            <strong>الحل:</strong>
                            <ul>
                                <li>تأكد من إضافة <code>ALIEXPRESS_API_KEY</code> و <code>ALIEXPRESS_API_SECRET</code> في ملف .env</li>
                                <li>قم بتشغيل <code>php artisan config:clear</code></li>
                                <li>أعد تشغيل السيرفر</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem2">
                            <i class="ri-error-warning-line me-2"></i>
                            خطأ: "Invalid signature"
                        </button>
                    </h2>
                    <div id="problem2" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                        <div class="accordion-body">
                            <strong>الحل:</strong>
                            <ul>
                                <li>تحقق من صحة <code>APP_SECRET</code></li>
                                <li>تأكد من عدم وجود مسافات زائدة في المفاتيح</li>
                                <li>تأكد من استخدام نفس المفاتيح من لوحة تحكم AliExpress</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem3">
                            <i class="ri-error-warning-line me-2"></i>
                            خطأ: "Access token expired"
                        </button>
                    </h2>
                    <div id="problem3" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                        <div class="accordion-body">
                            <strong>الحل:</strong>
                            <ul>
                                <li>Access Token ينتهي بعد فترة معينة (عادة شهر)</li>
                                <li>استخدم Refresh Token لتجديد الـ Access Token</li>
                                <li>أو قم بعملية OAuth مرة أخرى</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem4">
                            <i class="ri-error-warning-line me-2"></i>
                            لا تظهر المنتجات بعد الاستيراد
                        </button>
                    </h2>
                    <div id="problem4" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                        <div class="accordion-body">
                            <strong>الحل:</strong>
                            <ul>
                                <li>تحقق من أن المنتج تم حفظه في قاعدة البيانات</li>
                                <li>تأكد من أن حالة المنتج "منشور" وليس "مسودة"</li>
                                <li>امسح الذاكرة المؤقتة: <code>php artisan cache:clear</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Reference -->
        <div class="step-card">
            <h3 class="text-center mb-4">
                <i class="ri-code-box-line"></i>
                مرجع API - الروابط المتاحة
            </h3>

            <div class="table-responsive table-custom">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>الرابط</th>
                            <th>الوصف</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>/aliexpress-oauth-start</code></td>
                            <td>بدء عملية OAuth للحصول على Access Token</td>
                            <td><a href="{{ url('/aliexpress-oauth-start') }}" target="_blank" class="btn btn-sm btn-primary">زيارة</a></td>
                        </tr>
                        <tr>
                            <td><code>/test-aliexpress</code></td>
                            <td>اختبار الاتصال مع AliExpress API</td>
                            <td><a href="{{ url('/test-aliexpress') }}" target="_blank" class="btn btn-sm btn-success">اختبار</a></td>
                        </tr>
                        <tr>
                            <td><code>/test-aliexpress-token</code></td>
                            <td>اختبار إنشاء Token يدوياً</td>
                            <td><a href="{{ url('/test-aliexpress-token') }}" target="_blank" class="btn btn-sm btn-info">اختبار</a></td>
                        </tr>
                        <tr>
                            <td><code>/test-aliexpress-all</code></td>
                            <td>اختبار جميع وظائف API</td>
                            <td><a href="{{ url('/test-aliexpress-all') }}" target="_blank" class="btn btn-sm btn-warning">اختبار شامل</a></td>
                        </tr>
                        <tr>
                            <td><code>/products</code></td>
                            <td>إدارة المنتجات في متجرك</td>
                            <td><a href="{{ url('/products') }}" class="btn btn-sm btn-secondary">إدارة</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Best Practices -->
        <div class="step-card">
            <h3 class="text-center mb-4">
                <i class="ri-star-line"></i>
                أفضل الممارسات
            </h3>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <h5><i class="ri-shield-check-line"></i> الأمان</h5>
                        <ul class="mb-0">
                            <li>لا تشارك API Keys مع أحد</li>
                            <li>استخدم HTTPS في الموقع</li>
                            <li>قم بتحديث Access Token بانتظام</li>
                            <li>لا تحفظ المفاتيح في Git</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="success-box">
                        <h5><i class="ri-speed-line"></i> الأداء</h5>
                        <ul class="mb-0">
                            <li>استخدم Cache للمنتجات</li>
                            <li>قم بمزامنة الأسعار يومياً</li>
                            <li>حدد عدد الطلبات للـ API</li>
                            <li>استخدم Queue للعمليات الكبيرة</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="warning-box">
                        <h5><i class="ri-money-dollar-circle-line"></i> التسعير</h5>
                        <ul class="mb-0">
                            <li>أضف هامش ربح واقعي (20-50%)</li>
                            <li>راقب أسعار المنافسين</li>
                            <li>احسب تكلفة الشحن</li>
                            <li>قدم عروض وخصومات</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <h5><i class="ri-customer-service-2-line"></i> خدمة العملاء</h5>
                        <ul class="mb-0">
                            <li>كن واضحاً بشأن مدة الشحن</li>
                            <li>راقب جودة المنتجات</li>
                            <li>تابع الطلبات بانتظام</li>
                            <li>قدم دعماً ممتازاً</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <h4 class="mb-4">
                <i class="ri-links-line"></i>
                روابط مفيدة
            </h4>

            <div class="quick-links">
                <a href="https://openservice.aliexpress.com/" target="_blank" class="action-btn">
                    <i class="ri-external-link-line"></i>
                    منصة المطورين
                </a>
                <a href="https://ds.aliexpress.com/" target="_blank" class="action-btn">
                    <i class="ri-shopping-bag-line"></i>
                    برنامج الدروبشيبينغ
                </a>
                <a href="{{ url('/aliexpress-oauth-start') }}" class="action-btn">
                    <i class="ri-key-2-line"></i>
                    بدء OAuth
                </a>
                <a href="{{ url('/test-aliexpress') }}" class="action-btn">
                    <i class="ri-test-tube-line"></i>
                    اختبار الاتصال
                </a>
                <a href="{{ url('/dashboard') }}" class="action-btn">
                    <i class="ri-dashboard-line"></i>
                    لوحة التحكم
                </a>
            </div>

            <hr class="my-4">

            <p class="text-muted mb-0">
                <i class="ri-information-line"></i>
                تم إنشاء هذا الدليل في {{ now()->format('Y-m-d') }}
                <br>
                <small>للحصول على الدعم، تواصل مع فريق الدعم الفني</small>
            </p>

            <div class="mt-3">
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="ri-printer-line"></i>
                    طباعة الدليل
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
