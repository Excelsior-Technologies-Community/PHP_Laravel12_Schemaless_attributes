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
        | Dynamic Attribute Filter
        |--------------------------------------------------------------------------
        |
        | The attribute can now be any valid schemaless attribute.
        |
        */
        if (
            $attribute &&
            $attributeValue !== null &&
            $attributeValue !== ''
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
                    )->orWhereRaw(
                        "JSON_EXTRACT(extra_attributes, '$.in_stock') IS NULL"
                    );
                });
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum Price
        |--------------------------------------------------------------------------
        */
        if ($priceMin !== null && $priceMin !== '') {
            $query->where('price', '>=', $priceMin);
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Price
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

            'attribute_name' => 'nullable|array',
            'attribute_name.*' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/',
            ],

            'attribute_value' => 'nullable|array',
            'attribute_value.*' => 'nullable|string|max:1000',

            'attribute_type' => 'nullable|array',
            'attribute_type.*' => 'nullable|in:text,number,boolean,date',
        ], [
            'attribute_name.*.regex' =>
                'Attribute names may contain only letters, numbers and underscores, and must start with a letter.',
        ]);

        $product = new Product();

        $product->name = $validated['name'];
        $product->description = $validated['description'] ?? null;
        $product->price = $validated['price'];

        /*
        |--------------------------------------------------------------------------
        | Build Dynamic Schemaless Attributes
        |--------------------------------------------------------------------------
        */
        $extraAttributes = $this->buildDynamicAttributes($request);

        /*
        |--------------------------------------------------------------------------
        | Default Attributes
        |--------------------------------------------------------------------------
        */
        $extraAttributes['created_by'] = 'admin';
        $extraAttributes['in_stock'] = true;

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

            'attribute_name' => 'nullable|array',
            'attribute_name.*' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/',
            ],

            'attribute_value' => 'nullable|array',
            'attribute_value.*' => 'nullable|string|max:1000',

            'attribute_type' => 'nullable|array',
            'attribute_type.*' => 'nullable|in:text,number,boolean,date',
        ], [
            'attribute_name.*.regex' =>
                'Attribute names may contain only letters, numbers and underscores, and must start with a letter.',
        ]);

        $product->name = $validated['name'];
        $product->description = $validated['description'] ?? null;
        $product->price = $validated['price'];

        /*
        |--------------------------------------------------------------------------
        | Existing Attributes
        |--------------------------------------------------------------------------
        */
        $existingAttributes = $product->extra_attributes
            ? $product->extra_attributes->toArray()
            : [];

        /*
        |--------------------------------------------------------------------------
        | Preserve System Attributes
        |--------------------------------------------------------------------------
        */
        $createdBy = $existingAttributes['created_by'] ?? 'admin';
        $inStock = $existingAttributes['in_stock'] ?? true;

        /*
        |--------------------------------------------------------------------------
        | Build New Dynamic Attributes
        |--------------------------------------------------------------------------
        */
        $extraAttributes = $this->buildDynamicAttributes($request);

        /*
        |--------------------------------------------------------------------------
        | System Attributes
        |--------------------------------------------------------------------------
        */
        $extraAttributes['created_by'] = $createdBy;
        $extraAttributes['in_stock'] = $inStock;
        $extraAttributes['updated_at'] = now()->toDateTimeString();

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
     * Search products by dynamic attribute.
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
        | Dynamic Attribute Filter
        |--------------------------------------------------------------------------
        */
        if (
            $attribute &&
            $attributeValue !== null &&
            $attributeValue !== ''
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
                    )->orWhereRaw(
                        "JSON_EXTRACT(extra_attributes, '$.in_stock') IS NULL"
                    );
                });
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Price Filters
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

            fputcsv($handle, [
                'ID',
                'Name',
                'Description',
                'Price',
                'Dynamic Attributes',
                'Created At',
            ]);

            foreach ($products as $product) {
                $attributes = $product->extra_attributes
                    ? $product->extra_attributes->toArray()
                    : [];

                fputcsv($handle, [
                    $product->id,
                    $product->name,
                    $product->description,
                    $product->price,
                    json_encode($attributes),
                    optional($product->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Build typed dynamic schemaless attributes.
     */
    private function buildDynamicAttributes(Request $request): array
    {
        $names = $request->input('attribute_name', []);
        $values = $request->input('attribute_value', []);
        $types = $request->input('attribute_type', []);

        $attributes = [];

        foreach ($names as $index => $name) {
            $name = trim($name ?? '');

            if ($name === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent system fields from being overwritten
            |--------------------------------------------------------------------------
            */
            if (in_array($name, [
                'created_by',
                'in_stock',
                'updated_at',
            ], true)) {
                continue;
            }

            $value = $values[$index] ?? '';
            $type = $types[$index] ?? 'text';

            /*
            |--------------------------------------------------------------------------
            | Convert Value According To Type
            |--------------------------------------------------------------------------
            */
            switch ($type) {
                case 'number':
                    if ($value === '') {
                        continue 2;
                    }

                    $attributes[$name] = is_numeric($value)
                        ? (str_contains((string) $value, '.')
                            ? (float) $value
                            : (int) $value)
                        : $value;

                    break;

                case 'boolean':
                    $attributes[$name] = in_array(
                        strtolower((string) $value),
                        ['1', 'true', 'yes', 'on'],
                        true
                    );

                    break;

                case 'date':
                    if ($value === '') {
                        continue 2;
                    }

                    $attributes[$name] = $value;

                    break;

                case 'text':
                default:
                    if ($value === '') {
                        continue 2;
                    }

                    $attributes[$name] = (string) $value;

                    break;
            }
        }

        return $attributes;
    }
}