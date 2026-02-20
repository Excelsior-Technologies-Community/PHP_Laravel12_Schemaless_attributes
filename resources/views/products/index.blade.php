{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <div class="row mb-4">
        <div class="col">
            <h1>Products</h1>
        </div>
        <div class="col text-end">
            <a href="{{ route('products.create') }}" class="btn btn-primary">Create New Product</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col">
            <form action="{{ route('products.search-by-attribute') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="attribute" class="form-control" placeholder="Attribute name (e.g., color)" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="value" class="form-control" placeholder="Attribute value" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-info">Search by Attribute</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        @forelse($products as $product)
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="card-text">{{ Str::limit($product->description, 100) }}</p>
                        <p class="card-text"><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
                        
                        @if($product->extra_attributes && count($product->extra_attributes) > 0)
                            <div class="mt-3">
                                <h6>Additional Attributes:</h6>
                                <ul class="list-unstyled">
                                    @foreach($product->extra_attributes as $key => $value)
                                        <li><small><strong>{{ $key }}:</strong> 
                                            @if(is_bool($value))
                                                {{ $value ? 'Yes' : 'No' }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </small></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="mt-3">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col">
                <p class="text-center">No products found.</p>
            </div>
        @endforelse
    </div>
@endsection