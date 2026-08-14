<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    /**
     * Display products with search, filters and pagination.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $attribute = $request->input('attribute');
        $attributeValue = $request->input('value');
        $stock = $request->input('stock');
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');

        $query = Product::query();

        /*
        |--------------------------------------------------------------------------
        | Global Search
        |--------------------------------------------------------------------------
        |
        | Search normal fields and schemaless JSON values.
        |
        */
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');

                // Search inside JSON/schemaless attributes
                $q->orWhereRaw(
                    "JSON_SEARCH(extra_attributes, 'one', ?) IS NOT NULL",
                    ['%' . $search . '%']
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Dynamic Attribute Filter
        |--------------------------------------------------------------------------
        |
        | Example:
        | attribute = color
        | value = Red
        |
        */
        $allowedAttributes = [
            'color',
            'size',
            'manufacturer',
            'created_by',
        ];

        if (
            $attribute &&
            $attributeValue !== null &&
            $attributeValue !== '' &&
            in_array($attribute, $allowedAttributes, true)
        ) {
            $query->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(extra_attributes, ?)) LIKE ?",
                [
                    '$.' . $attribute,
                    '%' . $attributeValue . '%',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Stock Filter
        |--------------------------------------------------------------------------
        */
        if ($stock !== null && $stock !== '') {
            if ($stock === '1') {
                $query->whereRaw(
                    "JSON_EXTRACT(extra_attributes, '$.in_stock') = true"
                );
            }

            if ($stock === '0') {
                $query->where(function ($q) {
                    $q->whereRaw(
                        "JSON_EXTRACT(extra_attributes, '$.in_stock') = false"
                    )
                        ->orWhereRaw(
                            "JSON_EXTRACT(extra_attributes, '$.in_stock') IS NULL"
                        );
                });
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Price Minimum
        |--------------------------------------------------------------------------
        */
        if ($priceMin !== null && $priceMin !== '') {
            $query->where('price', '>=', $priceMin);
        }

        /*
        |--------------------------------------------------------------------------
        | Price Maximum
        |--------------------------------------------------------------------------
        */
        if ($priceMax !== null && $priceMax !== '') {
            $query->where('price', '<=', $priceMax);
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        $products = $query
            ->oldest()
            ->paginate(5)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */
        $totalProducts = Product::count();

        $filteredProducts = $products->total();

        return view('products.index', compact(
            'products',
            'search',
            'attribute',
            'attributeValue',
            'stock',
            'priceMin',
            'priceMax',
            'totalProducts',
            'filteredProducts'
        ));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'manufacturer' => 'nullable|string|max:255',
            'warranty_years' => 'nullable|integer|min:0',
        ]);

        $product = new Product();

        $product->name = $validated['name'];
        $product->description = $validated['description'];
        $product->price = $validated['price'];

        $extraAttributes = [
            'color' => $request->color,
            'size' => $request->size,
            'weight' => $request->weight,
            'manufacturer' => $request->manufacturer,
            'warranty_years' => $request->warranty_years,
            'created_by' => 'admin',
            'in_stock' => true,
        ];

        $extraAttributes = array_filter(
            $extraAttributes,
            fn($value) => !is_null($value) && $value !== ''
        );

        $product->extra_attributes = $extraAttributes;

        $product->save();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show product.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show edit form.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'manufacturer' => 'nullable|string|max:255',
            'warranty_years' => 'nullable|integer|min:0',
        ]);

        $product->name = $validated['name'];
        $product->description = $validated['description'];
        $product->price = $validated['price'];

        $extraAttributes = $product->extra_attributes
            ? $product->extra_attributes->toArray()
            : [];

        $extraAttributes['color'] = $request->color;
        $extraAttributes['size'] = $request->size;
        $extraAttributes['weight'] = $request->weight;
        $extraAttributes['manufacturer'] = $request->manufacturer;
        $extraAttributes['warranty_years'] = $request->warranty_years;
        $extraAttributes['updated_at'] = now()->toDateTimeString();

        $extraAttributes = array_filter(
            $extraAttributes,
            fn($value) => !is_null($value) && $value !== ''
        );

        $product->extra_attributes = $extraAttributes;

        $product->save();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Old attribute search route.
     *
     * Kept for compatibility with the existing project.
     */
    public function searchByAttribute(Request $request)
    {
        return redirect()->route('products.index', [
            'attribute' => $request->attribute,
            'value' => $request->value,
        ]);
    }

    /**
     * Export currently filtered products to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $search = $request->input('search');
        $attribute = $request->input('attribute');
        $attributeValue = $request->input('value');
        $stock = $request->input('stock');
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');

        $query = Product::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereRaw(
                        "JSON_SEARCH(extra_attributes, 'one', ?) IS NOT NULL",
                        ['%' . $search . '%']
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Attribute filter
        |--------------------------------------------------------------------------
        */
        $allowedAttributes = [
            'color',
            'size',
            'manufacturer',
            'created_by',
        ];

        if (
            $attribute &&
            $attributeValue !== null &&
            $attributeValue !== '' &&
            in_array($attribute, $allowedAttributes, true)
        ) {
            $query->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(extra_attributes, ?)) LIKE ?",
                [
                    '$.' . $attribute,
                    '%' . $attributeValue . '%',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Stock filter
        |--------------------------------------------------------------------------
        */
        if ($stock !== null && $stock !== '') {
            if ($stock === '1') {
                $query->whereRaw(
                    "JSON_EXTRACT(extra_attributes, '$.in_stock') = true"
                );
            }

            if ($stock === '0') {
                $query->where(function ($q) {
                    $q->whereRaw(
                        "JSON_EXTRACT(extra_attributes, '$.in_stock') = false"
                    )
                        ->orWhereRaw(
                            "JSON_EXTRACT(extra_attributes, '$.in_stock') IS NULL"
                        );
                });
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Price filters
        |--------------------------------------------------------------------------
        */
        if ($priceMin !== null && $priceMin !== '') {
            $query->where('price', '>=', $priceMin);
        }

        if ($priceMax !== null && $priceMax !== '') {
            $query->where('price', '<=', $priceMax);
        }

        $products = $query->latest()->get();

        $filename = 'products-' . now()->format('Y-m-d-H-i-s') . '.csv';

        return response()->streamDownload(function () use ($products) {

            $handle = fopen('php://output', 'w');

            /*
            |--------------------------------------------------------------------------
            | CSV Header
            |--------------------------------------------------------------------------
            */
            fputcsv($handle, [
                'ID',
                'Name',
                'Description',
                'Price',
                'Color',
                'Size',
                'Weight',
                'Manufacturer',
                'Warranty Years',
                'Created By',
                'In Stock',
                'Created At',
            ]);

            /*
            |--------------------------------------------------------------------------
            | CSV Rows
            |--------------------------------------------------------------------------
            */
            foreach ($products as $product) {

                $attributes = $product->extra_attributes
                    ? $product->extra_attributes->toArray()
                    : [];

                fputcsv($handle, [
                    $product->id,
                    $product->name,
                    $product->description,
                    $product->price,
                    $attributes['color'] ?? '',
                    $attributes['size'] ?? '',
                    $attributes['weight'] ?? '',
                    $attributes['manufacturer'] ?? '',
                    $attributes['warranty_years'] ?? '',
                    $attributes['created_by'] ?? '',
                    isset($attributes['in_stock'])
                        ? ($attributes['in_stock'] ? 'Yes' : 'No')
                        : 'No',
                    optional($product->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
