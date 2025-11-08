# Seller Subcategory Profit System

## Overview

This system allows sellers to set custom profit margins for each subcategory. When a seller assigns a product from AliExpress, the system automatically applies the appropriate profit based on the product's subcategory.

## Features

- **Per-Subcategory Profit Settings**: Sellers can configure different profit margins for each subcategory
- **Flexible Profit Types**:
  - **Percentage**: Add a percentage of the base price (e.g., 20% on $100 = $20 profit)
  - **Fixed Amount**: Add a fixed amount regardless of price (e.g., $15 on any product)
- **Automatic Application**: Profits are automatically applied when products are assigned
- **Active/Inactive Status**: Enable or disable profit settings per subcategory
- **Bulk Management**: Update multiple subcategory profits at once

## Database Structure

### Table: `seller_subcategory_profits`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK to users table (seller) |
| category_id | bigint | FK to categories table (subcategory) |
| profit_type | enum | 'percentage' or 'fixed' |
| profit_value | decimal(10,2) | The profit value (% or amount) |
| currency | string(3) | Currency code (default: USD) |
| is_active | boolean | Whether this profit setting is active |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

**Unique Constraint**: (user_id, category_id) - One profit setting per seller per subcategory

## How It Works

### 1. Setting Up Profits

Sellers can manage their subcategory profits at `/seller/profits`:

```
1. Navigate to "Profit Settings" in seller dashboard
2. For each subcategory, set:
   - Profit Type (Percentage or Fixed Amount)
   - Profit Value (e.g., 20 for 20% or 15 for $15)
   - Active Status (toggle on/off)
3. Click "Save All" to save all settings
```

### 2. Automatic Application

When a seller assigns a product:

```php
// Example: Product with base price $100 in "Electronics > Smartphones" subcategory
// If seller has set 20% profit for "Smartphones":

Base Price (AliExpress): $100.00
Seller Profit (20%):      $20.00
Final Price:             $120.00
```

The system automatically:
1. Checks if the product has a category_id
2. Looks up the seller's profit setting for that subcategory
3. Calculates the profit amount
4. Sets `seller_amount` field with the profit
5. Updates `price` field with the final price

### 3. Product Assignment Flow

```
┌─────────────────────┐
│ Seller finds product│
│ on AliExpress       │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Selects category    │
│ (subcategory)       │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ System checks for   │
│ profit setting      │
└──────────┬──────────┘
           │
     ┌─────┴─────┐
     │           │
     ▼           ▼
  Found       Not Found
     │           │
     │           ▼
     │      Use base price
     │           │
     ▼           │
Calculate profit     │
  amount        │
     │           │
     └─────┬─────┘
           │
           ▼
┌─────────────────────┐
│ Create/Update       │
│ product with        │
│ final price         │
└─────────────────────┘
```

## API Endpoints

### Web Routes (Authenticated Sellers Only)

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/seller/profits` | Display profit settings page |
| POST | `/seller/profits` | Create/update single profit setting |
| POST | `/seller/profits/bulk-update` | Update multiple profit settings |
| POST | `/seller/profits/{profit}/toggle` | Toggle active status |
| DELETE | `/seller/profits/{profit}` | Delete profit setting |
| GET | `/seller/profits/api/subcategory/{categoryId}` | Get profit for subcategory (JSON) |

## Code Examples

### Setting a Profit in Controller

```php
use App\Models\SellerSubcategoryProfit;

$profit = SellerSubcategoryProfit::create([
    'user_id' => auth()->id(),
    'category_id' => $subcategoryId,
    'profit_type' => 'percentage',
    'profit_value' => 20.00,
    'currency' => 'USD',
    'is_active' => true,
]);
```

### Calculating Profit

```php
$profitSetting = $seller->getProfitForSubcategory($categoryId);

if ($profitSetting) {
    $basePrice = 100.00;

    // Get profit amount
    $profitAmount = $profitSetting->calculateProfit($basePrice);

    // Get final price
    $finalPrice = $profitSetting->calculateFinalPrice($basePrice);
}
```

### Checking Profit in Blade Template

```blade
@if($seller->getProfitForSubcategory($product->category_id))
    <span class="badge bg-success">Profit Applied</span>
@endif
```

## Model Relationships

### User Model

```php
// Get all profit settings for a seller
$seller->subcategoryProfits()

// Get profit for specific subcategory
$seller->getProfitForSubcategory($categoryId)
```

### Category Model

```php
// Get all seller profit settings for this category
$category->sellerProfits()

// Check if it's a subcategory
$category->isSubcategory()
```

### SellerSubcategoryProfit Model

```php
// Calculate profit amount
$profit->calculateProfit($basePrice)

// Calculate final price
$profit->calculateFinalPrice($basePrice)

// Scopes
SellerSubcategoryProfit::active()->get()
SellerSubcategoryProfit::forSeller($sellerId)->get()
SellerSubcategoryProfit::forCategory($categoryId)->get()
```

## Migration Commands

```bash
# Run the migration
php artisan migrate

# Rollback the migration
php artisan migrate:rollback
```

## Testing Scenarios

### Test Case 1: Percentage Profit

```
Subcategory: Smartphones
Profit Type: Percentage
Profit Value: 25%
Base Price: $200.00

Expected Results:
- Profit Amount: $50.00 (200 * 0.25)
- Final Price: $250.00
```

### Test Case 2: Fixed Profit

```
Subcategory: Laptops
Profit Type: Fixed
Profit Value: $75.00
Base Price: $500.00

Expected Results:
- Profit Amount: $75.00
- Final Price: $575.00
```

### Test Case 3: Inactive Profit

```
Subcategory: Tablets
Profit Type: Percentage
Profit Value: 15%
Status: Inactive
Base Price: $300.00

Expected Results:
- Profit Amount: $0.00 (inactive)
- Final Price: $300.00
```

## UI Features

The profit management page includes:

1. **Parent Category Grouping**: Subcategories are grouped under their parent categories
2. **Inline Editing**: Edit all profit settings on one page
3. **Profit Preview**: Preview how profit calculations work with custom base prices
4. **Bulk Save**: Save all changes at once
5. **Toggle Active/Inactive**: Quickly enable/disable profits
6. **Delete Option**: Remove profit settings
7. **Real-time Unit Display**: Shows % or $ based on profit type
8. **Bilingual Support**: Displays Arabic names when locale is 'ar'

## Security Considerations

1. **Authorization**: Only sellers can access profit management
2. **Ownership Validation**: Sellers can only modify their own profit settings
3. **Subcategory Validation**: System ensures only subcategories (not parent categories) can have profits
4. **Input Validation**: All profit values must be >= 0

## Best Practices

1. **Set Default Profits**: Configure default profit margins for your main subcategories
2. **Review Regularly**: Update profit margins based on market conditions
3. **Use Percentage for Variable Products**: For products with varying prices, percentage works better
4. **Use Fixed for Consistent Margins**: For predictable margins, use fixed amounts
5. **Test Before Publishing**: Use the preview feature to verify calculations

## Troubleshooting

### Profit Not Applied

**Issue**: Product assigned but no profit added

**Solutions**:
1. Check if category_id was provided during assignment
2. Verify profit setting is active
3. Ensure profit setting exists for that subcategory
4. Check logs for calculation errors

### Wrong Profit Amount

**Issue**: Calculated profit doesn't match expected

**Solutions**:
1. Verify profit_type (percentage vs fixed)
2. Check profit_value is correct
3. Ensure base price is being passed correctly
4. Use preview feature to test calculations

## Future Enhancements

Potential improvements:
- Import/Export profit settings
- Copy profit settings between sellers
- Profit templates/presets
- Historical profit tracking
- Profit analytics dashboard
- Automatic profit adjustments based on competition
- Minimum/Maximum profit limits
- Currency-specific profit settings
