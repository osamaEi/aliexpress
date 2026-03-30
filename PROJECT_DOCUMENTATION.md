# E-Commerce Platform — توثيق شامل للمشروع

> منصة متكاملة للـ Dropshipping مبنية على Laravel 12، تجمع بين AliExpress وبوابات دفع متعددة، وتدعم 4 أنواع من المستخدمين: عملاء، بائعون، موزعون، وإداريون.

---

## فهرس المحتويات

1. [نظرة عامة](#1-نظرة-عامة)
2. [التقنيات المستخدمة](#2-التقنيات-المستخدمة)
3. [هيكل المشروع](#3-هيكل-المشروع)
4. [تثبيت وتشغيل المشروع](#4-تثبيت-وتشغيل-المشروع)
5. [متغيرات البيئة](#5-متغيرات-البيئة)
6. [أنواع المستخدمين وصلاحياتهم](#6-أنواع-المستخدمين-وصلاحياتهم)
7. [قاعدة البيانات والنماذج](#7-قاعدة-البيانات-والنماذج)
8. [الميزات والوحدات الرئيسية](#8-الميزات-والوحدات-الرئيسية)
9. [الـ Routes (المسارات)](#9-الـ-routes-المسارات)
10. [الـ Controllers](#10-الـ-controllers)
11. [الـ Services](#11-الـ-services)
12. [الـ Middleware](#12-الـ-middleware)
13. [بوابات الدفع](#13-بوابات-الدفع)
14. [تكامل AliExpress](#14-تكامل-aliexpress)
15. [نظام الاشتراكات والتجربة المجانية](#15-نظام-الاشتراكات-والتجربة-المجانية)
16. [نظام الأرباح](#16-نظام-الأرباح)
17. [نظام المحفظة](#17-نظام-المحفظة)
18. [الواجهة الأمامية](#18-الواجهة-الأمامية)
19. [الـ Artisan Commands](#19-الـ-artisan-commands)
20. [الأمان](#20-الأمان)
21. [الـ Deployment للإنتاج](#21-الـ-deployment-للإنتاج)

---

## 1. نظرة عامة

منصة Dropshipping متكاملة تتيح:
- **للبائعين (Sellers):** استيراد المنتجات من AliExpress وبيعها بهامش ربح محدد.
- **للموزعين (Distributors):** إنشاء كتالوج منتجات محلي وإدارة الكوبونات والطلبات.
- **للعملاء (Customers):** تصفح المنتجات وإتمام الشراء.
- **للإداريين (Admins):** إدارة كاملة للمنصة، المستخدمين، الأرباح، والإعدادات.

**الدعم اللغوي:** عربي وإنجليزي
**العملات المدعومة:** AED، USD، EUR، GBP، SAR

---

## 2. التقنيات المستخدمة

### Backend
| التقنية | الإصدار | الاستخدام |
|---------|---------|-----------|
| PHP | 8.2+ | لغة البرمجة |
| Laravel | 12 | الـ Framework الرئيسي |
| SQLite | - | قاعدة البيانات (للتطوير) |
| MySQL | - | قاعدة البيانات (للإنتاج) |
| Laravel Breeze | - | نظام المصادقة |
| Laravel Sail | - | دعم Docker |
| Laravel Pail | - | عرض الـ Logs |
| AliExpress SDK | lpcs007/ae-php-sdk | تكامل AliExpress |

### Frontend
| التقنية | الإصدار | الاستخدام |
|---------|---------|-----------|
| Vite | 7.0.7 | أداة البناء |
| Tailwind CSS | 3.1.0 | الـ CSS Framework |
| Alpine.js | 3.4.2 | التفاعلية في الـ Frontend |
| Axios | 1.1.0 | HTTP Client |
| Blade | - | محرك القوالب |

### Development Tools
| الأداة | الاستخدام |
|--------|-----------|
| PHPUnit 11.5.3 | الاختبارات |
| Laravel Pint 1.24 | تنسيق الكود |
| Mockery 1.6 | Mock للاختبارات |
| Concurrently 9.0.1 | تشغيل عدة خدمات معاً |

---

## 3. هيكل المشروع

```
ecommerce/
├── app/
│   ├── Console/
│   │   └── Commands/          # 5 أوامر Artisan مخصصة
│   ├── Helpers/
│   │   └── helpers.php        # دوال مساعدة عامة
│   ├── Http/
│   │   ├── Controllers/       # 28+ Controller
│   │   ├── Middleware/        # 7 Middleware مخصصة
│   │   └── Requests/          # Form Requests للتحقق
│   ├── Models/                # 26 Eloquent Model
│   └── Services/              # 10 Service Classes
├── bootstrap/
│   └── app.php                # Bootstrap التطبيق
├── config/
│   ├── app.php
│   ├── database.php
│   ├── paymob.php             # إعدادات Paymob
│   ├── paypal.php             # إعدادات PayPal
│   └── ziina.php              # إعدادات Ziina
├── database/
│   ├── migrations/            # 48 migration
│   ├── factories/
│   └── seeders/
├── public/                    # الملفات العامة
├── resources/
│   ├── css/app.css            # Tailwind CSS
│   ├── js/                    # Alpine.js & Axios
│   └── views/                 # 50+ Blade Templates
│       ├── admin/
│       ├── seller/
│       ├── distributor/
│       ├── customer/
│       ├── products/
│       ├── orders/
│       ├── subscriptions/
│       ├── auth/
│       ├── profile/
│       ├── components/        # Reusable Components
│       ├── layouts/
│       ├── emails/
│       └── partials/
├── routes/
│   ├── web.php                # المسارات الرئيسية (~38KB)
│   ├── api.php
│   └── auth.php               # مسارات المصادقة
├── storage/                   # Logs والملفات المرفوعة
├── tests/
├── .env.example
├── composer.json
├── package.json
├── vite.config.js
└── tailwind.config.js
```

---

## 4. تثبيت وتشغيل المشروع

### المتطلبات
- PHP 8.2+
- Composer
- Node.js + NPM
- SQLite أو MySQL

### خطوات التثبيت

```bash
# 1. استنساخ المشروع
git clone <repository-url>
cd ecommerce

# 2. نسخ ملف البيئة
cp .env.example .env

# 3. تثبيت مكتبات PHP
composer install

# 4. توليد مفتاح التطبيق
php artisan key:generate

# 5. تثبيت مكتبات Node.js
npm install

# 6. تشغيل الـ Migrations
php artisan migrate

# 7. بناء الـ Assets
npm run build
```

### تشغيل بيئة التطوير

```bash
# تشغيل كل الخدمات دفعة واحدة (الأفضل)
composer run dev

# أو تشغيل كل خدمة بشكل منفصل:
php artisan serve          # Laravel Server على port 8000
npm run dev                # Vite Dev Server على port 5173
php artisan queue:listen   # Queue Worker للعمليات الخلفية
php artisan pail           # عرض الـ Logs بشكل مباشر
```

### تشغيل الاختبارات

```bash
composer run test
```

---

## 5. متغيرات البيئة

### الإعدادات الأساسية
```env
APP_NAME="اسم التطبيق"
APP_ENV=local          # local / production
APP_KEY=base64:...     # يتولد تلقائياً بـ php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost
```

### قاعدة البيانات
```env
# للتطوير (SQLite)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# للإنتاج (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=
```

### الجلسات والقوائم
```env
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

### البريد الإلكتروني
```env
MAIL_MAILER=log          # log للتطوير / smtp للإنتاج
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
ADMIN_EMAIL=admin@example.com
```

### AliExpress API
```env
ALIEXPRESS_API_KEY=
ALIEXPRESS_API_SECRET=
ALIEXPRESS_TRACKING_ID=
ALIEXPRESS_ACCESS_TOKEN=
```

### PayPal
```env
PAYPAL_MODE=sandbox        # sandbox / live
PAYPAL_SANDBOX_CLIENT_ID=
PAYPAL_SANDBOX_CLIENT_SECRET=
PAYPAL_LIVE_CLIENT_ID=
PAYPAL_LIVE_CLIENT_SECRET=
```

### Ziina (بوابة الدفع الإماراتية)
```env
ZIINA_API_KEY=
ZIINA_TEST_MODE=true
```

### Paymob
```env
PAYMOB_API_KEY=
PAYMOB_HMAC=
PAYMOB_IFRAME_ID=
PAYMOB_CARD_INTEGRATION_ID=
PAYMOB_WALLET_INTEGRATION_ID=
```

### Google reCAPTCHA
```env
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=
```

---

## 6. أنواع المستخدمين وصلاحياتهم

### Customer (العميل)
- تصفح وشراء المنتجات
- مشاهدة سجل الطلبات
- إدارة الحساب الشخصي

### Seller (البائع)
- **فترة تجريبية:** 24 ساعة بصلاحيات كاملة بعد التسجيل
- **بعد التجربة:** يحتاج اشتراك مدفوع للاستمرار
- **شرط إضافي:** إكمال إعداد الملف الشخصي + إعدادات الأرباح
- استيراد المنتجات من AliExpress
- تحديد هامش الربح لكل تصنيف فرعي
- إدارة الطلبات
- تتبع الشحنات
- رفع تذاكر الدعم الفني

### Distributor (الموزع)
- إنشاء كتالوج منتجات محلي خاص
- إدارة الفئات والتصنيفات
- إدارة الطلبات الواردة
- إنشاء وإدارة كوبونات الخصم
- رفع تذاكر الدعم الفني

### Admin (المدير)
- الإدارة الكاملة للمنصة
- إدارة المستخدمين (حظر، تفعيل، مراجعة)
- إدارة الاشتراكات والخطط
- إعداد هوامش الأرباح على مستوى الفئات
- إدارة المحافظ والسحوبات
- إدارة العملات والدول
- الموافقة على منتجات الموزعين
- إدارة توكينات AliExpress
- مشاهدة تقارير الأرباح

---

## 7. قاعدة البيانات والنماذج

### المخطط العام (26 Model)

#### User — المستخدم
```
users
├── id, name, email, password, phone
├── full_name, company_name
├── type: customer | seller | distributor | admin
│
├── [Seller Fields]
│   ├── main_activity, sub_activity
│   ├── is_verified, profit_settings_completed
│   └── setup_completed_at
│
├── [Distributor Fields]
│   ├── store_name, store_slug
│   ├── default_currency
│   ├── commercial_register
│   └── social_media_accounts (JSON)
│
├── [Withdrawal Fields]
│   ├── withdrawal_method
│   ├── paypal_email
│   ├── bank_name, account_holder, iban, swift
│   └── wallet_provider
│
└── is_blocked, block_reason, marketing_code
```

#### Product — المنتج
```
products
├── id, seller_id, category_id
├── name, name_ar (عربي/إنجليزي)
├── description, description_ar
├── price, currency, original_price
├── seller_amount, admin_amount (الأرباح المحسوبة)
├── stock_quantity, track_inventory
├── has_variations
│
└── [AliExpress Fields]
    ├── aliexpress_id
    ├── aliexpress_price, aliexpress_variants (JSON)
    └── type: aliexpress | distributor
```

#### Order — الطلب
```
orders
├── id, order_number
├── user_id (البائع), product_id
├── quantity, unit_price, total_price
├── freight_amount, total_amount
│
├── [Profit Tracking]
│   ├── aliexpress_profit
│   ├── admin_category_profit
│   └── seller_profit
│
├── [Customer Info]
│   ├── customer_name, email, phone
│   └── shipping: address, city, country, zip
│
├── status, payment_status
├── tracking_number
│
└── [AliExpress Integration]
    ├── aliexpress_order_id
    └── aliexpress_response (JSON)
```

#### Category — الفئة
```
categories
├── id, name, name_ar
├── parent_id (للتصنيفات الهرمية)
├── aliexpress_category_id
└── is_active, order
```

#### Subscription — الاشتراك
```
subscriptions
├── id, name, name_ar, description, description_ar
├── price, duration_days, commission_rate
└── [Features]
    ├── max_products
    ├── max_orders_per_month
    ├── priority_support (boolean)
    ├── analytics_access (boolean)
    ├── bulk_import (boolean)
    └── api_access (boolean)
```

#### Wallet & WalletTransaction — المحفظة
```
wallets
├── id, user_id
├── balance, pending_balance
├── currency, is_active

wallet_transactions
├── id, wallet_id
├── type: credit | debit
├── transaction_type (نوع العملية)
├── amount
├── balance_before, balance_after
└── status
```

#### Coupon — الكوبون
```
coupons
├── id, distributor_id
├── code, type: percentage | fixed
├── value (قيمة الخصم)
├── max_uses, used_count
├── expires_at
└── country_id (مرتبط بدولة محددة)
```

#### باقي النماذج
| النموذج | الوصف |
|---------|-------|
| `UserSubscription` | تتبع اشتراكات المستخدمين |
| `PaymentTransaction` | سجل المدفوعات (PayPal, Ziina, Paymob) |
| `Shipping` | معلومات الشحن والتتبع |
| `CouponUsage` | سجل استخدام الكوبونات |
| `Currency` | إدارة العملات وأسعار الصرف |
| `Country` | إدارة الدول |
| `SellerSubcategoryProfit` | إعدادات الربح للبائع لكل تصنيف فرعي |
| `AdminCategoryProfit` | إعدادات ربح الإدارة لكل فئة |
| `AliExpressToken` | توكينات OAuth لـ AliExpress |
| `Setting` | إعدادات التطبيق (key-value) |
| `Ticket` + `TicketReply` | نظام تذاكر الدعم الفني |
| `ProductImage` | صور إضافية للمنتجات |
| `ProductVariation` | تنويعات المنتج (SKUs) |
| `WithdrawalRequest` | طلبات السحب |
| `Notification` | الإشعارات |

---

## 8. الميزات والوحدات الرئيسية

### A. نظام البحث والاستيراد من AliExpress
- البحث عن المنتجات بالكلمات المفتاحية
- مشاهدة تفاصيل المنتج والتنويعات (SKUs)
- استيراد المنتج بضغطة واحدة مع كل بياناته
- المزامنة التلقائية لبيانات SKU

### B. نظام الطلبات
- دعم نوعين: طلبات AliExpress وطلبات الموزع
- حساب تكاليف الشحن
- تطبيق كوبونات الخصم
- إرسال الطلب تلقائياً لـ AliExpress
- تتبع حالة الطلب بشكل آلي
- Webhooks لتحديثات الحالة اللحظية

### C. نظام تعدد العملات
- تحويل الأسعار تلقائياً بناءً على أسعار الصرف
- حفظ تفضيل العملة في الـ Session
- عرض الأسعار بالعملة المختارة في كل مكان

### D. الواجهة الثنائية اللغة
- العربية والإنجليزية
- تبديل اللغة من أي صفحة
- حفظ اللغة المفضلة

### E. إدارة الكوبونات
- يصدرها الموزع لعملاء دولة محددة
- نوعان: خصم نسبة مئوية أو مبلغ ثابت
- تحديد عدد مرات الاستخدام وتاريخ الانتهاء

### F. نظام الدعم الفني (Tickets)
- البائعون والموزعون يرفعون التذاكر
- الإدارة ترد وتتابع التذاكر
- سجل كامل للمحادثات

---

## 9. الـ Routes (المسارات)

### مسارات عامة (بدون تسجيل دخول)
```
GET  /                          # الصفحة الرئيسية
GET  /lang/{locale}             # تغيير اللغة
GET  /currency/{code}           # تغيير العملة
GET  /aliexpress                # صفحة البحث في AliExpress
POST /aliexpress/search         # البحث عن منتج
GET  /aliexpress/product/{id}   # تفاصيل منتج AliExpress

# Callbacks بوابات الدفع
GET  /payment/callback          # PayPal return
GET  /paymob/callback           # Paymob return
POST /paymob/webhook            # Paymob webhook
POST /webhooks/aliexpress/*     # AliExpress webhooks

# تسجيل البائعين والموزعين (نماذج متعددة الخطوات)
GET/POST /seller/register/*
GET/POST /distributor/register/*
```

### مسارات المستخدم المسجل
```
# الملف الشخصي
GET/PUT  /profile

# المحفظة
GET      /wallet
POST     /wallet/deposit
POST     /wallet/withdraw

# الاشتراكات
GET      /subscriptions
POST     /subscriptions/{id}/subscribe

# المنتجات
GET      /products
GET/POST /products/search-china
GET/POST /products/search-distributor
POST     /products/china/import
POST     /products/assign

# الطلبات
GET      /orders
POST     /orders
GET      /orders/{id}
GET/POST /orders/distributor/*
```

### مسارات البائع (`/seller/`)
```
GET  /seller/dashboard
GET  /seller/profit-settings
POST /seller/profit-settings
GET  /seller/shipping
GET  /seller/tickets
POST /seller/tickets
```

### مسارات الموزع (`/distributor/`)
```
GET  /distributor/dashboard
/distributor/products/*          # CRUD المنتجات
/distributor/categories/*        # CRUD الفئات
/distributor/orders/*            # إدارة الطلبات
/distributor/coupons/*           # CRUD الكوبونات
/distributor/tickets/*           # الدعم الفني
```

### مسارات الإدارة (`/admin/`)
```
GET  /admin/dashboard
/admin/tokens/*                  # توكينات AliExpress
/admin/subscriptions/*           # CRUD الاشتراكات
/admin/orders/*                  # إدارة الطلبات
/admin/order-profits/*           # تقارير الأرباح
/admin/shipping/*                # إدارة الشحنات
/admin/users/*                   # إدارة المستخدمين
/admin/wallets/*                 # إدارة المحافظ والسحوبات
/admin/categories/*              # إدارة الفئات
/admin/category-profits/*        # إعدادات أرباح الفئات
/admin/currencies/*              # إدارة العملات
/admin/countries/*               # إدارة الدول
/admin/distributor-products/*    # مراجعة منتجات الموزعين
/admin/settings/*                # إعدادات التطبيق
```

---

## 10. الـ Controllers

| Controller | الحجم | الوظيفة |
|-----------|-------|---------|
| `ProductController` | ~92KB | إدارة المنتجات كاملة + استيراد AliExpress |
| `OrderController` | ~51KB | معالجة الطلبات + ربط AliExpress |
| `AliExpressController` | - | واجهة API مع AliExpress |
| `DistributorController` | - | لوحة تحكم الموزع |
| `SellerController` | - | لوحة تحكم البائع |
| `WalletController` | - | عمليات المحفظة |
| `SubscriptionController` | - | إدارة الاشتراكات |
| `PaymentController` | - | معالجة المدفوعات (PayPal) |
| `PaymobController` | - | تكامل Paymob |
| `AdminUserController` | - | إدارة المستخدمين |
| `AdminOrderController` | - | إدارة الطلبات للإدارة |
| `AdminWalletController` | - | إدارة المحافظ والسحوبات |
| `CategoryController` | - | إدارة الفئات |
| `CurrencyController` | - | إدارة العملات |
| `ProfileController` | - | تعديل الملف الشخصي |

---

## 11. الـ Services

| Service | الحجم | الوظيفة |
|---------|-------|---------|
| `AliExpressService` | ~97KB | الوظيفة الرئيسية لـ API AliExpress |
| `AliExpressAuthService` | - | إدارة OAuth Tokens |
| `AliExpressWebhookService` | - | معالجة Webhooks الواردة |
| `AliexpressTextService` | - | البحث النصي في منتجات AliExpress |
| `AliExpressDropshippingService` | - | ميزات الـ Dropshipping |
| `PayPalService` | - | تكامل PayPal |
| `ZiinaPaymentService` | - | معالجة مدفوعات Ziina |
| `WalletService` | - | عمليات المحفظة (إيداع، سحب، تحويل) |
| `WhatsAppOTPService` | - | التحقق عبر OTP على WhatsApp |
| `TranslationService` | - | دعم تعدد اللغات |

---

## 12. الـ Middleware

| Middleware | الوظيفة |
|-----------|---------|
| `IsAdmin` | يسمح فقط للمدراء |
| `IsDistributor` | يسمح فقط للموزعين |
| `SellerAccessControl` | يتحكم في وصول البائع (تجربة/اشتراك) |
| `SetLocale` | تطبيق اللغة المختارة |
| `SetCurrency` | تطبيق العملة المختارة |
| `PermissionMiddleware` | التحقق من الصلاحيات (RBAC) |
| `RoleMiddleware` | التحقق من الدور |

---

## 13. بوابات الدفع

### PayPal
- يدعم وضع Sandbox للتطوير ووضع Live للإنتاج
- يُستخدم للإيداع في المحفظة ودفع الاشتراكات
- Callback URL: `/payment/callback`

### Ziina
- بوابة دفع إماراتية، تعمل بالدرهم الإماراتي (AED)
- مناسبة للمستخدمين في الإمارات

### Paymob
- بوابة دفع تدعم الإمارات والشرق الأوسط
- تدعم الدفع بالبطاقات والمحافظ الإلكترونية
- Webhook URL: `/paymob/webhook`
- Callback URL: `/paymob/callback`

---

## 14. تكامل AliExpress

### كيف يعمل
1. **OAuth:** البائع يوصل حسابه بـ AliExpress عبر OAuth
2. **البحث:** البحث عن منتجات بالكلمات المفتاحية أو رقم المنتج
3. **الاستيراد:** نقل المنتج بكل بياناته (صور، تنويعات، أسعار) للمنصة
4. **الطلب:** عند شراء العميل، يُرسل الطلب تلقائياً لـ AliExpress
5. **التتبع:** رقم التتبع يُزامن تلقائياً من AliExpress

### أوامر AliExpress المخصصة
```bash
php artisan aliexpress:auth             # توليد OAuth Token
php artisan aliexpress:test-api         # اختبار الاتصال بـ API
php artisan orders:sync-status          # مزامنة حالات الطلبات
php artisan products:sync-sku           # مزامنة بيانات SKU
php artisan orders:recalculate-profits  # إعادة حساب الأرباح
```

### Webhooks
```
POST /webhooks/aliexpress/order-status     # تحديث حالة الطلب
POST /webhooks/aliexpress/tracking         # تحديث التتبع
```

---

## 15. نظام الاشتراكات والتجربة المجانية

### فترة التجربة المجانية للبائع
- **المدة:** 24 ساعة من وقت التسجيل
- **الصلاحيات:** كاملة خلال فترة التجربة
- **بعد انتهاء التجربة:** يُقيَّد الوصول حتى الاشتراك

### شرط اكتمال الإعداد
قبل الوصول الكامل، يجب على البائع إكمال:
1. الملف الشخصي
2. إعدادات الأرباح لكل تصنيف

### خطط الاشتراك
كل خطة تحدد:
- السعر ومدة الاشتراك
- نسبة العمولة
- الحد الأقصى للمنتجات
- الحد الأقصى للطلبات شهرياً
- الميزات المتاحة: (دعم أولوية، تحليلات، استيراد مجمع، API)

> للمزيد: `SELLER_TRIAL_SYSTEM.md`

---

## 16. نظام الأرباح

### آلية حساب الربح
```
سعر البيع = سعر AliExpress + ربح الإدارة + ربح البائع

ربح الإدارة  → يُحدده الإداري على مستوى الفئة (AdminCategoryProfit)
ربح البائع   → يُحدده البائع على مستوى التصنيف الفرعي (SellerSubcategoryProfit)
```

### التقارير
- `admin/order-profits` — تقرير الأرباح التفصيلي لكل طلب
- كل طلب يحفظ: `seller_profit`, `admin_category_profit`, `aliexpress_profit`

### إعادة حساب الأرباح
```bash
php artisan orders:recalculate-profits
```

---

## 17. نظام المحفظة

### العمليات المتاحة
| العملية | الوصف |
|---------|-------|
| إيداع | شحن المحفظة عبر PayPal / Ziina / Paymob |
| سحب | طلب سحب للـ PayPal / حساب بنكي / محفظة إلكترونية |
| تحويل | تحويل داخلي بين المستخدمين |

### دورة حياة السحب
```
طلب سحب (pending) → مراجعة الإدارة → موافقة/رفض → تنفيذ (completed)
```

### طرق السحب المدعومة
- PayPal (عبر البريد الإلكتروني)
- حساب بنكي (IBAN + SWIFT)
- محفظة إلكترونية (Wallet Provider)

---

## 18. الواجهة الأمامية

### تقنيات الـ Frontend
- **Blade Templates:** محرك القوالب الرئيسي
- **Tailwind CSS:** التصميم والتخطيط
- **Alpine.js:** التفاعلية (Modals, Dropdowns, Forms)
- **Axios:** طلبات AJAX

### لوحات التحكم
| المستخدم | الصفحة الرئيسية |
|---------|----------------|
| Customer | `/customer/dashboard` |
| Seller | `/seller/dashboard` |
| Distributor | `/distributor/dashboard` |
| Admin | `/admin/dashboard` |

### الصفحات الرئيسية
```
resources/views/
├── welcome.blade.php              # الصفحة الرئيسية
├── products/
│   ├── search.blade.php           # بحث المنتجات
│   ├── detail.blade.php           # تفاصيل المنتج (AliExpress)
│   ├── detail-distributor.blade.php  # تفاصيل منتج الموزع
│   └── assigned.blade.php         # المنتجات المستوردة
├── orders/                        # صفحات الطلبات
├── subscriptions/                 # خطط الاشتراك والدفع
├── auth/                          # تسجيل الدخول والتسجيل
└── emails/                        # قوالب الإيميلات
```

---

## 19. الـ Artisan Commands

```bash
# AliExpress
php artisan aliexpress:auth                  # OAuth Token جديد
php artisan aliexpress:test-api              # اختبار الاتصال
php artisan aliexpress:test-order-sync       # اختبار مزامنة الطلبات
php artisan products:sync-sku                # مزامنة بيانات SKU
php artisan orders:sync-status               # مزامنة حالات الطلبات
php artisan orders:recalculate-profits       # إعادة حساب الأرباح

# Laravel القياسية المهمة
php artisan migrate                          # تشغيل الـ Migrations
php artisan db:seed                          # تعبئة بيانات تجريبية
php artisan queue:work                       # تشغيل Queue Worker
php artisan config:cache                     # Cache الإعدادات
php artisan route:cache                      # Cache المسارات
php artisan view:cache                       # Cache القوالب
php artisan storage:link                     # ربط Storage بالـ Public
```

---

## 20. الأمان

### الحماية المطبقة
| الآلية | التفاصيل |
|--------|---------|
| CSRF Protection | على جميع النماذج |
| Email Verification | مطلوبة للبائعين والموزعين |
| OTP via WhatsApp | تحقق إضافي للتسجيل |
| RBAC | صلاحيات مبنية على الأدوار |
| Password Hashing | bcrypt عبر Laravel |
| Google reCAPTCHA | على نماذج التسجيل |
| Soft Deletes | حماية البيانات من الحذف الدائم |
| Middleware Guards | حماية كل مجموعة مسارات |

### هيكل الصلاحيات
```
Admin     → وصول كامل لكل شيء
Seller    → (SellerAccessControl middleware) → مقيَّد بالاشتراك
Distributor → (IsDistributor middleware)
Customer  → وصول محدود (تسوق فقط)
```

---

## 21. الـ Deployment للإنتاج

### خطوات النشر
```bash
# 1. تثبيت المكتبات بدون Dev dependencies
composer install --no-dev --optimize-autoloader
npm install

# 2. بناء الـ Assets
npm run build

# 3. تشغيل الـ Migrations
php artisan migrate --force

# 4. Cache كل شيء
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. ربط Storage
php artisan storage:link
```

### إعدادات الـ Queue Worker (Supervisor)
```ini
[program:ecommerce-worker]
command=php /path/to/project/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
```

### متطلبات السيرفر
- PHP 8.2+ مع Extensions: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`
- MySQL 8+
- Nginx أو Apache
- Composer
- Node.js (لبناء الـ Assets فقط)

---

## ملاحظات للمطور الجديد

### أهم الملفات للبدء
1. **`routes/web.php`** — ابدأ من هنا لفهم بنية التطبيق
2. **`app/Models/User.php`** — فهم نظام المستخدمين المتعدد
3. **`app/Http/Controllers/ProductController.php`** — أكبر controller وأكثرها تعقيداً
4. **`app/Services/AliExpressService.php`** — كل تكامل AliExpress هنا
5. **`app/Http/Middleware/SellerAccessControl.php`** — منطق التحكم في وصول البائع

### أشياء مهمة تعرفها
- **الـ Queue مهم جداً:** عمليات AliExpress تعمل في الخلفية، لازم `queue:work` شغال
- **التنويعات (Variants):** مخزنة كـ JSON في `aliexpress_variants` في جدول المنتجات
- **الأرباح محسوبة مسبقاً:** تُحفظ في الطلب وقت الشراء، مش محسوبة on-the-fly
- **العملة والـ Locale:** محفوظين في الـ Session، متاح في كل مكان عبر `app()->getLocale()` و helpers مخصصة
- **الإيميلات في التطوير:** تروح لـ Log بدل الإرسال الفعلي (`MAIL_MAILER=log`)

---

*آخر تحديث: مارس 2026*
