# Product Module Setup Guide

## Issue: "Record not found!" / Unauthorized Error

If you're seeing a permission error when trying to access the products page, follow these steps:

## 1. Add Navigation Menu Item

Update your navigation menu configuration to include the products link. This is typically in your backend API response for `/menus`:

```json
{
  "menu": [
    {
      "title": "Home",
      "to": "/",
      "icon": "tabler-home"
    },
    {
      "title": "Products",
      "to": "/products",
      "icon": "tabler-package",
      "action": "products.view"
    },
    {
      "title": "Users",
      "to": "/users",
      "icon": "tabler-users",
      "action": "users.view"
    }
  ]
}
```

## 2. Backend: Create Permissions

Add these permissions to your database (permissions table):

```sql
INSERT INTO permissions (name, display_name, description) VALUES
('products.view', 'View Products', 'Can view products listing'),
('products.create', 'Create Products', 'Can create new products'),
('products.edit', 'Edit Products', 'Can edit existing products'),
('products.delete', 'Delete Products', 'Can delete products');
```

## 3. Backend: Assign Permissions to Role

Assign the permissions to the admin role (or your current role):

```sql
-- Get your admin role ID first
SELECT id FROM roles WHERE name = 'admin';

-- Then assign permissions (replace 1 with your role ID)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions WHERE name LIKE 'products.%';
```

## 4. Backend: Create ProductController

Create the controller in Laravel:

```bash
php artisan make:controller Api/ProductController
```

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getListingConfig()
    {
        // Check permission
        if (!auth()->user()->can('products.view')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'headers' => [
                    'fields' => [
                        [
                            'title' => 'Product Name',
                            'key' => 'name',
                            'db_field' => 'name',
                            'orderByField' => 'name',
                            'search_field' => 'Text',
                            'column_sort' => true,
                            'sortable' => true,
                            'default' => true,
                            'includeAllFilter' => false
                        ],
                        [
                            'title' => 'SKU',
                            'key' => 'sku',
                            'db_field' => 'sku',
                            'orderByField' => 'sku',
                            'search_field' => 'Text',
                            'column_sort' => true,
                            'sortable' => true,
                            'default' => true,
                            'includeAllFilter' => false
                        ],
                        [
                            'title' => 'Price',
                            'key' => 'price',
                            'db_field' => 'price',
                            'orderByField' => 'price',
                            'search_field' => 'Number',
                            'column_sort' => true,
                            'sortable' => true,
                            'default' => true,
                            'includeAllFilter' => false
                        ],
                        [
                            'title' => 'Quantity',
                            'key' => 'quantity',
                            'db_field' => 'quantity',
                            'orderByField' => 'quantity',
                            'search_field' => 'Number',
                            'column_sort' => true,
                            'sortable' => true,
                            'default' => true,
                            'includeAllFilter' => false
                        ],
                        [
                            'title' => 'Status',
                            'key' => 'status',
                            'db_field' => 'status',
                            'orderByField' => 'status',
                            'search_field' => 'Text',
                            'column_sort' => true,
                            'sortable' => true,
                            'default' => true,
                            'includeAllFilter' => true
                        ],
                    ],
                    'default_sort' => [
                        'key' => 'name',
                        'order' => 'asc'
                    ]
                ],
                'bulkActions' => ['delete', 'enable', 'disable'],
                'columnSearchable' => [
                    'status' => [
                        ['value' => 'active', 'title' => 'Active'],
                        ['value' => 'inactive', 'title' => 'Inactive'],
                        ['value' => 'out_of_stock', 'title' => 'Out of Stock']
                    ]
                ],
                'showCheckbox' => true,
                'selectedColumns' => ['name', 'sku', 'price', 'quantity', 'status'],
                'advanceFilters' => [],
                'showExportButton' => true,
                'exportColumns' => [],
                'externalFilters' => []
            ]
        ]);
    }

    public function list(Request $request)
    {
        // Check permission
        if (!auth()->user()->can('products.view')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // For now, return empty data
        return response()->json([
            'status' => true,
            'data' => [],
            'pagination' => [
                'total' => 0
            ]
        ]);
    }
}
```

## 5. Backend: Add Routes

Add routes to `routes/api.php`:

```php
use App\Http\Controllers\Api\ProductController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Product routes
    Route::get('/products/listing-config', [ProductController::class, 'getListingConfig']);
    Route::post('/products/list', [ProductController::class, 'list']);
    Route::post('/product', [ProductController::class, 'store']);
    Route::post('/product/update', [ProductController::class, 'update']);
    Route::delete('/products/delete', [ProductController::class, 'bulkDelete']);
    Route::post('/products/activate', [ProductController::class, 'bulkActivate']);
    Route::post('/products/deactivate', [ProductController::class, 'bulkDeactivate']);
    Route::post('/products/export', [ProductController::class, 'export']);
    Route::get('/product/fetch', [ProductController::class, 'fetch']);
    Route::get('/product/show', [ProductController::class, 'show']);
    Route::get('/product/formInitialData', [ProductController::class, 'formInitialData']);
});
```

## 6. Clear Cache and Refresh

```bash
# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Restart development server if needed
```

## 7. Frontend: Check User Permissions

Make sure your user has the `products.view` permission. You can check in browser console:

```javascript
console.log($nuxt.$auth.user.permissions)
```

## 8. Temporary Fix for Testing

If you want to test without permissions temporarily, you can comment out the `checkPermission` in the page meta:

```typescript
definePageMeta({
  navActiveLink: 'products',
  // checkPermission: 'products.view', // Temporarily commented
})
```

**⚠️ Remember to uncomment this after testing!**

## Quick Checklist

- [ ] Permissions created in database
- [ ] Permissions assigned to your role
- [ ] Routes added to `routes/api.php`
- [ ] Controller created with `getListingConfig` method
- [ ] Navigation menu updated to include products link
- [ ] User has the required permissions
- [ ] Cache cleared
- [ ] Browser refreshed / logged out and back in

## Testing the Setup

1. **Test the API endpoint directly:**
   ```bash
   curl -X GET "http://lab-local.anba.test:9000/api/products/listing-config" \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

2. **Check browser Network tab:**
   - Open DevTools → Network
   - Navigate to `/products`
   - Look for the `/api/products/listing-config` request
   - Check if it returns 200 OK or 403 Forbidden

3. **Check browser Console:**
   - Look for any JavaScript errors
   - Check if the API response is being received

## Common Issues

### Issue: 403 Forbidden
**Solution:** User doesn't have `products.view` permission. Assign the permission to the user's role.

### Issue: 404 Not Found
**Solution:** Route not defined in Laravel. Add the route to `routes/api.php`.

### Issue: Menu item not showing
**Solution:** Update the menu API response to include the products link with proper `action` permission.

### Issue: Page redirects to /unauthorized
**Solution:** The `checkPermission` middleware is blocking access. Ensure the user has the permission or temporarily comment it out for testing.
