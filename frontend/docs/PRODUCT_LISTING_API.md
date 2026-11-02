# Product Listing Configuration - Backend API Documentation

## Overview
This document describes the backend API structure required for the Products listing configuration endpoint.

## Endpoint: `/products/listing-config`

### Method: GET

### Response Structure

The endpoint should return a JSON response following this structure:

```json
{
  "status": true,
  "message": "Configuration fetched successfully",
  "data": {
    "headers": {
      "fields": [
        {
          "title": "Product Name",
          "key": "name",
          "db_field": "name",
          "orderByField": "name",
          "search_field": "Text",
          "column_sort": true,
          "sortable": true,
          "default": true,
          "includeAllFilter": false
        },
        {
          "title": "SKU",
          "key": "sku",
          "db_field": "sku",
          "orderByField": "sku",
          "search_field": "Text",
          "column_sort": true,
          "sortable": true,
          "default": true,
          "includeAllFilter": false
        },
        {
          "title": "Category",
          "key": "category",
          "db_field": "category",
          "orderByField": "category",
          "search_field": "Text",
          "column_sort": true,
          "sortable": true,
          "default": true,
          "includeAllFilter": true
        },
        {
          "title": "Price",
          "key": "price",
          "db_field": "price",
          "orderByField": "price",
          "search_field": "Number",
          "column_sort": true,
          "sortable": true,
          "default": true,
          "includeAllFilter": false
        },
        {
          "title": "Quantity",
          "key": "quantity",
          "db_field": "quantity",
          "orderByField": "quantity",
          "search_field": "Number",
          "column_sort": true,
          "sortable": true,
          "default": true,
          "includeAllFilter": false
        },
        {
          "title": "Unit",
          "key": "unit",
          "db_field": "unit",
          "orderByField": "unit",
          "search_field": "Text",
          "column_sort": false,
          "sortable": false,
          "default": true,
          "includeAllFilter": true
        },
        {
          "title": "Status",
          "key": "status",
          "db_field": "status",
          "orderByField": "status",
          "search_field": "Text",
          "column_sort": true,
          "sortable": true,
          "default": true,
          "includeAllFilter": true
        },
        {
          "title": "Barcode",
          "key": "barcode",
          "db_field": "barcode",
          "orderByField": "barcode",
          "search_field": "Text",
          "column_sort": false,
          "sortable": false,
          "default": false,
          "includeAllFilter": false
        },
        {
          "title": "Cost Price",
          "key": "cost_price",
          "db_field": "cost_price",
          "orderByField": "cost_price",
          "search_field": "Number",
          "column_sort": true,
          "sortable": true,
          "default": false,
          "includeAllFilter": false
        },
        {
          "title": "Tax Rate",
          "key": "tax_rate",
          "db_field": "tax_rate",
          "orderByField": "tax_rate",
          "search_field": "Number",
          "column_sort": true,
          "sortable": true,
          "default": false,
          "includeAllFilter": false
        },
        {
          "title": "Created At",
          "key": "created_at",
          "db_field": "created_at",
          "orderByField": "created_at",
          "search_field": "Date",
          "column_sort": true,
          "sortable": true,
          "default": false,
          "includeAllFilter": false
        }
      ],
      "default_sort": {
        "key": "name",
        "order": "asc"
      }
    },
    "bulkActions": [
      "delete",
      "enable",
      "disable"
    ],
    "columnSearchable": {
      "category": [],
      "unit": [],
      "status": [
        {
          "value": "active",
          "title": "Active"
        },
        {
          "value": "inactive",
          "title": "Inactive"
        },
        {
          "value": "out_of_stock",
          "title": "Out of Stock"
        }
      ]
    },
    "showCheckbox": true,
    "selectedColumns": [
      "name",
      "sku",
      "category",
      "price",
      "quantity",
      "unit",
      "status"
    ],
    "advanceFilters": {},
    "showExportButton": true,
    "exportColumns": [
      {
        "title": "Product Name",
        "key": "name",
        "db_field": "name"
      },
      {
        "title": "SKU",
        "key": "sku",
        "db_field": "sku"
      },
      {
        "title": "Category",
        "key": "category",
        "db_field": "category"
      },
      {
        "title": "Price",
        "key": "price",
        "db_field": "price"
      },
      {
        "title": "Cost Price",
        "key": "cost_price",
        "db_field": "cost_price"
      },
      {
        "title": "Quantity",
        "key": "quantity",
        "db_field": "quantity"
      },
      {
        "title": "Unit",
        "key": "unit",
        "db_field": "unit"
      },
      {
        "title": "Tax Rate",
        "key": "tax_rate",
        "db_field": "tax_rate"
      },
      {
        "title": "Status",
        "key": "status",
        "db_field": "status"
      },
      {
        "title": "Barcode",
        "key": "barcode",
        "db_field": "barcode"
      }
    ],
    "externalFilters": {}
  }
}
```

## Field Definitions

### DataTableFieldConfig
Each field in the `headers.fields` array contains:

- **title**: Display name of the column
- **key**: Unique identifier for the column
- **db_field**: Database field name for queries
- **orderByField**: Field name used for sorting
- **search_field**: Type of search input ("Text", "Number", "Date")
- **column_sort**: Whether sorting is enabled for this column
- **sortable**: Whether the column can be sorted (for VDataTableServer)
- **default**: Whether the column is visible by default
- **includeAllFilter**: Whether to include this field in the "All" filter dropdown

### Configuration Properties

- **headers**: Column definitions and default sorting
- **bulkActions**: Array of available bulk actions ("delete", "enable", "disable")
- **columnSearchable**: Defines filterable columns with their options
- **showCheckbox**: Enable row selection checkboxes
- **selectedColumns**: Default visible columns
- **advanceFilters**: Advanced filter configurations (empty object if none)
- **showExportButton**: Enable export functionality
- **exportColumns**: Columns to include in exports
- **externalFilters**: Additional filter configurations

## Additional Endpoints Required

### 1. `/products/list` (POST)
Fetches paginated product data with filters and sorting.

**Request Body:**
```json
{
  "page": 1,
  "itemsPerPage": 10,
  "sortBy": [{ "key": "name", "order": "asc" }],
  "search": "search term",
  "filters": {
    "status": ["active"],
    "category": ["Electronics"]
  }
}
```

**Response:**
```json
{
  "status": true,
  "data": [
    {
      "uuid": "product-uuid-1",
      "name": "Product Name",
      "sku": "SKU-001",
      "category": "Electronics",
      "price": 1500.00,
      "cost_price": 1000.00,
      "quantity": 50,
      "unit": "pcs",
      "tax_rate": 18,
      "status": "active",
      "barcode": "1234567890",
      "created_at": "2024-01-01T00:00:00Z",
      "actions": {
        "writePermission": true,
        "viewPermission": true,
        "deletePermission": true,
        "isLocked": false
      }
    }
  ],
  "pagination": {
    "total": 100
  }
}
```

### 2. `/product` (POST)
Creates a new product.

### 3. `/product/update` (POST)
Updates an existing product.

### 4. `/products/delete` (DELETE)
Deletes selected products (soft delete recommended).

### 5. `/products/activate` (POST)
Activates selected products.

### 6. `/products/deactivate` (POST)
Deactivates selected products.

### 7. `/products/export` (POST)
Exports products based on filters.

### 8. `/product/fetch?uuid={uuid}` (GET)
Fetches a single product by UUID for editing.

### 9. `/product/show?uuid={uuid}` (GET)
Fetches a single product by UUID for viewing.

### 10. `/product/formInitialData` (GET)
Returns initial data for the product form (categories, units, etc.).

## Database Schema Suggestion

```sql
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) UNIQUE,
    description TEXT,
    category VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL,
    cost_price DECIMAL(10, 2),
    quantity INT DEFAULT 0,
    unit VARCHAR(50),
    tax_rate DECIMAL(5, 2),
    discount DECIMAL(10, 2),
    image VARCHAR(255),
    barcode VARCHAR(100),
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_sku (sku),
    INDEX idx_status (status),
    INDEX idx_category (category)
);
```

## Notes

1. All endpoints should implement proper authentication and authorization
2. Implement permission checks: `products.view`, `products.create`, `products.edit`, `products.delete`
3. Use UUID for product identification in URLs
4. Implement proper error handling and validation
5. Add rate limiting for API endpoints
6. Log all bulk actions in audit trail
7. Implement proper image upload handling for product images
