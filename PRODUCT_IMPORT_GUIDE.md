# Product Import Guide - AliExpress Orders Ready

This guide explains how to import products that are ready for placing orders on AliExpress.

## ✅ What's Now Automated

When you import a product from AliExpress, the system now **automatically**:

1. ✅ Fetches complete product details including SKU variants
2. ✅ Stores full API response in `aliexpress_data` field
3. ✅ Extracts and stores SKU information in `aliexpress_variants` field
4. ✅ Validates that SKU data is present
5. ✅ Extracts product images from multiple possible locations
6. ✅ Sets `last_synced_at` timestamp
7. ✅ Shows you if the product is order-ready with SKU count

## 🎯 How to Import a Product

### Method 1: Via Import Page (Recommended)

1. Go to **Products → Import from AliExpress**
2. Enter the AliExpress Product ID (e.g., `1005005017254589`)
3. Select optional category and profit margin
4. Click **Import Product**

The system will:
- Fetch complete product data
- Show you: "Product imported successfully. ✅ Ready for ordering (9 variants available)."
- Product is now ready for orders!

### Method 2: Via API

```bash
POST /products/import-aliexpress
{
  "aliexpress_id": "1005005017254589",
  "category_id": 1,
  "profit_margin": 30,
  "country": "US",
  "currency": "USD"
}
```

Response will include:
```json
{
  "success": true,
  "message": "Product imported successfully. ✅ Ready for ordering (9 variants available).",
  "product": {...},
  "order_ready": true,
  "sku_count": 9
}
```

## 📊 Product Data Fields Set During Import

### Critical Fields (Required for Orders)
| Field | Description | Example |
|-------|-------------|---------|
| `aliexpress_id` | Product ID from AliExpress | "1005005017254589" |
| `aliexpress_data` | **Complete API response with SKU variants** | {ae_item_sku_info_dtos: {...}} |
| `aliexpress_variants` | Extracted SKU data for quick access | {ae_item_sku_info_d_t_o: [...]} |
| `name` | Product name | "Smart Watch Y68" |
| `price` | Your selling price (with profit margin) | 15.99 |
| `currency` | Currency code | "USD" |

### Additional Fields
| Field | Description |
|-------|-------------|
| `images` | Product images array |
| `description` | Full product description |
| `aliexpress_url` | Link to AliExpress product |
| `aliexpress_price` | Original AliExpress price |
| `supplier_profit_margin` | Your profit margin % |
| `last_synced_at` | When data was last fetched |
| `is_active` | Set to `false` by default (review before activating) |

## 🔍 Verifying Product is Order-Ready

### Check via Database
```sql
SELECT
    id,
    name,
    aliexpress_id,
    JSON_EXTRACT(aliexpress_data, '$.ae_item_sku_info_dtos.ae_item_sku_info_d_t_o[0].sku_attr') as first_sku,
    last_synced_at,
    CASE
        WHEN aliexpress_data IS NULL THEN '❌ Missing data'
        WHEN JSON_EXTRACT(aliexpress_data, '$.ae_item_sku_info_dtos') IS NULL THEN '⚠️ No SKU data'
        ELSE '✅ Order ready'
    END as status
FROM products
WHERE aliexpress_id = '1005005017254589';
```

### Check via Logs
Look for this in `storage/logs/laravel.log`:
```
[DATE] local.INFO: Product imported successfully
{
    "product_id": 7,
    "aliexpress_id": "1005005017254589",
    "has_sku_data": true,
    "sku_count": 9,
    "image_count": 6
}
```

## 🛠️ What If SKU Data is Missing?

If you imported a product **before** this update, run the sync command:

```bash
# Sync all products missing SKU data
php artisan products:sync-sku-data

# Sync specific product
php artisan products:sync-sku-data 5
```

## ✨ Example: Complete Import Flow

1. **Import Product**
   ```
   Product ID: 1005005017254589
   Profit Margin: 30%
   Currency: USD
   ```

2. **System Fetches Data**
   - Makes API call to `aliexpress.ds.product.get`
   - Retrieves complete product info including:
     - Product name, description, images
     - Price information
     - **9 SKU variants** with stock info
     - Shipping details

3. **System Stores Data**
   ```
   aliexpress_data: {
     "ae_item_sku_info_dtos": {
       "ae_item_sku_info_d_t_o": [
         {
           "sku_attr": "14:496#Green 116Plus",
           "sku_available_stock": 98621,
           "offer_sale_price": "3.55"
         },
         ...8 more variants
       ]
     }
   }
   ```

4. **Product is Now Order-Ready**
   - Can create orders immediately
   - System will auto-select first available SKU
   - Orders will be placed successfully on AliExpress

## 📝 Quick Checklist

Before placing an order, ensure:

- [x] Product imported via the import page (or after this update)
- [x] `aliexpress_data` field contains JSON data
- [x] Import success message shows "✅ Ready for ordering"
- [x] Product is active (`is_active = 1`) if you want it visible
- [x] Pricing is reviewed and correct

## 🚨 Common Issues & Solutions

### Issue: "Product may not be ready for ordering"
**Solution**: The product was imported but AliExpress didn't return SKU data. Try:
1. Re-import the product
2. Check if product exists on AliExpress
3. Run sync command: `php artisan products:sync-sku-data <id>`

### Issue: Old products missing SKU data
**Solution**: Run bulk sync:
```bash
php artisan products:sync-sku-data
```

### Issue: Order fails with SKU_NOT_EXIST
**Solution**: Product needs fresh SKU data:
```bash
php artisan products:sync-sku-data <product-id>
```

## 📚 Related Documentation

- [ALIEXPRESS_ORDER_SETUP.md](./ALIEXPRESS_ORDER_SETUP.md) - Complete order setup guide
- [SyncProductSkuData.php](./app/Console/Commands/SyncProductSkuData.php) - SKU sync command
- [ProductController.php](./app/Http/Controllers/ProductController.php) - Import logic

---

**Last Updated**: October 31, 2025
**Version**: 2.0 - Auto SKU Import
