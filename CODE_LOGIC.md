# منطق الكود — كيف يعمل التطبيق من الداخل

---

## فهرس

1. [دورة حياة الـ Request](#1-دورة-حياة-الـ-request)
2. [نظام التحكم في وصول البائع](#2-نظام-التحكم-في-وصول-البائع)
3. [استيراد منتج من AliExpress](#3-استيراد-منتج-من-aliexpress)
4. [إنشاء طلب جديد](#4-إنشاء-طلب-جديد)
5. [إرسال الطلب لـ AliExpress](#5-إرسال-الطلب-لـ-aliexpress)
6. [حساب الأرباح](#6-حساب-الأرباح)
7. [نظام المحفظة](#7-نظام-المحفظة)
8. [نظام الاشتراكات](#8-نظام-الاشتراكات)
9. [تكامل AliExpress API](#9-تكامل-aliexpress-api)
10. [نماذج البيانات الأساسية](#10-نماذج-البيانات-الأساسية)

---

## 1. دورة حياة الـ Request

كل request بيمر بالمراحل دي بالترتيب:

```
HTTP Request
     │
     ▼
routes/web.php          ← تحديد Controller والـ Method
     │
     ▼
Middleware Stack:
  ├── SetLocale          ← تطبيق اللغة (ar/en)
  ├── SetCurrency        ← تطبيق العملة
  ├── auth               ← هل المستخدم مسجل دخول؟
  ├── IsAdmin/IsDistributor ← هل له الصلاحية؟
  └── SellerAccessControl ← هل البائع عنده اشتراك؟
     │
     ▼
Controller Method        ← منطق العمل
     │
     ▼
Service / Model          ← العمليات على البيانات
     │
     ▼
Blade View               ← عرض النتيجة
```

---

## 2. نظام التحكم في وصول البائع

**الملف:** `app/Http/Middleware/SellerAccessControl.php`

### كيف يعمل

```
request وصل
     │
     ▼
هل المسار مسموح دايماً؟ (subscriptions, profile, profit-setup, tickets)
     │ نعم                    │ لا
     ▼                        ▼
اسمح بالمرور         هل اكتمل الـ Setup؟
                             │
                    ┌────────┴────────┐
                   لا                نعم
                    │                 │
                    ▼                 ▼
             redirect           هل عنده اشتراك نشط؟
             لصفحة               أو لسه في فترة
             الـ Setup            التجربة؟
                             ┌────────┴────────┐
                            لا                نعم
                             │                 │
                             ▼                 ▼
                      redirect           اسمح بالمرور
                      لصفحة
                      الاشتراكات
```

### شرط اكتمال الـ Setup — `User::hasCompletedSetup()`

```php
// البائع لازم يكمل الاتنين:
return $this->hasCompletedProfile() && $this->profit_settings_completed;

// hasCompletedProfile() بتتحقق من:
// phone, commercial_register, logo, company_name, currency, withdrawal_method
```

### شرط الاشتراك النشط

```php
// فترة التجربة (24 ساعة من التسجيل)
public function isInTrialPeriod(): bool
{
    return $this->created_at->diffInHours(now()) < 24;
}

// الاشتراك النشط
public function hasActiveSubscription(): bool
{
    return $this->userSubscriptions()
        ->where('status', 'active')
        ->where('end_date', '>', now())
        ->exists();
}

// الـ Middleware بيتحقق:
$hasAccess = $user->isInTrialPeriod() || $user->hasActiveSubscription();
```

---

## 3. استيراد منتج من AliExpress

**الملف:** `app/Http/Controllers/ProductController.php`

### خطوات الاستيراد

```
البائع يبحث عن منتج
        │
        ▼
POST /products/search-china
        │
        ▼
AliExpressService::searchProductsByText(keyword)
        │
  ┌─────▼──────────────────────────────┐
  │  API: aliexpress.ds.text.search     │
  │  Returns: [{                        │
  │    itemId, title, images,           │
  │    sale_price, original_price,      │
  │    evaluateRate, orders             │
  │  }]                                 │
  └─────┬──────────────────────────────┘
        │
        ▼
عرض النتائج للبائع
        │
        ▼
البائع يختار منتج → GET /aliexpress/product/{id}
        │
        ▼
AliExpressService::getProductDetails(productId)
        │
  ┌─────▼──────────────────────────────┐
  │  API: aliexpress.ds.product.get     │
  │  Returns: {                         │
  │    title, description, images,      │
  │    ae_item_sku_info_dtos: [         │
  │      { sku_id, price, quantity,     │
  │        sku_attr (Color/Size) }      │
  │    ]                               │
  │  }                                  │
  └─────┬──────────────────────────────┘
        │
        ▼
POST /products/china/import
        │
        ▼
ProductController::importFromChina()
        │
  ┌─────▼──────────────────────────────┐
  │  إنشاء Product record:              │
  │  {                                  │
  │    seller_id: current user,         │
  │    aliexpress_id: productId,        │
  │    name: title,                     │
  │    aliexpress_price: sale_price,    │
  │    aliexpress_variants: [sku_data], │
  │    price: (محسوب),                  │
  │    seller_amount: (هامش البائع),    │
  │    admin_amount: (عمولة المنصة)     │
  │  }                                  │
  └─────────────────────────────────────┘
```

### حساب السعر عند الاستيراد

```php
// سعر AliExpress (التكلفة)
$aliexpressPrice = $productData['sale_price'];

// عمولة الإدارة للفئة دي
$adminProfit = AdminCategoryProfit::getProfitForCategory($categoryId);

// هامش ربح البائع للتصنيف الفرعي
$sellerProfit = SellerSubcategoryProfit::getProfitForSeller($sellerId, $subcategoryId);

// السعر النهائي للعميل
$finalPrice = $aliexpressPrice + $adminProfit + $sellerProfit;

// حفظ كل قيمة منفصلة في جدول Product
$product->aliexpress_price = $aliexpressPrice;
$product->admin_amount     = $adminProfit;
$product->seller_amount    = $sellerProfit;
$product->price            = $finalPrice;
```

---

## 4. إنشاء طلب جديد

**الملف:** `app/Http/Controllers/OrderController.php` → `store()`

### تسلسل العمليات

```php
// Step 1: Validate
$request->validate([
    'product_id'        => 'required|exists:products,id',
    'quantity'          => 'required|integer|min:1',
    'customer_name'     => 'required|string',
    'customer_phone'    => 'required|string',
    'shipping_address'  => 'required|string',
    'shipping_country'  => 'required|string',
    // ...
]);

// Step 2: Load product & calculate total
$product    = Product::findOrFail($productId);
$unitPrice  = $product->price;                // السعر بالعملة الحالية
$freight    = $this->calculateFreight();      // تكلفة الشحن من AliExpress
$total      = ($unitPrice * $quantity) + $freight;

// Step 3: Check wallet balance
$wallet = $user->wallet;
if (!$wallet || $wallet->balance < $total) {
    return back()->withErrors([
        'wallet' => 'رصيد غير كافٍ. المطلوب: ' . $total . ' | المتاح: ' . $wallet->balance
    ]);
}

// Step 4: Create order record
$order = Order::create([
    'order_number'    => $this->generateOrderNumber(),
    'user_id'         => $user->id,
    'product_id'      => $product->id,
    'quantity'        => $quantity,
    'unit_price'      => $unitPrice,
    'freight_amount'  => $freight,
    'total_price'     => $unitPrice * $quantity,
    'total_amount'    => $total,
    'status'          => 'pending',
    'payment_status'  => 'paid',
    // customer & shipping info...
    'selected_sku_attr' => $request->sku_attr, // التنويع المختار (Color/Size)
]);

// Step 5: Debit wallet immediately
$wallet->debit($total, 'دفع طلب رقم #' . $order->order_number);

// Step 6: Dispatch event → async processing
OrderCreated::dispatch($order);
```

### حالات الطلب (Status Flow)

```
pending ──► processing ──► placed ──► shipped ──► delivered
                │
                ▼
             failed (لو AliExpress رفض الطلب)
```

---

## 5. إرسال الطلب لـ AliExpress

**الملف:** `app/Http/Controllers/OrderController.php` → `placeOnAliexpress()`

### منطق اختيار الـ SKU

```php
// محاولات الـ fallback بالترتيب:
$skuAttr = null;

// 1. من طلب المستخدم
if ($order->selected_sku_attr) {
    $skuAttr = $order->selected_sku_attr;
}

// 2. من بيانات المنتج المخزنة
elseif ($product->aliexpress_variants) {
    $variants = json_decode($product->aliexpress_variants, true);
    $skuAttr  = $variants[0]['sku_attr'] ?? null;
}

// 3. من AliExpress API مباشرة
else {
    $details = $aliexpressService->getProductDetails($product->aliexpress_id);
    $skuAttr = $details['skus'][0]['sku_attr'] ?? null;
}
```

### البيانات اللي بتتبعت لـ AliExpress

```php
$orderPayload = [
    'logistics_address' => [
        'contact_person' => $order->customer_name,
        'mobile_no'      => $order->customer_phone,
        'phone_country'  => $countryCode,           // كود الدولة (+20 مثلاً)
        'address'        => $order->shipping_address,
        'city'           => $order->shipping_city,
        'country'        => $order->shipping_country,
        'province'       => $order->shipping_province,
        'zip'            => $order->shipping_zip,
    ],
    'product_items' => [[
        'product_id'    => $product->aliexpress_id,
        'product_count' => $order->quantity,
        'sku_attr'      => $skuAttr,                // الـ SKU المختار
    ]],
    'ds_extend_request' => [
        'payment' => [
            'pay_currency' => 'USD',
            'try_to_pay'   => true,
        ]
    ]
];
```

### معالجة الـ Response

```php
$response = $aliexpressService->createOrder($orderPayload);

if ($response['is_success']) {
    $order->update([
        'status'              => 'placed',
        'aliexpress_order_id' => $response['order_id'],
        'placed_at'           => now(),
    ]);
} else {
    $order->update([
        'status'      => 'failed',
        'admin_notes' => $response['error_message'],
    ]);
    // إعادة الفلوس للمحفظة
    $wallet->credit($order->total_amount, 'استرداد طلب فاشل #' . $order->order_number);
}
```

---

## 6. حساب الأرباح

**الملف:** `app/Models/Order.php`

### المعادلات

```php
// 1. ربح AliExpress (المورد)
$aliexpressCost   = ($product->aliexpress_price + $order->freight_amount) * $order->quantity;
$aliexpressProfit = $aliexpressCost * ($product->supplier_profit_margin / 100);

// 2. ربح الإدارة (عمولة المنصة)
$adminProfitPerUnit   = AdminCategoryProfit::getProfitForCategory($product->category_id);
$adminCategoryProfit  = $adminProfitPerUnit * $order->quantity;

// 3. ربح البائع
$sellerSetting = SellerSubcategoryProfit::getProfitForSeller(
    $order->user_id,
    $product->subcategory_id
);

// الربح ممكن يكون:
if ($sellerSetting->type === 'percentage') {
    $sellerProfit = $order->total_price * ($sellerSetting->value / 100);
} elseif ($sellerSetting->type === 'fixed') {
    $sellerProfit = $sellerSetting->value * $order->quantity;
}

// حفظ في الطلب
$order->update([
    'aliexpress_profit'      => $aliexpressProfit,
    'admin_category_profit'  => $adminCategoryProfit,
    'seller_profit'          => $sellerProfit,
]);
```

### جدول ملخص

```
سعر البيع للعميل = 17$
                   ─────────────────────────────────
                   AliExpress Cost:  10$ (يروح للمورد)
                   Admin Profit:      2$ (عمولة المنصة)
                   Seller Profit:     5$ (يروح لمحفظة البائع)
```

---

## 7. نظام المحفظة

**الملف:** `app/Services/WalletService.php`

### العمليات الأساسية

```php
// إيداع (Deposit) — من بوابة دفع خارجية
public function deposit(User $user, float $amount, string $source): void
{
    $wallet = $this->getOrCreateWallet($user);

    $wallet->update(['balance' => $wallet->balance + $amount]);

    WalletTransaction::create([
        'wallet_id'       => $wallet->id,
        'type'            => 'credit',
        'transaction_type'=> 'deposit',
        'amount'          => $amount,
        'balance_before'  => $wallet->balance - $amount,
        'balance_after'   => $wallet->balance,
        'status'          => 'completed',
        'reference'       => $source,
    ]);
}

// خصم (Debit) — عند دفع طلب
public function debit(float $amount, string $description): void
{
    if ($this->balance < $amount) {
        throw new InsufficientBalanceException();
    }

    $balanceBefore = $this->balance;
    $this->decrement('balance', $amount);

    WalletTransaction::create([
        'type'            => 'debit',
        'transaction_type'=> 'order_payment',
        'amount'          => $amount,
        'balance_before'  => $balanceBefore,
        'balance_after'   => $this->balance,
    ]);
}

// إضافة عمولة للبائع بعد اكتمال الطلب
public function addCommission(User $seller, float $amount, Order $order): void
{
    $wallet = $this->getOrCreateWallet($seller);
    $wallet->increment('balance', $amount);

    WalletTransaction::create([
        'transaction_type' => 'commission',
        'amount'           => $amount,
        'reference'        => 'Order #' . $order->order_number,
    ]);
}
```

### دورة حياة الفلوس في طلب واحد

```
1. البائع يعمل طلب
   → wallet.debit(total)         [pending_balance يطلع، balance ينزل]

2. الطلب يتبعت لـ AliExpress بنجاح
   → status = 'placed'

3. الطلب يتشحن
   → status = 'shipped'

4. الطلب يتسلم (delivered)
   → wallet.addCommission(seller, seller_profit)   [ربح البائع يضاف]
   → admin_profit محفوظ في قاعدة البيانات للتقارير

5. البائع يطلب سحب
   → WithdrawalRequest.create(status: 'pending')

6. الإدارة توافق
   → WithdrawalRequest.update(status: 'completed')
   → wallet.debit(withdrawal_amount)
```

---

## 8. نظام الاشتراكات

**الملف:** `app/Http/Controllers/SubscriptionController.php`

### تدفق الاشتراك

```php
// 1. المستخدم يختار خطة وطريقة الدفع
POST /subscriptions/{plan_id}/subscribe
{
    payment_method: 'wallet' | 'ziina' | 'paymob'
}

// 2. حسب طريقة الدفع:

// الدفع من المحفظة
if ($method === 'wallet') {
    if ($wallet->balance < $plan->price) {
        return error('رصيد غير كافٍ');
    }
    $wallet->debit($plan->price, 'اشتراك في خطة: ' . $plan->name);
    $this->activateSubscription($user, $plan);
}

// الدفع عبر Ziina
if ($method === 'ziina') {
    $paymentUrl = ZiinaPaymentService::createPayment([
        'amount'      => $plan->price,
        'currency'    => 'AED',
        'description' => 'Subscription: ' . $plan->name,
        'callback'    => route('subscriptions.callback'),
    ]);
    return redirect($paymentUrl);
}
```

### تفعيل الاشتراك

```php
private function activateSubscription(User $user, Subscription $plan): void
{
    // إلغاء الاشتراك الحالي إن وجد
    $existing = $user->activeSubscription();
    $extraDays = 0;

    if ($existing) {
        // الأيام المتبقية تنتقل للاشتراك الجديد
        $extraDays = now()->diffInDays($existing->end_date);
        $existing->update(['status' => 'cancelled']);
    }

    // إنشاء اشتراك جديد
    UserSubscription::create([
        'user_id'        => $user->id,
        'subscription_id'=> $plan->id,
        'start_date'     => now(),
        'end_date'       => now()->addDays($plan->duration_days + $extraDays),
        'status'         => 'active',
        'amount_paid'    => $plan->price,
        'payment_method' => $method,
    ]);
}
```

---

## 9. تكامل AliExpress API

**الملف:** `app/Services/AliExpressService.php`

### بناء الـ Request

```php
// كل call للـ API بيبني بالنفس الطريقة دي:
private function buildApiCall(string $method, array $params): array
{
    $token  = AliExpressToken::getActiveToken();    // JWT Token
    $params = array_merge($params, [
        'app_key'    => config('aliexpress.api_key'),
        'timestamp'  => now()->format('Y-m-d H:i:s'),
        'sign_method'=> 'sha256',
        'access_token'=> $token->access_token,
    ]);

    // توليد الـ Signature
    $params['sign'] = $this->generateSign($params, config('aliexpress.api_secret'));

    return Http::post(config('aliexpress.api_url'), [
        'method' => $method,
        ...$params
    ])->json();
}
```

### المتاح من الـ API

```php
// بحث عن منتجات
$this->buildApiCall('aliexpress.ds.text.search', [
    'search_text'    => $keyword,
    'target_language'=> 'EN',
    'target_currency'=> 'USD',
    'page_no'        => $page,
    'page_size'      => 20,
]);

// تفاصيل منتج (مع SKUs)
$this->buildApiCall('aliexpress.ds.product.get', [
    'product_id'     => $productId,
    'target_language'=> 'EN',
    'target_currency'=> 'USD',
]);

// إنشاء طلب
$this->buildApiCall('aliexpress.ds.order.create', [
    'param_place_order_request4_open_api_d_t_o' => json_encode($orderPayload)
]);

// تتبع طلب
$this->buildApiCall('aliexpress.ds.order.tracking.info.get', [
    'order_id' => $aliexpressOrderId
]);
```

### إدارة الـ Token

```php
// الـ Token بينتهي صلاحيته وبيتجدد تلقائياً
class AliExpressToken extends Model
{
    public static function getActiveToken(): self
    {
        $token = self::latest()->first();

        // لو هينتهي في أقل من ساعة، جدد
        if ($token->expires_at->diffInMinutes(now()) < 60) {
            return AliExpressAuthService::refreshToken($token);
        }

        return $token;
    }
}
```

---

## 10. نماذج البيانات الأساسية

### User Model

```php
// Relationships
hasOne(Wallet::class)
hasMany(Product::class)
hasMany(Order::class)
hasMany(UserSubscription::class)

// Business Logic Methods
hasCompletedSetup(): bool       // profile + profit settings
isInTrialPeriod(): bool         // أقل من 24 ساعة من التسجيل
hasActiveSubscription(): bool   // اشتراك نشط
hasCompletedProfile(): bool     // البيانات الأساسية مكتملة
```

### Product Model

```php
// Attributes (computed)
getPriceInCurrencyAttribute()   // السعر محول للعملة المختارة
getFormattedPriceAttribute()    // السعر مع الرمز

// Relationships
belongsTo(User::class, 'seller_id')
belongsTo(Category::class)
hasMany(ProductImage::class)
hasMany(ProductVariation::class)
hasMany(Order::class)

// Scopes
scopeActive()                   // منتجات نشطة فقط
scopeAliexpress()               // منتجات AliExpress
scopeDistributor()              // منتجات الموزع
```

### Order Model

```php
// Status Constants
const STATUS_PENDING    = 'pending';
const STATUS_PLACED     = 'placed';
const STATUS_SHIPPED    = 'shipped';
const STATUS_DELIVERED  = 'delivered';
const STATUS_FAILED     = 'failed';

// Computed
getTotalProfitAttribute()       // aliexpress + admin + seller profits
getIsPlaceableAttribute()       // ممكن يتبعت لـ AliExpress؟

// Relationships
belongsTo(User::class)          // البائع
belongsTo(Product::class)
hasOne(Shipping::class)         // معلومات الشحن والتتبع
```

---

## نقاط مهمة في الكود

| النقطة | التفاصيل |
|--------|---------|
| **الـ Queue** | أي عملية تاخد وقت (AliExpress API) بتتنفذ في Background Job |
| **الـ Variants** | الـ SKU data محفوظة كـ JSON في `aliexpress_variants` |
| **الأرباح** | محسوبة ومحفوظة وقت إنشاء الطلب، مش on-the-fly |
| **الـ Currency** | محفوظة في Session، كل السعار بتتحول لها عند العرض |
| **الـ Token** | لو AliExpress Token انتهى، بيتجدد تلقائياً قبل أي Call |
| **الـ Webhook** | AliExpress بيبعت updates تلقائية على الـ Order Status |
| **الـ Fallback** | لو الـ SKU مش موجود، الكود بيجرب 3 طرق قبل ما يفشل |

---

*آخر تحديث: مارس 2026*
