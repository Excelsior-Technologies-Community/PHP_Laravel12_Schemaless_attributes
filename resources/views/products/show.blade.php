{{-- resources/views/products/show.blade.php --}}
@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $product->name }}</h4>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width: 200px;">ID:</th>
                            <td>{{ $product->id }}</td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>{{ $product->name }}</td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $product->description ?: 'No description' }}</td>
                        </tr>
                        <tr>
                            <th>Price:</th>
                            <td>${{ number_format($product->price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $product->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $product->updated_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>

                    @if($product->extra_attributes && count($product->extra_attributes) > 0)
                        <h5 class="mt-4">Additional Attributes (Schemaless)</h5>
                        <table class="table">
                            @foreach($product->extra_attributes as $key => $value)
                                <tr>
                                    <th style="width: 200px;">{{ ucfirst(str_replace('_', ' ', $key)) }}:</th>
                                    <td>
                                        @if(is_bool($value))
                                            {{ $value ? 'Yes' : 'No' }}
                                        @elseif(is_array($value))
                                            {{ json_encode($value) }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection