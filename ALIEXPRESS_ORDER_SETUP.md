# AliExpress Order Setup Guide

This guide explains what fields are required for products to successfully place orders on AliExpress.

## Critical Product Database Fields

### Required Fields for Orders

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `aliexpress_id` | VARCHAR | ✅ Yes | The AliExpress product ID (e.g., "1005005017254589") |
| `aliexpress_data` | JSON | ✅ **CRITICAL** | Full product details including SKU variants from AliExpress API |
| `name` | VARCHAR | ✅ Yes | Product name |
| `price` | DECIMAL | ✅ Yes | Your selling price |
| `currency` | VARCHAR | ✅ Yes | Currency code (AED, USD, etc.) |
| `aliexpress_variants` | JSON | ⚠️ Optional | Simplified SKU data (legacy) |
| `aliexpress_url` | VARCHAR | ℹ️ Recommended | Link to AliExpress product |
| `aliexpress_price` | DECIMAL | ℹ️ Recommended | Original AliExpress price |

### The Most Important Field: `aliexpress_data`

The `aliexpress_data` field **MUST** contain the complete response from the AliExpress `aliexpress.ds.product.get` API. This includes:

```json
{
  "ae_item_sku_info_dtos": {
    "ae_item_sku_info_d_t_o": [
      {
        "sku_attr": "14:496#Green 116Plus",
        "sku_id": "12000031354808376",
        "sku_available_stock": 98621,
        "offer_sale_price": "3.55",
        "currency_code": "USD",
        "ae_sku_property_dtos": {
          "ae_sku_property_d_t_o": [
            {
              "sku_property_name": "Color",
              "sku_property_value": "PURPLE",
              "sku_image": "https://..."
            }
          ]
        }
      }
    ]
  },
  "ae_item_base_info_dto": {
    "subject": "Product Name",
    "product_id": "1005005017254589"
  }
}
```

## Why is `aliexpress_data` Critical?

When placing an order on AliExpress, you **MUST** specify which SKU (variant) to order. Products often have multiple options:
- Different colors
- Different sizes
- Different models/versions
- Different shipping methods

Without the SKU data, the order will fail with error: `SKU_NOT_EXIST`

## How to Fix Missing SKU Data

### Option 1: Use the Sync Command (Recommended)

We've created a command to automatically fetch and store SKU data for your products:

```bash
# Sync all products missing SKU data
php artisan products:sync-sku-data

# Sync a specific product by ID
php artisan products:sync-sku-data 5
```

This command will:
1. Find all products with `aliexpress_id` but missing `aliexpress_data`
2. Fetch complete product details from AliExpress API
3. Store the data in `aliexpress_data` field
4. Extract and store SKU information in `aliexpress_variants` field
5. Update `last_synced_at` timestamp

### Option 2: Manual Database Update

If you have the product data already, you can manually update via SQL:

```sql
UPDATE products
SET aliexpress_data = '{"ae_item_sku_info_dtos": {...}}',
    aliexpress_variants = '{"ae_item_sku_info_d_t_o": [...]}',
    last_synced_at = NOW()
WHERE aliexpress_id = '1005005017254589';
```

### Option 3: Re-import the Product

When importing a new product from AliExpress, the system automatically:
1. Fetches complete product details including SKU data
2. Stores it in `aliexpress_data` field
3. Makes the product ready for ordering

## How Order Placement Works

When you click "Place on AliExpress" for an order:

1. **Check for SKU data** in `aliexpress_data` field
2. **If missing**, automatically fetch from AliExpress API
3. **Extract first available SKU** with stock
4. **Send order** to AliExpress with:
   - Product ID
   - SKU attribute (e.g., "14:496#Green 116Plus")
   - Quantity
   - Shipping address
   - Customer details

## Common Errors and Solutions

### Error: `SKU_NOT_EXIST`

**Cause**: Product doesn't have SKU data or the SKU attribute is invalid.

**Solution**:
```bash
# Sync the product to fetch latest SKU data
php artisan products:sync-sku-data <product-id>
```

### Error: `PRODUCT_NOT_EXIST`

**Cause**: The AliExpress product ID is invalid or the product has been removed.

**Solution**: Check the product still exists on AliExpress. Update `aliexpress_id` if needed.

### Error: `INSUFFICIENT_INVENTORY`

**Cause**: The selected SKU is out of stock.

**Solution**: Sync the product to get updated stock information, or manually select a different SKU.

## Checking if a Product is Ready for Orders

Run this SQL query to check if a product has the required data:

```sql
SELECT
    id,
    name,
    aliexpress_id,
    CASE
        WHEN aliexpress_id IS NULL THEN '❌ No AliExpress ID'
        WHEN aliexpress_data IS NULL THEN '⚠️ Missing SKU data - Run sync command'
        WHEN JSON_EXTRACT(aliexpress_data, '$.ae_item_sku_info_dtos') IS NULL THEN '⚠️ Invalid SKU data format'
        ELSE '✅ Ready for orders'
    END as order_readiness
FROM products
WHERE id = 5;
```

## Best Practices

1. **Always sync products** after importing from AliExpress
2. **Re-sync periodically** to get updated stock and pricing
3. **Check SKU data** before placing bulk orders
4. **Store complete API response** in `aliexpress_data` - don't truncate it
5. **Monitor sync logs** at `storage/logs/laravel.log` for errors

## Quick Checklist for New Products

- [ ] Product has valid `aliexpress_id`
- [ ] `aliexpress_data` field contains complete API response
- [ ] `aliexpress_data` includes `ae_item_sku_info_dtos` section
- [ ] At least one SKU has `sku_available_stock > 0`
- [ ] Product is active (`is_active = 1`)
- [ ] Pricing is configured correctly

## Support

If you encounter issues:
1. Check `storage/logs/laravel.log` for detailed error messages
2. Verify AliExpress API credentials in `.env`
3. Test API connection: `php artisan aliexpress:test-connection`
4. Run sync command with verbose output: `php artisan products:sync-sku-data -v`

---

**Last Updated**: October 31, 2025
**Related Files**:
- `app/Services/AliExpressService.php` - Main API service
- `app/Http/Controllers/OrderController.php` - Order placement logic
- `app/Console/Commands/SyncProductSkuData.php` - SKU sync command
