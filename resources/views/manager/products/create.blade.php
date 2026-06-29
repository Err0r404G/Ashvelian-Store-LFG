@extends('layouts.portal')

@section('title', isset($product) ? 'Edit Product — ' . $product->name : 'Add New Product')

@section('content')
<style>
    /* ── Page-specific overrides ─────────────────────────────────── */
    .pf-section-label {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--ash-muted);
    }
    .pf-section-label::after {
        content: "";
        flex: 1;
        height: 1px;
        background: var(--ash-line);
    }

    /* Image upload zones */
    .pf-primary-drop {
        display: grid;
        place-items: center;
        min-height: 260px;
        border: 2px dashed var(--ash-line);
        border-radius: 12px;
        background: #fafafa;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
    }
    .pf-primary-drop:hover {
        border-color: var(--ash-blue);
        background: var(--ash-blue-soft);
    }
    .pf-thumb-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-top: 12px;
    }
    .pf-thumb-slot {
        position: relative;
        overflow: hidden;
        display: grid;
        place-items: center;
        aspect-ratio: 1 / 1;
        border: 1.5px dashed #d8d8d8;
        border-radius: 10px;
        background: #fafafa;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
    }
    .pf-thumb-slot:hover {
        border-color: var(--ash-blue);
        background: var(--ash-blue-soft);
    }
    .pf-thumb-slot .pf-thumb-overlay {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        background: #fff;
    }
    .pf-thumb-slot .pf-thumb-overlay.active {
        display: flex;
    }
    .pf-thumb-slot img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Variant stock table */
    #variant-stock-section .vs-table th {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #888;
        background: #f7f7f7;
        border-bottom: 1px solid var(--ash-line);
        padding: 12px 16px;
    }
    #variant-stock-section .vs-table td {
        padding: 10px 16px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    #variant-stock-section .vs-table tr:last-child td {
        border-bottom: 0;
    }
    #variant-stock-section .vs-comb-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 4px 10px;
        border-radius: 999px;
        background: #f0f0f0;
        font-size: 0.82rem;
        font-weight: 700;
    }

    /* Switch toggle */
    .pf-switch {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 0;
        border-bottom: 1px solid var(--ash-line);
        cursor: pointer;
    }
    .pf-switch:last-child { border-bottom: 0; }
    .pf-switch .form-check-input { cursor: pointer; }
    .pf-switch-label { flex: 1; }
    .pf-switch-label strong { display: block; font-size: 0.92rem; }
    .pf-switch-label small { color: var(--ash-muted); font-size: 0.8rem; }

    /* Breadcrumb */
    .pf-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 28px;
        font-size: 0.83rem;
        color: var(--ash-muted);
    }
    .pf-breadcrumb a { color: var(--ash-muted); text-decoration: none; }
    .pf-breadcrumb a:hover { color: var(--ash-black); }
    .pf-breadcrumb .sep { opacity: 0.4; }
    .pf-breadcrumb .current { color: var(--ash-black); font-weight: 700; }

    /* Save bar */
    .pf-save-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 28px;
        border: 1px solid var(--ash-line);
        border-radius: 12px;
        background: #fff;
        box-shadow: var(--ash-shadow);
        margin-top: 32px;
        margin-bottom: 20px;
    }

    /* Tip banner */
    .pf-tip {
        display: flex;
        gap: 12px;
        padding: 14px 18px;
        border-radius: 10px;
        background: #eff6ff;
        border-left: 3px solid var(--ash-blue);
        font-size: 0.85rem;
        color: #334;
        margin-top: 16px;
    }
    .pf-tip i { color: var(--ash-blue); font-size: 1rem; flex-shrink: 0; margin-top: 1px; }
</style>

{{-- Breadcrumb --}}
<nav class="pf-breadcrumb">
    <a href="{{ route('manager.products.index') }}"><i class="bi bi-box"></i> Products</a>
    <span class="sep">/</span>
    <span class="current">{{ isset($product) ? 'Edit: ' . $product->name : 'New Product' }}</span>
</nav>

{{-- Page Header --}}
<div class="portal-header">
    <div class="portal-title">
        <h1>{{ isset($product) ? 'Edit Product' : 'Add New Product' }}</h1>
        <p class="muted mt-1">{{ isset($product) ? 'Update the details, variants, pricing and imagery.' : 'Fill in the form below to list a new product in your store.' }}</p>
    </div>
    <div class="d-flex gap-3">
        <a class="btn-ghost" href="{{ route('manager.products.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

{{-- Validation Errors --}}
@if ($errors->any())
<div class="alert alert-danger border-0 rounded-3 mb-4" style="background:#fff2f2; border-left: 4px solid #e74c3c !important;">
    <strong><i class="bi bi-exclamation-circle me-2"></i>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2 ps-3">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="post" enctype="multipart/form-data"
      action="{{ isset($product) ? route('manager.products.update', $product) : route('manager.products.store') }}"
      id="product-form">
    @csrf
    @isset($product)
        @method('PUT')
    @endisset

    <div class="row g-4 align-items-start">

        {{-- ═══════════════════════════ LEFT COLUMN ═══════════════════════════ --}}
        <div class="col-lg-8">

            {{-- ── Basic Info ──────────────────────────────────────────────── --}}
            <div class="panel panel-pad mb-4">
                <div class="pf-section-label"><i class="bi bi-info-circle"></i> Basic Information</div>
                <div class="mb-4">
                    <label class="form-label fw-semibold" for="input-name">Product Name</label>
                    <input id="input-name" class="form-control" name="name"
                           value="{{ old('name', $product->name ?? '') }}"
                           placeholder="e.g. Carbon-Tech Performance Jersey" required>
                    <div class="form-text">This will be shown on your store listing and used to generate the URL slug.</div>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold" for="input-desc">Description</label>
                    <textarea id="input-desc" class="form-control" name="description" rows="6"
                              placeholder="Describe the technical specifications, materials, and luxury finish…" required>{{ old('description', $product->description ?? '') }}</textarea>
                </div>
            </div>

            {{-- ── Pricing & Inventory ─────────────────────────────────────── --}}
            <div class="panel panel-pad mb-4">
                <div class="pf-section-label"><i class="bi bi-tag"></i> Pricing & Inventory</div>
                <div class="row g-3">
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold">Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input class="form-control" name="price" type="number" step="0.01"
                                   value="{{ old('price', $product->price ?? '0.00') }}" required>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold">Cost <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input class="form-control" name="cost" type="number" step="0.01"
                                   value="{{ old('cost', $product->cost ?? '0.00') }}" required>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold">Compare At Price</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input class="form-control" name="compare_at_price" type="number" step="0.01"
                                   value="{{ old('compare_at_price', $product->compare_at_price ?? '') }}"
                                   placeholder="Optional">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold" for="stock_quantity_input">Stock Quantity <span class="text-danger">*</span></label>
                        <input class="form-control" name="stock_quantity" type="number"
                               id="stock_quantity_input"
                               value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required>
                        <div class="form-text">Auto-summed from variants when options are set.</div>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold">Low Stock Alert</label>
                        <input class="form-control" name="low_stock_threshold" type="number"
                               value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}" required>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold">SKU <span class="text-danger">*</span></label>
                        <input class="form-control" name="sku"
                               value="{{ old('sku', $product->sku ?? '') }}"
                               placeholder="ASH-2024-XP" required>
                    </div>
                </div>
            </div>

            {{-- ── Classification ──────────────────────────────────────────── --}}
            <div class="panel panel-pad mb-4">
                <div class="pf-section-label"><i class="bi bi-diagram-3"></i> Classification & Status</div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_id" required>
                            <option value="">— Choose Category —</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected(old('category_id', $product->category_id ?? '') == $category->id)
                                    style="{{ $category->parent ? '' : 'font-weight: bold;' }}">
                                    @if ($category->parent)
                                        &nbsp;&nbsp;&nbsp;&nbsp;↳ {{ $category->name }}
                                    @else
                                        {{ $category->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Publish Status</label>
                        <select class="form-select" name="status">
                            @foreach (['active', 'draft', 'hidden', 'out_of_stock'] as $status)
                                <option value="{{ $status }}"
                                    @selected(old('status', $product->status ?? 'active') === $status)>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="pf-switch">
                        <input type="hidden" name="is_featured" value="0">
                        <input class="form-check-input mt-0" type="checkbox" name="is_featured" id="sw-featured" value="1"
                               @checked(old('is_featured', $product->is_featured ?? false))>
                        <label class="pf-switch-label" for="sw-featured">
                            <strong>Featured Product</strong>
                            <small>Show this product in the featured section on the homepage.</small>
                        </label>
                    </div>
                    <div class="pf-switch">
                        <input type="hidden" name="is_on_sale" value="0">
                        <input class="form-check-input mt-0" type="checkbox" name="is_on_sale" id="sw-sale" value="1"
                               @checked(old('is_on_sale', $product->is_on_sale ?? false))>
                        <label class="pf-switch-label" for="sw-sale">
                            <strong>On Sale</strong>
                            <small>Show this product in the Sale section of the store.</small>
                        </label>
                    </div>
                </div>
            </div>

            {{-- ── Product Options (Variants) ───────────────────────────────── --}}
            <div class="panel panel-pad mb-4">
                <div class="pf-section-label"><i class="bi bi-grid-3x3-gap"></i> Product Options — Sizes & Colors</div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="sizes-input">Available Sizes</label>
                        <input id="sizes-input" class="form-control" name="sizes"
                               value="{{ is_array(old('sizes')) ? implode(', ', old('sizes')) : old('sizes', isset($product->sizes) ? implode(', ', $product->sizes) : '') }}"
                               placeholder="e.g. XS, S, M, L, XL, XXL">
                        <div class="form-text"><i class="bi bi-info-circle me-1"></i>Comma-separated values — e.g. <strong>XS, S, M, L, XL</strong></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="colors-input">Available Colors</label>
                        <input id="colors-input" class="form-control" name="colors"
                               value="{{ is_array(old('colors')) ? implode(', ', old('colors')) : old('colors', isset($product->colors) ? implode(', ', $product->colors) : '') }}"
                               placeholder="e.g. White, Blue, Black, Yellow">
                        <div class="form-text"><i class="bi bi-info-circle me-1"></i>Comma-separated values — e.g. <strong>White, Blue, Black</strong></div>
                    </div>
                </div>

                {{-- Variant Stock Matrix --}}
                <div id="variant-stock-section" style="display:none;" class="mt-2">
                    <div class="pf-section-label mt-2"><i class="bi bi-table"></i> Stock Qty & Image per Variant</div>
                    <div class="table-responsive">
                        <table class="vs-table w-100" style="border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th>Variant</th>
                                    <th style="width:160px;">Qty</th>
                                    <th>Option Image URL</th>
                                </tr>
                            </thead>
                            <tbody id="variant-stock-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── Technical Specifications ─────────────────────────────────── --}}
            <div class="panel panel-pad mb-4">
                <div class="pf-section-label"><i class="bi bi-cpu"></i> Technical Specifications</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Material</label>
                        <input class="form-control" name="specifications[Material]"
                               value="{{ old('specifications.Material', $product->specifications['Material'] ?? '') }}"
                               placeholder="e.g. 82% recycled performance fiber, 18% elastane">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Weight</label>
                        <input class="form-control" name="specifications[Weight]"
                               value="{{ old('specifications.Weight', $product->specifications['Weight'] ?? '') }}"
                               placeholder="e.g. Lightweight / Heavyweight">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Breathability</label>
                        <input class="form-control" name="specifications[Breathability]"
                               value="{{ old('specifications.Breathability', $product->specifications['Breathability'] ?? '') }}"
                               placeholder="e.g. Extreme / Ventalux system">
                    </div>
                </div>
            </div>

            {{-- ── Size Guide ──────────────────────────────────────────────── --}}
            <div class="panel panel-pad mb-4">
                <div class="pf-section-label"><i class="bi bi-rulers"></i> Size Guide</div>
                <div class="pf-switch mb-3">
                    <input type="hidden" name="has_size_guide" value="0">
                    <input class="form-check-input mt-0" type="checkbox" name="has_size_guide" id="has_size_guide" value="1"
                           @checked(old('has_size_guide', $product->has_size_guide ?? false))>
                    <label class="pf-switch-label" for="has_size_guide">
                        <strong>Enable Size Guide</strong>
                        <small>Show a size guide image on this product's page.</small>
                    </label>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Upload Size Guide Image</label>
                        <input type="file" name="size_guide_image_file" class="form-control" accept="image/*">
                        @if (isset($product) && $product->size_guide_image)
                            <div class="form-text text-success"><i class="bi bi-check-circle me-1"></i>Current: <a href="{{ $product->size_guide_image }}" target="_blank">{{ basename($product->size_guide_image) }}</a></div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Or Paste Image URL</label>
                        <input type="url" name="size_guide_image" class="form-control"
                               value="{{ old('size_guide_image', $product->size_guide_image ?? '') }}"
                               placeholder="https://example.com/size-guide.png">
                    </div>
                </div>
            </div>

        </div>{{-- /col-lg-8 --}}

        {{-- ═══════════════════════════ RIGHT COLUMN ══════════════════════════ --}}
        <div class="col-lg-4">

            {{-- ── Product Images ──────────────────────────────────────────── --}}
            <div class="panel panel-pad mb-4" style="position:sticky; top:24px;">
                <div class="pf-section-label"><i class="bi bi-images"></i> Product Imagery</div>

                {{-- Primary Image --}}
                <div class="pf-primary-drop position-relative mb-2" id="primary-image-container">
                    <label class="w-100 h-100 d-flex flex-column align-items-center justify-content-center m-0 cursor-pointer gap-2"
                           id="primary-upload-label" style="min-height:260px; padding:20px;">
                        <input class="d-none" type="file" name="primary_image" id="primary-image-input" accept="image/*">
                        <i class="bi bi-cloud-arrow-up fs-1 text-muted"></i>
                        <div class="text-center">
                            <strong class="d-block">Click to upload primary image</strong>
                            <small class="text-muted">PNG or JPG up to 5 MB</small>
                        </div>
                    </label>
                    <div id="primary-preview-container"
                         class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center"
                         style="background:#f8f8f8; border-radius:12px;">
                        <img id="primary-preview-img" class="w-100 h-100"
                             style="object-fit:contain; padding:12px; border-radius:12px;" src="">
                        <button type="button" id="btn-remove-primary"
                                class="btn btn-danger btn-sm position-absolute"
                                style="top:10px;right:10px;border-radius:50%;width:30px;height:30px;padding:0;line-height:1;z-index:5;">
                            <i class="bi bi-x-lg" style="font-size:0.75rem;"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <input class="form-control" name="primary_image_url"
                           value="{{ old('primary_image_url', $product->primary_image_url ?? '') }}"
                           placeholder="Or paste primary image URL">
                </div>

                {{-- Additional Images --}}
                <label class="form-label fw-semibold mb-2">Additional Images</label>
                <div class="pf-thumb-grid mb-3" id="additional-images-grid">
                    @for ($i = 0; $i < 4; $i++)
                        @php
                            $existingImage = isset($product) && isset($product->images[$i]) ? $product->images[$i] : null;
                        @endphp
                        <div class="pf-thumb-slot" data-index="{{ $i }}">
                            <input type="hidden" name="additional_image_urls[]" id="additional-url-{{ $i }}" value="{{ $existingImage }}">
                            <input class="d-none additional-image-input" type="file" name="additional_images[]" id="additional-input-{{ $i }}" accept="image/*">

                            <label for="additional-input-{{ $i }}"
                                   class="w-100 h-100 d-flex align-items-center justify-content-center m-0 cursor-pointer {{ $existingImage ? 'd-none' : '' }}"
                                   id="additional-label-{{ $i }}">
                                <i class="bi bi-plus-lg text-muted"></i>
                            </label>

                            <div id="additional-preview-container-{{ $i }}"
                                 class="pf-thumb-overlay {{ $existingImage ? 'active' : '' }}">
                                <img id="additional-preview-img-{{ $i }}" class="w-100 h-100"
                                     style="object-fit:cover; border-radius:10px;"
                                     src="{{ $existingImage ?? '' }}">
                                <button type="button" class="btn btn-danger btn-sm btn-remove-additional position-absolute"
                                        data-index="{{ $i }}"
                                        style="top:5px;right:5px;z-index:10;border-radius:50%;width:24px;height:24px;padding:0;line-height:1;font-size:0.7rem;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    @endfor
                </div>

                <div class="pf-tip">
                    <i class="bi bi-lightbulb"></i>
                    <span>High-resolution lifestyle photography increases product confidence and conversion rates.</span>
                </div>
            </div>

        </div>{{-- /col-lg-4 --}}
    </div>

    {{-- ── Save Bar ─────────────────────────────────────────────────────── --}}
    <div class="pf-save-bar">
        <div>
            <strong class="d-block">{{ isset($product) ? 'Ready to update?' : 'Ready to publish?' }}</strong>
            <small class="text-muted">{{ isset($product) ? 'Your changes will be saved and reflected immediately.' : 'The product will be saved with the status you selected above.' }}</small>
        </div>
        <div class="d-flex gap-3">
            <a class="btn-ghost" href="{{ route('manager.products.index') }}">Cancel</a>
            <button class="btn-ash" type="submit">
                <i class="bi bi-check-lg"></i>
                {{ isset($product) ? 'Save Changes' : 'Publish Product' }}
            </button>
        </div>
    </div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sizesInput = document.getElementById('sizes-input');
    const colorsInput = document.getElementById('colors-input');
    const variantStockSection = document.getElementById('variant-stock-section');
    const variantStockTbody = document.getElementById('variant-stock-tbody');
    const mainStockInput = document.getElementById('stock_quantity_input');

    const initialVariantStock = @json(isset($product) && $product->variant_stock ? $product->variant_stock : new \stdClass());

    function updateVariantStockMatrix() {
        const sizes = (sizesInput.value || '').split(',').map(s => s.trim()).filter(s => s.length > 0);
        const colors = (colorsInput.value || '').split(',').map(c => c.trim()).filter(c => c.length > 0);

        if (sizes.length === 0 || colors.length === 0) {
            variantStockSection.style.display = 'none';
            variantStockTbody.innerHTML = '';
            if (mainStockInput) mainStockInput.removeAttribute('readonly');
            return;
        }

        variantStockSection.style.display = 'block';
        if (mainStockInput) mainStockInput.setAttribute('readonly', 'readonly');

        const currentValues = {};
        variantStockTbody.querySelectorAll('.variant-stock-input').forEach(input => {
            const comb = input.getAttribute('data-combination');
            const imgInput = variantStockTbody.querySelector(`.variant-image-input[data-combination="${comb}"]`);
            currentValues[comb] = { qty: input.value, image: imgInput ? imgInput.value : '' };
        });

        let html = '';
        colors.forEach(color => {
            sizes.forEach(size => {
                const comb = `${color} - ${size}`;
                let qty = 0, image = '';

                if (currentValues[comb] !== undefined) {
                    qty = currentValues[comb].qty;
                    image = currentValues[comb].image;
                } else if (initialVariantStock[comb] !== undefined) {
                    if (typeof initialVariantStock[comb] === 'object' && initialVariantStock[comb] !== null) {
                        qty   = initialVariantStock[comb].qty   !== undefined ? initialVariantStock[comb].qty   : 0;
                        image = initialVariantStock[comb].image !== undefined ? initialVariantStock[comb].image : '';
                    } else {
                        qty = initialVariantStock[comb];
                    }
                }

                html += `
                <tr>
                    <td><span class="vs-comb-badge">${comb}</span></td>
                    <td>
                        <input type="number"
                               name="variant_stock[${comb}][qty]"
                               data-combination="${comb}"
                               class="form-control variant-stock-input"
                               min="0" value="${qty}" required>
                    </td>
                    <td>
                        <input type="url"
                               name="variant_stock[${comb}][image]"
                               data-combination="${comb}"
                               class="form-control variant-image-input"
                               placeholder="https://…"
                               value="${image}">
                    </td>
                </tr>`;
            });
        });

        variantStockTbody.innerHTML = html;
        updateTotalStock();
    }

    function updateTotalStock() {
        const inputs = variantStockTbody.querySelectorAll('.variant-stock-input');
        if (inputs.length > 0) {
            let total = 0;
            inputs.forEach(i => total += parseInt(i.value || 0, 10));
            if (mainStockInput) mainStockInput.value = total;
        }
    }

    sizesInput.addEventListener('input', updateVariantStockMatrix);
    colorsInput.addEventListener('input', updateVariantStockMatrix);
    variantStockTbody.addEventListener('input', e => {
        if (e.target.classList.contains('variant-stock-input')) updateTotalStock();
    });

    // ── Primary image preview ────────────────────────────────
    const primaryInput     = document.getElementById('primary-image-input');
    const primaryUrlInput  = document.querySelector('input[name="primary_image_url"]');
    const primaryPreviewCt = document.getElementById('primary-preview-container');
    const primaryPreviewImg= document.getElementById('primary-preview-img');
    const btnRemovePrimary = document.getElementById('btn-remove-primary');
    const primaryUploadLbl = document.getElementById('primary-upload-label');

    function showPrimaryPreview(src) {
        if (primaryPreviewImg) primaryPreviewImg.src = src;
        if (primaryPreviewCt) { primaryPreviewCt.classList.remove('d-none'); primaryPreviewCt.classList.add('d-flex'); }
        if (primaryUploadLbl) primaryUploadLbl.classList.add('d-none');
    }
    function hidePrimaryPreview() {
        if (primaryPreviewImg) primaryPreviewImg.src = '';
        if (primaryPreviewCt) { primaryPreviewCt.classList.remove('d-flex'); primaryPreviewCt.classList.add('d-none'); }
        if (primaryUploadLbl) primaryUploadLbl.classList.remove('d-none');
    }

    if (primaryUrlInput && primaryUrlInput.value) showPrimaryPreview(primaryUrlInput.value);

    primaryInput?.addEventListener('change', e => {
        const file = e.target.files[0];
        if (file) { const r = new FileReader(); r.onload = ev => showPrimaryPreview(ev.target.result); r.readAsDataURL(file); }
    });

    primaryUrlInput?.addEventListener('input', () => {
        const val = primaryUrlInput.value.trim();
        if (val && (val.startsWith('http') || val.startsWith('/'))) showPrimaryPreview(val);
        else if (!val && (!primaryInput || !primaryInput.files.length)) hidePrimaryPreview();
    });

    btnRemovePrimary?.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        if (primaryInput)    primaryInput.value = '';
        if (primaryUrlInput) primaryUrlInput.value = '';
        hidePrimaryPreview();
    });

    // ── Additional image previews ────────────────────────────
    document.querySelectorAll('.pf-thumb-slot').forEach(slot => {
        const index    = slot.getAttribute('data-index');
        const fileInput= document.getElementById(`additional-input-${index}`);
        const urlInput = document.getElementById(`additional-url-${index}`);
        const prevCt   = document.getElementById(`additional-preview-container-${index}`);
        const prevImg  = document.getElementById(`additional-preview-img-${index}`);
        const label    = document.getElementById(`additional-label-${index}`);
        const btnRemove= slot.querySelector('.btn-remove-additional');

        fileInput?.addEventListener('change', e => {
            const file = e.target.files[0];
            if (file) {
                const r = new FileReader();
                r.onload = ev => {
                    if (prevImg)  prevImg.src = ev.target.result;
                    if (prevCt)   prevCt.classList.add('active');
                    if (label)    label.classList.add('d-none');
                    if (urlInput) urlInput.value = '';
                };
                r.readAsDataURL(file);
            }
        });

        btnRemove?.addEventListener('click', e => {
            e.preventDefault(); e.stopPropagation();
            if (fileInput) fileInput.value = '';
            if (urlInput)  urlInput.value  = '';
            if (prevImg)   prevImg.src     = '';
            if (prevCt)    prevCt.classList.remove('active');
            if (label)     label.classList.remove('d-none');
        });
    });

    updateVariantStockMatrix();
});
</script>
@endsection
