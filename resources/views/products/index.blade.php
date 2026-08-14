@extends('layouts.app')

@section('title', 'Products')

@section('content')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

        <div>
            <h1 class="fw-bold mb-1">
                Product Management
            </h1>

            <p class="text-muted mb-0">
                Search, filter, paginate and export products.
            </p>
        </div>

        <div class="mt-3 mt-md-0">

            <a href="{{ route('products.create') }}"
                class="btn btn-primary">
                + Create Product
            </a>

        </div>

    </div>


    {{-- Success Message --}}
    @if(session('success'))

    <div class="alert alert-success alert-dismissible fade show">

        {{ session('success') }}

        <button type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

    @endif


    {{-- Statistics --}}
    <div class="row g-3 mb-4">

        <div class="col-md-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-muted">
                        Total Products
                    </small>

                    <h3 class="fw-bold mb-0">
                        {{ $totalProducts }}
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <small class="text-muted">
                        Matching Products
                    </small>

                    <h3 class="fw-bold mb-0">
                        {{ $filteredProducts }}
                    </h3>

                </div>

            </div>

        </div>

    </div>


    {{-- Search and Filter --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-dark text-white">

            <strong>
                Search & Filters
            </strong>

        </div>


        <div class="card-body">

            <form method="GET"
                action="{{ route('products.index') }}">

                <div class="row g-3">

                    {{-- Search --}}
                    <div class="col-lg-4">

                        <label class="form-label">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="Search name, description, color...">

                    </div>


                    {{-- Attribute --}}
                    <div class="col-lg-2">

                        <label class="form-label">
                            Attribute
                        </label>

                        <select name="attribute"
                            class="form-select">

                            <option value="">
                                All
                            </option>

                            <option value="color"
                                {{ $attribute === 'color' ? 'selected' : '' }}>
                                Color
                            </option>

                            <option value="size"
                                {{ $attribute === 'size' ? 'selected' : '' }}>
                                Size
                            </option>

                            <option value="manufacturer"
                                {{ $attribute === 'manufacturer' ? 'selected' : '' }}>
                                Manufacturer
                            </option>

                            <option value="created_by"
                                {{ $attribute === 'created_by' ? 'selected' : '' }}>
                                Created By
                            </option>

                        </select>

                    </div>


                    {{-- Attribute Value --}}
                    <div class="col-lg-3">

                        <label class="form-label">
                            Attribute Value
                        </label>

                        <input
                            type="text"
                            name="value"
                            value="{{ $attributeValue }}"
                            class="form-control"
                            placeholder="Example: Red">

                    </div>


                    {{-- Stock --}}
                    <div class="col-lg-3">

                        <label class="form-label">
                            Stock
                        </label>

                        <select name="stock"
                            class="form-select">

                            <option value="">
                                All
                            </option>

                            <option value="1"
                                {{ $stock === '1' ? 'selected' : '' }}>
                                In Stock
                            </option>

                            <option value="0"
                                {{ $stock === '0' ? 'selected' : '' }}>
                                Out of Stock
                            </option>

                        </select>

                    </div>


                    {{-- Minimum Price --}}
                    <div class="col-lg-3">

                        <label class="form-label">
                            Minimum Price
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="price_min"
                            value="{{ $priceMin }}"
                            class="form-control"
                            placeholder="Min price">

                    </div>


                    {{-- Maximum Price --}}
                    <div class="col-lg-3">

                        <label class="form-label">
                            Maximum Price
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="price_max"
                            value="{{ $priceMax }}"
                            class="form-control"
                            placeholder="Max price">

                    </div>


                    {{-- Buttons --}}
                    <div class="col-lg-6 d-flex align-items-end gap-2">

                        <button type="submit"
                            class="btn btn-primary">

                            🔎 Search / Filter

                        </button>


                        <a href="{{ route('products.index') }}"
                            class="btn btn-secondary">

                            Reset

                        </a>


                        {{-- Export --}}
                        <button
                            type="submit"
                            formaction="{{ route('products.export') }}"
                            class="btn btn-success">

                            📥 Export CSV

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- Active Filters --}}
    @if(
    $search ||
    $attribute ||
    $attributeValue ||
    $stock !== null && $stock !== '' ||
    $priceMin !== null && $priceMin !== '' ||
    $priceMax !== null && $priceMax !== ''
    )

    <div class="alert alert-info">

        <strong>Active filters:</strong>

        @if($search)
        <span class="badge bg-primary">
            Search: {{ $search }}
        </span>
        @endif

        @if($attribute)
        <span class="badge bg-dark">
            {{ ucfirst($attribute) }}
        </span>
        @endif

        @if($attributeValue)
        <span class="badge bg-secondary">
            Value: {{ $attributeValue }}
        </span>
        @endif

        @if($stock === '1')
        <span class="badge bg-success">
            In Stock
        </span>
        @elseif($stock === '0')
        <span class="badge bg-danger">
            Out of Stock
        </span>
        @endif

        @if($priceMin !== null && $priceMin !== '')
        <span class="badge bg-info text-dark">
            Min: {{ $priceMin }}
        </span>
        @endif

        @if($priceMax !== null && $priceMax !== '')
        <span class="badge bg-info text-dark">
            Max: {{ $priceMax }}
        </span>
        @endif

    </div>

    @endif


    {{-- Product Table --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center">

                <strong>
                    Products
                </strong>

                <span class="text-muted">

                    Showing
                    {{ $products->firstItem() ?? 0 }}
                    -
                    {{ $products->lastItem() ?? 0 }}

                    of

                    {{ $products->total() }}

                </span>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-dark">

                        <tr>

                            <th>#</th>

                            <th>
                                Product
                            </th>

                            <th>
                                Price
                            </th>

                            <th>
                                Color
                            </th>

                            <th>
                                Size
                            </th>

                            <th>
                                Manufacturer
                            </th>

                            <th>
                                Stock
                            </th>

                            <th>
                                Created
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($products as $product)

                        @php

                        $attributes = $product->extra_attributes
                        ? $product->extra_attributes->toArray()
                        : [];

                        @endphp


                        <tr>

                            <td>
                                {{ $product->id }}
                            </td>


                            <td>

                                <div class="fw-bold">
                                    {{ $product->name }}
                                </div>

                                <small class="text-muted">

                                    {{ \Illuminate\Support\Str::limit(
                                        $product->description,
                                        50
                                    ) }}

                                </small>

                            </td>


                            <td>

                                <strong>
                                    ${{ number_format($product->price, 2) }}
                                </strong>

                            </td>


                            <td>

                                {{ $attributes['color'] ?? '-' }}

                            </td>


                            <td>

                                {{ $attributes['size'] ?? '-' }}

                            </td>


                            <td>

                                {{ $attributes['manufacturer'] ?? '-' }}

                            </td>


                            <td>

                                @if($attributes['in_stock'] ?? false)

                                <span class="badge bg-success">
                                    In Stock
                                </span>

                                @else

                                <span class="badge bg-danger">
                                    Out of Stock
                                </span>

                                @endif

                            </td>


                            <td>

                                <small>
                                    {{ $product->created_at?->format('d M Y') }}
                                </small>

                            </td>


                            <td>

                                <div class="d-flex gap-1">

                                    <a
                                        href="{{ route('products.show', $product) }}"
                                        class="btn btn-sm btn-info text-white">
                                        View
                                    </a>


                                    <a
                                        href="{{ route('products.edit', $product) }}"
                                        class="btn btn-sm btn-warning">
                                        Edit
                                    </a>


                                    <form
                                        action="{{ route('products.destroy', $product) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');">

                                        @csrf

                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger">
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="9"
                                class="text-center py-5">

                                <h5>
                                    No products found
                                </h5>

                                <p class="text-muted">
                                    Try changing your search or filters.
                                </p>

                                <a
                                    href="{{ route('products.index') }}"
                                    class="btn btn-secondary">
                                    Clear Filters
                                </a>

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- Pagination --}}
        @if($products->hasPages())

        <div class="card-footer bg-white">

            <div class="d-flex justify-content-center">

                <nav>

                    <ul class="pagination mb-0">

                        {{-- Previous --}}
                        @if($products->onFirstPage())

                        <li class="page-item disabled">

                            <span class="page-link">
                                Previous
                            </span>

                        </li>

                        @else

                        <li class="page-item">

                            <a
                                class="page-link"
                                href="{{ $products->previousPageUrl() }}">
                                Previous
                            </a>

                        </li>

                        @endif


                        {{-- Page Numbers --}}
                        @for(
                        $page = 1;
                        $page <= $products->lastPage();
                            $page++
                            )

                            @if($page == $products->currentPage())

                            <li class="page-item active">

                                <span class="page-link">
                                    {{ $page }}
                                </span>

                            </li>

                            @else

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="{{ $products->url($page) }}">
                                    {{ $page }}
                                </a>

                            </li>

                            @endif

                            @endfor


                            {{-- Next --}}
                            @if($products->hasMorePages())

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="{{ $products->nextPageUrl() }}">
                                    Next
                                </a>

                            </li>

                            @else

                            <li class="page-item disabled">

                                <span class="page-link">
                                    Next
                                </span>

                            </li>

                            @endif

                    </ul>

                </nav>

            </div>

        </div>

        @endif

    </div>

</div>

@endsection