@extends('layouts.app')

@section('title', $product->name)

@section('content')

<div class="row">
    <div class="col-lg-9 offset-lg-1">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-dark text-white">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-0">
                            {{ $product->name }}
                        </h4>

                        <small>
                            Product #{{ $product->id }}
                        </small>

                    </div>

                    <span class="badge bg-info">
                        Schemaless Product
                    </span>

                </div>

            </div>


            <div class="card-body">

                {{-- Basic Information --}}
                <h5 class="mb-3">
                    Product Information
                </h5>

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <tr>
                            <th style="width: 220px;">
                                ID
                            </th>

                            <td>
                                {{ $product->id }}
                            </td>
                        </tr>

                        <tr>
                            <th>
                                Name
                            </th>

                            <td>
                                {{ $product->name }}
                            </td>
                        </tr>

                        <tr>
                            <th>
                                Description
                            </th>

                            <td>
                                {{ $product->description ?: 'No description' }}
                            </td>
                        </tr>

                        <tr>
                            <th>
                                Price
                            </th>

                            <td>
                                <strong>
                                    ${{ number_format($product->price, 2) }}
                                </strong>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                Created
                            </th>

                            <td>
                                {{ $product->created_at->format('Y-m-d H:i:s') }}
                            </td>
                        </tr>

                        <tr>
                            <th>
                                Last Updated
                            </th>

                            <td>
                                {{ $product->updated_at->format('Y-m-d H:i:s') }}
                            </td>
                        </tr>

                    </table>

                </div>


                {{-- Schemaless Attributes --}}
                @php

                    $attributes = $product->extra_attributes
                        ? $product->extra_attributes->toArray()
                        : [];

                @endphp


                @if(count($attributes) > 0)

                    <div class="d-flex justify-content-between align-items-center mt-5 mb-3">

                        <div>

                            <h5 class="mb-1">
                                Dynamic Schemaless Attributes
                            </h5>

                            <small class="text-muted">
                                Stored inside the JSON column without schema changes.
                            </small>

                        </div>

                        <span class="badge bg-primary">
                            {{ count($attributes) }} Attributes
                        </span>

                    </div>


                    <div class="table-responsive">

                        <table class="table table-bordered table-hover">

                            <thead class="table-light">

                                <tr>

                                    <th>
                                        Attribute
                                    </th>

                                    <th>
                                        Value
                                    </th>

                                    <th>
                                        Data Type
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach($attributes as $key => $value)

                                    <tr>

                                        <th style="width: 30%;">

                                            {{ ucfirst(str_replace('_', ' ', $key)) }}

                                        </th>


                                        <td>

                                            @if(is_bool($value))

                                                @if($value)

                                                    <span class="badge bg-success">
                                                        Yes
                                                    </span>

                                                @else

                                                    <span class="badge bg-danger">
                                                        No
                                                    </span>

                                                @endif

                                            @elseif(is_array($value))

                                                <code>
                                                    {{ json_encode($value) }}
                                                </code>

                                            @else

                                                {{ $value }}

                                            @endif

                                        </td>


                                        <td>

                                            @if(is_bool($value))

                                                <span class="badge bg-warning text-dark">
                                                    Boolean
                                                </span>

                                            @elseif(is_int($value))

                                                <span class="badge bg-info text-dark">
                                                    Integer
                                                </span>

                                            @elseif(is_float($value))

                                                <span class="badge bg-info text-dark">
                                                    Decimal
                                                </span>

                                            @elseif(is_array($value))

                                                <span class="badge bg-secondary">
                                                    Array
                                                </span>

                                            @else

                                                <span class="badge bg-primary">
                                                    String
                                                </span>

                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{-- JSON Preview --}}
                    <div class="mt-4">

                        <h6>
                            Raw JSON Preview
                        </h6>

                        <div class="bg-dark text-light rounded p-3">

                            <pre class="mb-0 text-light">{{ json_encode($attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

                        </div>

                    </div>

                @endif


                {{-- Actions --}}
                <div class="mt-4">

                    <a
                        href="{{ route('products.edit', $product) }}"
                        class="btn btn-warning"
                    >
                        Edit Product
                    </a>

                    <a
                        href="{{ route('products.index') }}"
                        class="btn btn-secondary"
                    >
                        Back to Products
                    </a>

                </div>

            </div>

        </div>

    </div>
</div>

@endsection