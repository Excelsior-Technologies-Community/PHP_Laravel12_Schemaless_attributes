<?php
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'color' => 'nullable|string',
            'size' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'manufacturer' => 'nullable|string',
            'warranty_years' => 'nullable|integer',
        ]);

        // Store standard fields
        $product = new Product();
        $product->name = $validated['name'];
        $product->description = $validated['description'];
        $product->price = $validated['price'];
        
        // Store schemaless attributes
        $extraAttributes = [
            'color' => $request->color,
            'size' => $request->size,
            'weight' => $request->weight,
            'manufacturer' => $request->manufacturer,
            'warranty_years' => $request->warranty_years,
            'created_by' => 'admin',
            'in_stock' => true,
        ];
        
        // Remove null values
        $extraAttributes = array_filter($extraAttributes, function($value) {
            return !is_null($value);
        });
        
        $product->extra_attributes = $extraAttributes;
        $product->save();

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'color' => 'nullable|string',
            'size' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'manufacturer' => 'nullable|string',
            'warranty_years' => 'nullable|integer',
        ]);

        // Update standard fields
        $product->name = $validated['name'];
        $product->description = $validated['description'];
        $product->price = $validated['price'];
        
        // Update schemaless attributes
        $extraAttributes = $product->extra_attributes->toArray();
        $extraAttributes['color'] = $request->color;
        $extraAttributes['size'] = $request->size;
        $extraAttributes['weight'] = $request->weight;
        $extraAttributes['manufacturer'] = $request->manufacturer;
        $extraAttributes['warranty_years'] = $request->warranty_years;
        $extraAttributes['updated_at'] = now();
        
        $product->extra_attributes = $extraAttributes;
        $product->save();

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function searchByAttribute(Request $request)
    {
        $attribute = $request->attribute;
        $value = $request->value;
        
        $products = Product::withExtraAttributes()
            ->where("extra_attributes->{$attribute}", $value)
            ->get();
            
        return view('products.index', compact('products'));
    }
}