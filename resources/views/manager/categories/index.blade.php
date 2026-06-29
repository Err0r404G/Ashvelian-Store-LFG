@extends('layouts.portal')

@section('title', 'Categories | Ashvalian')

@section('content')
    <style>
        .uppercase-badge {
            font-size: 10px !important;
            font-weight: 700 !important;
            letter-spacing: 0.05em;
            padding: 4px 8px !important;
        }
        .category-row td, .subcategory-row td {
            padding: 16px 20px !important;
            border-top: 1px solid #f2f2f2 !important;
        }
        .btn-toggle-row {
            transition: all 0.2s ease;
        }
        .btn-toggle-row:hover {
            background-color: #e9ecef !important;
            color: #000 !important;
        }
    </style>
    <div class="portal-header"><div class="portal-title"><h1>Categories & Subcategories</h1><p class="fs-5 muted mt-2">Manage product platform-level navigation.</p></div></div>

    <section class="panel panel-pad mb-4">
        <form method="post" action="{{ route('manager.categories.store') }}" class="row g-3">
            @csrf
            <div class="col-md-4"><input class="form-control" name="name" placeholder="Category name" required></div>
            <div class="col-md-4">
                <select class="form-select" name="parent_id">
                    <option value="">Top-level category</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input class="form-control" name="sort_order" type="number" value="0"></div>
            <div class="col-md-2 form-check form-switch pt-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked> Active</div>
            <div class="col-12"><textarea class="form-control" name="description" rows="2" placeholder="Description"></textarea></div>
            <div class="col-12"><button class="btn-ash" type="submit">Create Category</button></div>
        </form>
    </section>

    <section class="panel mb-4">
        <div class="panel-header d-flex justify-content-between align-items-center p-3 border-bottom bg-white rounded-top">
            <h2 class="h4 mb-0 fw-bold text-dark">Summary</h2>
            <form method="get" class="d-flex align-items-center" style="max-width: 320px;">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input class="form-control border-start-0" name="q" value="{{ request('q') }}" placeholder="Search category...">
                </div>
            </form>
        </div>
        <table class="data-table mb-0">
            <thead>
                <tr>
                    <th style="width: 50%;">Category Name</th>
                    <th class="text-center" style="width: 15%;">Total Products</th>
                    <th class="text-center" style="width: 15%;">Sold Product</th>
                    <th class="text-end pe-4" style="width: 20%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    @php
                        // In memory collection operations using eager-loaded relationships
                        $directProducts = $category->products;
                        $directProductsCount = $directProducts->count();
                        $directSold = $directProducts->sum(fn($p) => $p->orderItems->sum('quantity'));

                        $children = $category->children;
                        $subcatCount = $children->count();

                        $totalProducts = $directProductsCount;
                        $totalSold = $directSold;

                        foreach ($children as $child) {
                            $childProducts = $child->products;
                            $totalProducts += $childProducts->count();
                            $totalSold += $childProducts->sum(fn($p) => $p->orderItems->sum('quantity'));
                        }
                    @endphp
                    <tr class="category-row bg-white align-middle">
                        <td>
                            <div class="d-flex align-items-center">
                                @if($subcatCount > 0)
                                    <button class="btn-toggle-row btn btn-sm bg-light text-secondary rounded p-1 me-2 border-0" type="button" data-target-class="subcategory-row-for-{{ $category->id }}" aria-expanded="false" style="width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-chevron-right toggle-icon"></i>
                                    </button>
                                @else
                                    <!-- A spacer matching the toggle button size -->
                                    <span class="d-inline-block me-2" style="width: 30px;"></span>
                                @endif
                                <strong class="fs-6 text-dark fw-bold category-name-click" style="cursor: pointer;" data-target-class="subcategory-row-for-{{ $category->id }}">{{ $category->name }}</strong>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2 px-2 py-1 uppercase-badge" style="font-size: 10px; font-weight: 700; letter-spacing: 0.05em;">PRIMARY</span>
                                <span class="text-muted small ms-2">{{ $subcatCount }} {{ Str::plural('subcategory', $subcatCount) }}</span>
                            </div>
                        </td>
                        <td class="text-center fw-semibold">{{ $totalProducts }}</td>
                        <td class="text-center fw-semibold">{{ $totalSold }}</td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-3 justify-content-end align-items-center">
                                <button class="btn btn-sm btn-ghost p-1 border-0" type="button" data-bs-toggle="modal" data-bs-target="#addSubcategory{{ $category->id }}" title="Add Subcategory">
                                    <i class="bi bi-plus-lg text-dark fs-5"></i>
                                </button>
                                <button class="btn btn-sm btn-ghost p-1 border-0" type="button" data-bs-toggle="modal" data-bs-target="#editCategory{{ $category->id }}" title="Edit Category">
                                    <i class="bi bi-pencil text-dark fs-6"></i>
                                </button>
                                <form method="post" action="{{ route('manager.categories.destroy', $category) }}" onsubmit="return confirm('Are you sure you want to delete this category?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-ghost p-1 border-0" type="submit" aria-label="Delete {{ $category->name }}" title="Delete Category">
                                        <i class="bi bi-trash text-dark fs-6"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- Render subcategories --}}
                    @foreach ($children as $child)
                        @php
                            $childProducts = $child->products;
                            $childProductsCount = $childProducts->count();
                            $childSold = $childProducts->sum(fn($p) => $p->orderItems->sum('quantity'));
                        @endphp
                        <tr class="subcategory-row-for-{{ $category->id }} subcategory-row bg-white align-middle d-none border-top">
                            <td class="ps-5">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="text-muted font-monospace me-2">|</span>
                                    <i class="bi bi-arrow-return-right text-muted me-2"></i>
                                    <span class="fs-6 text-dark fw-semibold">{{ $child->name }}</span>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-2 px-2 py-1 uppercase-badge" style="font-size: 10px; font-weight: 700; letter-spacing: 0.05em;">SUBCATEGORY</span>
                                    
                                    @if ($childProductsCount > 0)
                                        <button class="btn btn-sm text-primary py-0 px-2 ms-2 border-0 btn-toggle-products" type="button" data-bs-toggle="collapse" data-bs-target="#productsCollapse{{ $child->id }}" aria-expanded="false" aria-controls="productsCollapse{{ $child->id }}" style="font-size: 11px;">
                                            <i class="bi bi-box-seam me-1"></i> View Products ({{ $childProductsCount }})
                                        </button>
                                    @endif
                                </div>
                                
                                @if ($childProductsCount > 0)
                                    <div class="collapse" id="productsCollapse{{ $child->id }}">
                                        <div class="ps-4 ms-2 mt-2 border-start py-1">
                                            <div class="text-muted small fw-bold mb-1">Products:</div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach ($childProducts as $product)
                                                    <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                                                        <i class="bi bi-box text-secondary" style="font-size: 10px;"></i>
                                                        {{ $product->name }}
                                                        <span class="text-muted small">({{ $product->sku }})</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="ps-4 ms-2 mt-1 small text-muted font-italic">No products listed</div>
                                @endif
                            </td>
                            <td class="text-center text-muted">{{ $childProductsCount }}</td>
                            <td class="text-center text-muted">{{ $childSold }}</td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-3 justify-content-end align-items-center">
                                    <button class="btn btn-sm btn-ghost p-1 border-0" type="button" data-bs-toggle="modal" data-bs-target="#editCategory{{ $child->id }}" title="Edit Subcategory">
                                        <i class="bi bi-pencil text-dark fs-6"></i>
                                    </button>
                                    <form method="post" action="{{ route('manager.categories.destroy', $child) }}" onsubmit="return confirm('Are you sure you want to delete this subcategory?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-ghost p-1 border-0" type="submit" aria-label="Delete {{ $child->name }}" title="Delete Subcategory">
                                            <i class="bi bi-trash text-danger fs-6"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="panel-pad p-3 border-top bg-white rounded-bottom d-flex justify-content-between align-items-center">
            <span class="text-muted small">Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} main categories</span>
            <div>{{ $categories->links() }}</div>
        </div>
    </section>

    @foreach ($categories as $category)
        <div class="modal fade" id="editCategory{{ $category->id }}" tabindex="-1" aria-labelledby="editCategory{{ $category->id }}Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content category-edit-modal">
                    <form method="post" action="{{ route('manager.categories.update', $category) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <div>
                                <div class="mini-label">Edit Category</div>
                                <h2 class="modal-title fw-black" id="editCategory{{ $category->id }}Label">{{ $category->name }}</h2>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="category-name-{{ $category->id }}">Name</label>
                                    <input id="category-name-{{ $category->id }}" class="form-control" name="name" value="{{ $category->name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="category-parent-{{ $category->id }}">Parent</label>
                                    <select id="category-parent-{{ $category->id }}" class="form-select" name="parent_id">
                                        <option value="">Top-level category</option>
                                        @foreach ($parents as $parent)
                                            @if ($parent->id !== $category->id)
                                                <option value="{{ $parent->id }}" @selected($category->parent_id === $parent->id)>{{ $parent->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" for="category-sort-{{ $category->id }}">Sort Order</label>
                                    <input id="category-sort-{{ $category->id }}" class="form-control" name="sort_order" type="number" min="0" value="{{ $category->sort_order }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="category-description-{{ $category->id }}">Description</label>
                                    <textarea id="category-description-{{ $category->id }}" class="form-control" name="description" rows="4">{{ $category->description }}</textarea>
                                </div>
                                <div class="col-12">
                                    <input type="hidden" name="is_active" value="0">
                                    <label class="form-check form-switch d-inline-flex gap-2 align-items-center">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($category->is_active)>
                                        <span>Active category</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn-ash" type="submit">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addSubcategory{{ $category->id }}" tabindex="-1" aria-labelledby="addSubcategory{{ $category->id }}Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content category-edit-modal">
                    <form method="post" action="{{ route('manager.categories.store') }}">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $category->id }}">
                        <div class="modal-header">
                            <div>
                                <div class="mini-label">New Subcategory</div>
                                <h2 class="modal-title fw-black" id="addSubcategory{{ $category->id }}Label">Add Subcategory to {{ $category->name }}</h2>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="sub-category-name-{{ $category->id }}">Subcategory Name</label>
                                    <input id="sub-category-name-{{ $category->id }}" class="form-control" name="name" placeholder="e.g. Router, Switch..." required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Parent Category</label>
                                    <input class="form-control" value="{{ $category->name }}" disabled>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" for="sub-category-sort-{{ $category->id }}">Sort Order</label>
                                    <input id="sub-category-sort-{{ $category->id }}" class="form-control" name="sort_order" type="number" min="0" value="0">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="sub-category-description-{{ $category->id }}">Description</label>
                                    <textarea id="sub-category-description-{{ $category->id }}" class="form-control" name="description" rows="4" placeholder="Description"></textarea>
                                </div>
                                <div class="col-12">
                                    <input type="hidden" name="is_active" value="0">
                                    <label class="form-check form-switch d-inline-flex gap-2 align-items-center">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                        <span>Active category</span>
                                    </label>
                                </div>
                            </div>
                        </div>
        </div>

        @foreach ($category->children as $child)
            <div class="modal fade" id="editCategory{{ $child->id }}" tabindex="-1" aria-labelledby="editCategory{{ $child->id }}Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content category-edit-modal">
                        <form method="post" action="{{ route('manager.categories.update', $child) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <div>
                                    <div class="mini-label">Edit Subcategory</div>
                                    <h2 class="modal-title fw-black" id="editCategory{{ $child->id }}Label">{{ $child->name }}</h2>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="category-name-{{ $child->id }}">Name</label>
                                        <input id="category-name-{{ $child->id }}" class="form-control" name="name" value="{{ $child->name }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="category-parent-{{ $child->id }}">Parent</label>
                                        <select id="category-parent-{{ $child->id }}" class="form-select" name="parent_id">
                                            <option value="">Top-level category</option>
                                            @foreach ($parents as $parent)
                                                @if ($parent->id !== $child->id)
                                                    <option value="{{ $parent->id }}" @selected($child->parent_id === $parent->id)>{{ $parent->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label" for="category-sort-{{ $child->id }}">Sort Order</label>
                                        <input id="category-sort-{{ $child->id }}" class="form-control" name="sort_order" type="number" min="0" value="{{ $child->sort_order }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="category-description-{{ $child->id }}">Description</label>
                                        <textarea id="category-description-{{ $child->id }}" class="form-control" name="description" rows="4">{{ $child->description }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <input type="hidden" name="is_active" value="0">
                                        <label class="form-check form-switch d-inline-flex gap-2 align-items-center">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($child->is_active)>
                                            <span>Active category</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn-ash" type="submit">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addSubcategory{{ $child->id }}" tabindex="-1" aria-labelledby="addSubcategory{{ $child->id }}Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content category-edit-modal">
                        <form method="post" action="{{ route('manager.categories.store') }}">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $child->id }}">
                            <div class="modal-header">
                                <div>
                                    <div class="mini-label">New Subcategory</div>
                                    <h2 class="modal-title fw-black" id="addSubcategory{{ $child->id }}Label">Add Subcategory to {{ $child->name }}</h2>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="sub-category-name-{{ $child->id }}">Subcategory Name</label>
                                        <input id="sub-category-name-{{ $child->id }}" class="form-control" name="name" placeholder="e.g. Router, Switch..." required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Parent Category</label>
                                        <input class="form-control" value="{{ $child->name }}" disabled>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label" for="sub-category-sort-{{ $child->id }}">Sort Order</label>
                                        <input id="sub-category-sort-{{ $child->id }}" class="form-control" name="sort_order" type="number" min="0" value="0">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="sub-category-description-{{ $child->id }}">Description</label>
                                        <textarea id="sub-category-description-{{ $child->id }}" class="form-control" name="description" rows="4" placeholder="Description"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <input type="hidden" name="is_active" value="0">
                                        <label class="form-check form-switch d-inline-flex gap-2 align-items-center">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                            <span>Active category</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn-ash" type="submit">Create Subcategory</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleSubcategories = function (targetClass, triggerEl) {
                const subRows = document.querySelectorAll('.' + targetClass);
                const isExpanded = triggerEl.getAttribute('aria-expanded') === 'true';
                
                subRows.forEach(row => {
                    if (isExpanded) {
                        row.classList.add('d-none');
                    } else {
                        row.classList.remove('d-none');
                    }
                });
                
                triggerEl.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                const icon = triggerEl.querySelector('.toggle-icon');
                if (icon) {
                    if (isExpanded) {
                        icon.classList.remove('bi-chevron-down');
                        icon.classList.add('bi-chevron-right');
                    } else {
                        icon.classList.remove('bi-chevron-right');
                        icon.classList.add('bi-chevron-down');
                    }
                }
            };

            document.querySelectorAll('.btn-toggle-row').forEach(btn => {
                btn.addEventListener('click', function () {
                    const targetClass = btn.getAttribute('data-target-class');
                    toggleSubcategories(targetClass, btn);
                });
            });

            document.querySelectorAll('.category-name-click').forEach(nameEl => {
                nameEl.addEventListener('click', function () {
                    const targetClass = nameEl.getAttribute('data-target-class');
                    const triggerEl = nameEl.closest('td').querySelector('.btn-toggle-row');
                    if (triggerEl) {
                        toggleSubcategories(targetClass, triggerEl);
                    }
                });
            });
        });
    </script>
@endsection
