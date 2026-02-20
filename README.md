# PHP_Laravel12_Schemaless_attributes

This project demonstrates how to build a flexible product management system using **Spatie Laravel Schemaless Attributes**.

It allows you to:

* Store dynamic attributes without modifying database schema
* Save JSON data easily
* Query products by dynamic fields
* Access attributes like normal model properties

---

## STEP 1 – Create New Laravel Project

composer create-project laravel/laravel schemaless-demo
cd schemaless-demo

---

## STEP 2 – Install Required Package

composer require spatie/laravel-schemaless-attributes

---

## STEP 3 – Create Products Migration

php artisan make:migration create_products_table

Migration Structure:

* id
* name
* description
* price
* extra_attributes (JSON)
* timestamps

Run Migration:
php artisan migrate

---

## STEP 4 – Product Model Configuration

Add SchemalessAttributes cast to Product model.

Key Points:

* extra_attributes is stored as JSON
* Automatically cast to object/array
* Can be accessed like:
  $product->extra_attributes->color

Add scopeWithExtraAttributes() for querying JSON attributes.

---

## STEP 5 – Controller Logic

Controller Handles:

1. index() – Display all products
2. create() – Show create form
3. store() – Save product with dynamic attributes
4. show() – Display single product
5. edit() – Edit form
6. update() – Update normal + JSON attributes
7. destroy() – Delete product
8. searchByAttribute() – Query JSON attributes

Dynamic attributes example stored:
{
"color": "Red",
"size": "XL",
"weight": 2.5,
"manufacturer": "Nike",
"warranty_years": 2,
"created_by": "admin",
"in_stock": true
}

---

## STEP 6 – Routes

Route::resource('products', ProductController::class);
Route::get('/search-by-attribute', [ProductController::class, 'searchByAttribute']);

---

## STEP 7 – Blade Views

Structure:
resources/views/layouts/app.blade.php
resources/views/products/index.blade.php
resources/views/products/create.blade.php
resources/views/products/edit.blade.php
resources/views/products/show.blade.php

UI Framework: Bootstrap 5

Features Included:

* Product listing
* Search by dynamic attribute
* Display schemaless data
* Form validation
* CRUD operations

---

## STEP 8 – Run Project

php artisan serve

Visit:
[http://localhost:8000](http://localhost:8000)
<img width="1614" height="928" alt="image" src="https://github.com/user-attachments/assets/60ce52dc-2ee1-4d4a-a76c-8cf5d920eb16" />
<img width="1534" height="968" alt="image" src="https://github.com/user-attachments/assets/8f4ccf21-4f26-4eca-a8af-bc8ff684a964" />


---

## How Schemaless Attributes Work

1. Data stored in JSON column
2. Automatically cast into PHP object
3. No migration required for new attributes
4. Query using:
   where("extra_attributes->color", "Red")

---

## Example Access

$product->extra_attributes->color
$product->extra_attributes->manufacturer

---

## Key Advantages

1. Flexible Schema
   Add new attributes anytime without migration

2. JSON Storage
   Efficient storage of variable data

3. Easy Querying
   Search by dynamic attributes

4. Clean Model Integration
   Works like normal Eloquent attributes

---

## Real‑World Use Cases

• E‑commerce product variations
• Custom user profile fields
• IoT dynamic device metadata
• Survey response storage
• Configurable SaaS modules

---

## Best Practices

• Do not overuse schemaless fields for relational data
• Index JSON fields if frequently queried
• Validate dynamic attributes carefully
• Keep core important fields structured

---

## Advanced Improvements You Can Add

• Add filtering UI for JSON attributes
• Add pagination
• Add authentication
• Add API endpoints
• Add multi-tenant support
• Add dynamic attribute builder (admin configurable fields)

---

## Summary

This project demonstrates:

* Flexible JSON column usage
* Dynamic attribute storage
* Searchable schemaless fields
* Clean CRUD structure

You now have a complete Laravel Schemaless Attributes demo project ready for learning, portfolio, or production foundation.

End of Documentation
