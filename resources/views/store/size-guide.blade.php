@extends('layouts.storefront')

@section('title', 'Size Guide: ' . $product->name . ' | Ashvalian')

@section('content')
    <div class="container py-5 text-center" style="max-width: 800px;">
        <div class="mb-4">
            <h1 class="fw-black text-black">{{ $product->name }}</h1>
            <p class="text-muted">Official Size Guide & Fit Specifications</p>
        </div>

        <div class="panel panel-pad bg-white shadow-sm border rounded-3 mb-4 p-3 d-inline-block">
            <img src="{{ $product->size_guide_image }}" alt="Size Guide for {{ $product->name }}" class="img-fluid rounded" style="max-height: 700px; object-fit: contain;">
        </div>

        <div class="mt-2">
            <a href="{{ route('products.show', $product->slug) }}" class="btn-ash px-5 py-3 text-decoration-none">
                <i class="bi bi-arrow-left me-2"></i> Return to Product Page
            </a>
        </div>
    </div>
@endsection
