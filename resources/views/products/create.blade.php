@extends('layouts.app')

@section('title', 'Create Product')

@section('content')

<div class="row">
    <div class="col-lg-10 offset-lg-1">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-dark text-white">
                <h4 class="mb-0">
                    Create New Product
                </h4>

                <small class="text-light">
                    Add standard and dynamic schemaless attributes.
                </small>
            </div>

            <div class="card-body">

                <form action="{{ route('products.store') }}" method="POST">
                    @csrf

                    {{-- Basic Information --}}
                    <h5 class="mb-3">
                        Basic Information
                    </h5>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label for="name" class="form-label">
                                Product Name *
                            </label>

                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                            >

                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label for="price" class="form-label">
                                Price *
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control @error('price') is-invalid @enderror"
                                id="price"
                                name="price"
                                value="{{ old('price') }}"
                                required
                            >

                            @error('price')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                    <div class="mb-4">

                        <label for="description" class="form-label">
                            Description
                        </label>

                        <textarea
                            class="form-control @error('description') is-invalid @enderror"
                            id="description"
                            name="description"
                            rows="4"
                        >{{ old('description') }}</textarea>

                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Dynamic Attributes --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>
                            <h5 class="mb-1">
                                Dynamic Schemaless Attributes
                            </h5>

                            <small class="text-muted">
                                Add any custom attributes without changing the database schema.
                            </small>
                        </div>

                        <button
                            type="button"
                            class="btn btn-outline-primary"
                            id="addAttribute"
                        >
                            + Add Attribute
                        </button>

                    </div>


                    <div id="attributesContainer">

                        {{-- Existing old input after validation --}}
                        @if(old('attribute_name'))

                            @foreach(old('attribute_name') as $index => $name)

                                <div class="attribute-row border rounded p-3 mb-3 bg-light">

                                    <div class="row align-items-end">

                                        <div class="col-md-4">

                                            <label class="form-label">
                                                Attribute Name
                                            </label>

                                            <input
                                                type="text"
                                                name="attribute_name[]"
                                                class="form-control"
                                                value="{{ $name }}"
                                                placeholder="Example: material"
                                            >

                                        </div>

                                        <div class="col-md-3">

                                            <label class="form-label">
                                                Type
                                            </label>

                                            <select
                                                name="attribute_type[]"
                                                class="form-select"
                                            >

                                                <option value="text"
                                                    {{ old("attribute_type.$index") === 'text' ? 'selected' : '' }}>
                                                    Text
                                                </option>

                                                <option value="number"
                                                    {{ old("attribute_type.$index") === 'number' ? 'selected' : '' }}>
                                                    Number
                                                </option>

                                                <option value="boolean"
                                                    {{ old("attribute_type.$index") === 'boolean' ? 'selected' : '' }}>
                                                    Boolean
                                                </option>

                                                <option value="date"
                                                    {{ old("attribute_type.$index") === 'date' ? 'selected' : '' }}>
                                                    Date
                                                </option>

                                            </select>

                                        </div>

                                        <div class="col-md-4">

                                            <label class="form-label">
                                                Value
                                            </label>

                                            <input
                                                type="text"
                                                name="attribute_value[]"
                                                class="form-control"
                                                value="{{ old("attribute_value.$index") }}"
                                                placeholder="Attribute value"
                                            >

                                        </div>

                                        <div class="col-md-1">

                                            <button
                                                type="button"
                                                class="btn btn-outline-danger removeAttribute w-100"
                                            >
                                                ×
                                            </button>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        @endif

                    </div>


                    {{-- Help --}}
                    <div class="alert alert-info mt-3">

                        <strong>Examples:</strong>

                        <span class="ms-2">
                            material = Aluminum
                        </span>

                        <span class="ms-2">
                            ram = 12
                        </span>

                        <span class="ms-2">
                            waterproof = true
                        </span>

                        <span class="ms-2">
                            release_date = 2026-08-14
                        </span>

                    </div>


                    {{-- Buttons --}}
                    <div class="mt-4">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Create Product
                        </button>

                        <a
                            href="{{ route('products.index') }}"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>
</div>

@endsection


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const container = document.getElementById('attributesContainer');
    const addButton = document.getElementById('addAttribute');

    function createAttributeRow() {

        const row = document.createElement('div');

        row.className = 'attribute-row border rounded p-3 mb-3 bg-light';

        row.innerHTML = `
            <div class="row align-items-end">

                <div class="col-md-4">

                    <label class="form-label">
                        Attribute Name
                    </label>

                    <input
                        type="text"
                        name="attribute_name[]"
                        class="form-control"
                        placeholder="Example: material"
                    >

                </div>

                <div class="col-md-3">

                    <label class="form-label">
                        Type
                    </label>

                    <select
                        name="attribute_type[]"
                        class="form-select attribute-type"
                    >

                        <option value="text">
                            Text
                        </option>

                        <option value="number">
                            Number
                        </option>

                        <option value="boolean">
                            Boolean
                        </option>

                        <option value="date">
                            Date
                        </option>

                    </select>

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Value
                    </label>

                    <input
                        type="text"
                        name="attribute_value[]"
                        class="form-control attribute-value"
                        placeholder="Attribute value"
                    >

                </div>

                <div class="col-md-1">

                    <button
                        type="button"
                        class="btn btn-outline-danger removeAttribute w-100"
                    >
                        ×
                    </button>

                </div>

            </div>
        `;

        container.appendChild(row);

        setupTypeListener(row);
    }


    function setupTypeListener(row) {

        const typeSelect = row.querySelector('.attribute-type');
        const valueInput = row.querySelector('.attribute-value');

        typeSelect.addEventListener('change', function () {

            if (this.value === 'number') {

                valueInput.type = 'number';
                valueInput.step = 'any';
                valueInput.placeholder = 'Example: 12.5';

            } else if (this.value === 'date') {

                valueInput.type = 'date';
                valueInput.removeAttribute('step');
                valueInput.placeholder = '';

            } else if (this.value === 'boolean') {

                valueInput.type = 'text';
                valueInput.removeAttribute('step');
                valueInput.placeholder = 'true or false';

            } else {

                valueInput.type = 'text';
                valueInput.removeAttribute('step');
                valueInput.placeholder = 'Attribute value';
            }

        });
    }


    addButton.addEventListener('click', function () {
        createAttributeRow();
    });


    container.addEventListener('click', function (event) {

        if (event.target.classList.contains('removeAttribute')) {

            event.target.closest('.attribute-row').remove();

        }

    });


    document.querySelectorAll('.attribute-row').forEach(function (row) {
        setupTypeListener(row);
    });

});

</script>

@endpush