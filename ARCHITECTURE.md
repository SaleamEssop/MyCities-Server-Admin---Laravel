# MyCities System Architecture

## Overview

MyCities is a utility billing management system consisting of:
1. **Laravel Admin Panel** (this repo) - For administrators to manage billing templates
2. **Vue/Quasar Mobile App** (SaleamEssop/MyCities-Vue-Quasar) - For end users to track usage and costs

---

## Data Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│  ADMIN PANEL (Laravel - this repo)                                  │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Region Cost Template (create/edit)                          │   │
│  │  Path: resources/views/admin/region_cost/                    │   │
│  │  Controller: app/Http/Controllers/RegionsCostController.php  │   │
│  │                                                               │   │
│  │  Fields:                                                      │   │
│  │  - Water In costs (min, max, cost per tier)                  │   │
│  │  - Water In related costs (title, percentage, cost)          │   │
│  │  - Water Out costs (min, max, percentage, cost per tier)     │   │
│  │  - Water Out related costs (title, percentage, cost)         │   │
│  │  - Electricity costs (min, max, cost per tier)               │   │
│  │  - Electricity related costs (title, percentage, cost)       │   │
│  │  - Additional costs (name, cost, exempt_vat)                 │   │
│  │  - VAT percentage, rates, rebates                            │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                              │                                      │
│                              ▼                                      │
│              Saved to: regions_account_type_cost table              │
│              Model: app/Models/RegionsAccountTypeCost.php           │
└─────────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│  API LAYER (Laravel)                                                │
│  Controller: app/Http/Controllers/ApiController.php                 │
│  Service: app/Http/Services/MeterService.php                        │
│                                                                     │
│  Key Endpoints (routes/api.php):                                    │
│  - GET /v1/default-cost/get → getDefaultCosts()                    │
│  - GET /v1/regions/getEastimateCost → getEastimateCost()           │
│  - GET /v1/regions/getAdditionalCost → getAdditionalCost()         │
│  - GET /v1/regions/getBillday → getBillday()                       │
└─────────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│  MOBILE APP (Vue/Quasar - SaleamEssop/MyCities-Vue-Quasar)         │
│                                                                     │
│  API Client: src/boot/axios.js                                      │
│  Data Store: src/stores/defaultCost.js                              │
│  Components:                                                        │
│  - src/components/AccountComponent.vue                              │
│  - src/components/AccountCost.vue                                   │
│  - src/components/MeterCost.vue                                     │
│                                                                     │
│  Features:                                                          │
│  - Display current meter readings                                   │
│  - Calculate costs based on usage tiers from template               │
│  - Project future bills based on usage patterns                     │
│  - Allow users to toggle/modify certain costs                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Database Structure

### Main Table: `regions_account_type_cost`

Model: `app/Models/RegionsAccountTypeCost.php`

| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| template_name | string | Name of the billing template |
| region_id | bigint | FK to regions table |
| account_type_id | bigint | FK to account_type table |
| start_date | date | Template validity start |
| end_date | date | Template validity end |
| is_water | int | Water meter enabled (0/1) |
| is_electricity | int | Electricity meter enabled (0/1) |
| water_used | int | Default water usage for calculations |
| electricity_used | int | Default electricity usage for calculations |
| water_in | JSON | Water intake cost tiers |
| water_out | JSON | Sewage/water out cost tiers |
| electricity | JSON | Electricity cost tiers |
| waterin_additional | JSON | Water in related costs |
| waterout_additional | JSON | Water out related costs |
| electricity_additional | JSON | Electricity related costs |
| additional | JSON | Additional fixed costs |
| vat_percentage | decimal | VAT percentage |
| vat_rate | decimal | VAT rate |
| ratable_value | decimal | Property ratable value |
| rates_rebate | decimal | Rates rebate amount |
| billing_day | int | Day of month for billing |
| read_day | int | Day of month for meter reading |

### JSON Field Structures

#### water_in / water_out / electricity
```json
[
  {"min": "0", "max": "6", "cost": "26.87", "percentage": "100"},
  {"min": "7", "max": "12", "cost": "32.41", "percentage": "100"}
]
```

#### waterin_additional / waterout_additional / electricity_additional
```json
[
  {"title": "Infrastructure Surcharge", "percentage": "100", "cost": "1.70"},
  {"title": "Sanitation", "percentage": "95", "cost": "1.48"}
]
```

#### additional
```json
[
  {"name": "Water Loss Levy", "cost": "17.12", "exempt_vat": "no"},
  {"name": "Refuse Removal", "cost": "250.00", "exempt_vat": "no"}
]
```

---

## Cost Calculation Flow

### MeterService.php handles all calculations:

1. **Get Usage Data** - From meter readings
2. **Apply Tiered Pricing** - Using `getTotalCostByBrackets()`
3. **Add Related Costs** - Percentage-based additional costs
4. **Add Fixed Costs** - From `additional` field
5. **Calculate VAT** - Based on `vat_percentage`
6. **Project Future Bills** - Based on usage patterns

```php
// Example: Water cost calculation
$waterInBrackets = json_decode($regionAccountTypeCost->water_in, true);
$waterInTotal = $this->getTotalCostByBrackets($waterInBrackets, $usageInfo['total_usage']);
```

---

## Key Files Reference

### Admin Panel (Laravel)
- `routes/web.php` - Admin routes for region_cost CRUD
- `app/Http/Controllers/RegionsCostController.php` - Template CRUD controller
- `resources/views/admin/region_cost/create.blade.php` - Create template form
- `resources/views/admin/region_cost/edit.blade.php` - Edit template form
- `resources/views/admin/region_cost/index.blade.php` - List templates

### API Layer
- `routes/api.php` - All API endpoints
- `app/Http/Controllers/ApiController.php` - API controller with cost methods
- `app/Http/Services/MeterService.php` - Cost calculation service

### Models
- `app/Models/RegionsAccountTypeCost.php` - Main template model
- `app/Models/FixedCost.php` - User-specific fixed costs
- `app/Models/Account.php` - User accounts

---

## Cost Types

### 1. Default Costs (From Template)
- Set by admin in region cost template
- Applied to all users in that region/account type
- Read-only for users

### 2. User-Modifiable Costs
- Initially copied from template's `additional` field
- Stored in `fixed_costs` table per user account
- Users can toggle on/off in the app
- Users can modify values for some costs

### 3. Calculated Costs
- Based on actual meter readings
- Uses tiered pricing from template
- Includes predictive/projected amounts

---

## Important Notes for Future Development

1. **Blade Views vs API** - Changes to create.blade.php and edit.blade.php only affect the admin UI, NOT the API responses or calculations.

2. **JSON Data Format** - The JSON structure in the database must match what MeterService.php expects. Do not change field names without updating the service.

3. **VAT Handling** - Some additional costs have `exempt_vat` field. MeterService must check this when calculating totals.

4. **Date Ranges** - Templates have start_date and end_date. The system should use the appropriate template based on current date.

5. **Account Types** - Different account types (Residential, Commercial, etc.) can have different pricing templates for the same region.

---

## Related Repositories

- **Admin Panel**: SaleamEssop/MyCities-Server-Admin---Laravel (this repo)
- **Mobile App**: SaleamEssop/MyCities-Vue-Quasar

---

Commit message: Add comprehensive architecture documentation for future reference