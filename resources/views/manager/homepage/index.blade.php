@extends('layouts.portal')

@section('title', 'Homepage Management | Ashvalian')

@section('content')
    <div class="portal-header mb-4">
        <div class="portal-title">
            <h1>Homepage Management</h1>
            <p class="fs-5 muted mt-2">Manage the 4 key sections of the storefront home page: Announcements, Banners, Products on Sale, and Trending Products.</p>
        </div>
        <a class="btn-ghost" href="{{ route('home') }}" target="_blank"><i class="bi bi-box-arrow-up-right"></i> View Storefront</a>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-pills mb-4 gap-2" id="homepageTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active px-4 py-2 fw-bold" id="products-tab" data-bs-toggle="tab" data-bs-target="#products-pane" type="button" role="tab" aria-controls="products-pane" aria-selected="true">
                <i class="bi bi-grid-3x3-gap me-2"></i>Product Sections
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2 fw-bold" id="banners-tab" data-bs-toggle="tab" data-bs-target="#banners-pane" type="button" role="tab" aria-controls="banners-pane" aria-selected="false">
                <i class="bi bi-images me-2"></i>Banners Slider
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2 fw-bold" id="announcements-tab" data-bs-toggle="tab" data-bs-target="#announcements-pane" type="button" role="tab" aria-controls="announcements-pane" aria-selected="false">
                <i class="bi bi-megaphone me-2"></i>Notice Bar (Announcements)
            </button>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="homepageTabsContent">
        
        {{-- TAB 1: PRODUCT SECTIONS --}}
        <div class="tab-pane fade show active" id="products-pane" role="tabpanel" aria-labelledby="products-tab" tabindex="0">
            <section class="dashboard-grid mb-4">
                <article class="panel panel-pad">
                    <div class="section-heading mb-3">
                        <h2 class="fw-black">Trending Products</h2>
                        <span class="status-pill blue">{{ $featuredProducts->count() }} active</span>
                    </div>
                    @forelse ($featuredProducts as $product)
                        <div class="d-flex gap-3 align-items-center p-2 mb-3 border rounded-2 bg-white">
                            <img class="rounded-2" style="width:58px;height:58px;object-fit:cover;" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}">
                            <div>
                                <strong class="text-black">{{ $product->name }}</strong>
                                <div class="small muted">Slot {{ $product->featured_sort_order ?: $loop->iteration }} - {{ $product->category?->name }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="muted">No trending products are selected.</p>
                    @endforelse
                </article>

                <article class="panel panel-pad">
                    <div class="section-heading mb-3">
                        <h2 class="fw-black">Products on Sale</h2>
                        <span class="status-pill red">{{ $saleProducts->count() }} sale picks</span>
                    </div>
                    @forelse ($saleProducts as $product)
                        <div class="d-flex gap-3 align-items-center p-2 mb-3 border rounded-2 bg-white">
                            <img class="rounded-2" style="width:58px;height:58px;object-fit:cover;" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}">
                            <div>
                                <strong class="text-black">{{ $product->name }}</strong>
                                <div class="small muted">Sale slot {{ $product->sale_sort_order ?: $loop->iteration }} - ৳{{ number_format($product->price, 2) }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="muted">No products on sale are selected.</p>
                    @endforelse
                </article>
            </section>

            <form method="post" action="{{ route('manager.homepage.update') }}" class="panel">
                @csrf
                @method('PATCH')
                <div class="panel-pad border-bottom">
                    <h2 class="fw-black mb-1">Assign Homepage Sections</h2>
                    <p class="muted mb-0">Select up to eight products for each storefront section. Product order follows the current list order.</p>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Trending (Featured)</th>
                                <th>On Sale</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex gap-3 align-items-center">
                                            <img class="rounded-2" style="width:58px;height:58px;object-fit:cover;" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}">
                                            <div>
                                                <strong class="text-black">{{ $product->name }}</strong>
                                                <div class="small muted">{{ $product->sku }} - {{ ucfirst($product->status) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="status-pill">{{ $product->category?->name }}</span></td>
                                    <td><strong class="{{ $product->is_low_stock ? 'text-danger' : '' }}">{{ $product->stock_quantity }}</strong></td>
                                    <td>
                                        <input class="form-check-input fs-4" type="checkbox" name="featured_product_ids[]" value="{{ $product->id }}" @checked($product->is_featured)>
                                    </td>
                                    <td>
                                        <input class="form-check-input fs-4" type="checkbox" name="sale_product_ids[]" value="{{ $product->id }}" @checked($product->is_on_sale)>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="panel-pad text-end">
                    <button class="btn-ash" type="submit">Publish Homepage Changes</button>
                </div>
            </form>
        </div>

        {{-- TAB 2: BANNERS --}}
        <div class="tab-pane fade" id="banners-pane" role="tabpanel" aria-labelledby="banners-tab" tabindex="0">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-black mb-0 text-black">Banner Slides</h2>
                <button class="btn-ash py-2" type="button" data-bs-toggle="collapse" data-bs-target="#addBannerCollapse" aria-expanded="false" aria-controls="addBannerCollapse">
                    <i class="bi bi-plus-lg me-1"></i> Add Banner Slide
                </button>
            </div>

            <!-- Create Banner Form -->
            <div class="collapse mb-4" id="addBannerCollapse">
                <form action="{{ route('manager.homepage.banners.store') }}" method="post" enctype="multipart/form-data" class="panel panel-pad bg-light border">
                    @csrf
                    <h4 class="fw-black mb-3 text-black">Add New Banner Slide</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Uncompromising Performance" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Subtitle</label>
                            <input type="text" name="subtitle" class="form-control" placeholder="e.g. Elite technical apparel engineered for athletes">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Banner Image File</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                            <div class="form-text text-muted">Upload a local image (Max 5MB).</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Banner Image URL</label>
                            <input type="url" name="image_url" class="form-control" placeholder="e.g. https://images.unsplash.com/...">
                            <div class="form-text text-muted">Or enter a remote image URL.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">CTA Button Label</label>
                            <input type="text" name="cta_label" class="form-control" placeholder="e.g. Shop Now">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">CTA Button URL</label>
                            <input type="text" name="cta_url" class="form-control" placeholder="e.g. /shop/fitness">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-dark">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-2 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="new_banner_active" checked>
                                <label class="form-check-label fw-bold text-dark" for="new_banner_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn-ash px-4 py-2">Create Banner Slide</button>
                    </div>
                </form>
            </div>

            <!-- Banners List -->
            <div class="row row-cols-1 row-cols-md-2 g-4">
                @forelse($banners as $banner)
                    <div class="col">
                        <div class="card bg-white border-light shadow-sm h-100 rounded-3 overflow-hidden">
                            <div class="position-relative" style="height: 200px;">
                                <img src="{{ $banner->image_url }}" class="w-100 h-100 object-fit-cover" alt="{{ $banner->title }}">
                                <span class="position-absolute top-0 end-0 m-2 badge {{ $banner->is_active ? 'bg-success' : 'bg-secondary' }} fs-6">
                                    {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="card-body p-4 text-dark">
                                <h4 class="card-title fw-black mb-1 text-black">{{ $banner->title }}</h4>
                                <p class="card-text text-muted mb-3">{{ $banner->subtitle ?: 'No subtitle provided.' }}</p>
                                <div class="border-top pt-3 small text-muted">
                                    <div class="mb-1"><strong>CTA Label:</strong> {{ $banner->cta_label ?: 'N/A' }}</div>
                                    <div class="mb-1"><strong>CTA URL:</strong> <code>{{ $banner->cta_url ?: 'N/A' }}</code></div>
                                    <div><strong>Sort Order:</strong> {{ $banner->sort_order }}</div>
                                </div>
                            </div>
                            <div class="card-footer bg-light border-0 d-flex gap-2 justify-content-end p-3">
                                <button class="btn btn-sm btn-outline-dark px-3" data-bs-toggle="modal" data-bs-target="#editBannerModal-{{ $banner->id }}">Edit</button>
                                <form action="{{ route('manager.homepage.banners.destroy', $banner->id) }}" method="post" onsubmit="return confirm('Are you sure you want to delete this banner slide?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Banner Modal -->
                    <div class="modal fade" id="editBannerModal-{{ $banner->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form action="{{ route('manager.homepage.banners.update', $banner->id) }}" method="post" enctype="multipart/form-data" class="modal-content text-start">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title fw-black text-black">Edit Banner Slide</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Title *</label>
                                            <input type="text" name="title" class="form-control" value="{{ $banner->title }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Subtitle</label>
                                            <input type="text" name="subtitle" class="form-control" value="{{ $banner->subtitle }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Banner Image File</label>
                                            <input type="file" name="image_file" class="form-control" accept="image/*">
                                            <div class="form-text text-muted">Upload a new image file to replace the current one.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Banner Image URL</label>
                                            <input type="url" name="image_url" class="form-control" value="{{ $banner->image_url }}">
                                            <div class="form-text text-muted">Or update the remote image URL.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">CTA Button Label</label>
                                            <input type="text" name="cta_label" class="form-control" value="{{ $banner->cta_label }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">CTA Button URL</label>
                                            <input type="text" name="cta_url" class="form-control" value="{{ $banner->cta_url }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold text-dark">Sort Order</label>
                                            <input type="number" name="sort_order" class="form-control" value="{{ $banner->sort_order }}" min="0">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end pb-2">
                                            <div class="form-check">
                                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="edit_banner_active-{{ $banner->id }}" @checked($banner->is_active)>
                                                <label class="form-check-label fw-bold text-dark" for="edit_banner_active-{{ $banner->id }}">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-ash">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-12 w-100 text-center py-5">
                        <div class="panel panel-pad text-muted">No banner slides configured. Add one above.</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- TAB 3: ANNOUNCEMENTS --}}
        <div class="tab-pane fade" id="announcements-pane" role="tabpanel" aria-labelledby="announcements-tab" tabindex="0">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-black mb-0 text-black">Notice Bar Announcements</h2>
                <button class="btn-ash py-2" type="button" data-bs-toggle="collapse" data-bs-target="#addAnnouncementCollapse" aria-expanded="false" aria-controls="addAnnouncementCollapse">
                    <i class="bi bi-plus-lg me-1"></i> Add Announcement
                </button>
            </div>

            <!-- Create Announcement Form -->
            <div class="collapse mb-4" id="addAnnouncementCollapse">
                <form action="{{ route('manager.homepage.announcements.store') }}" method="post" class="panel panel-pad bg-light border">
                    @csrf
                    <h4 class="fw-black mb-3 text-black">Add New Announcement</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Summer Release Promo" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Starts At</label>
                            <input type="datetime-local" name="starts_at" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark">Notification Message *</label>
                            <textarea name="message" class="form-control" rows="2" placeholder="e.g. 20% off all elite technical apparel using code ELITE24." required></textarea>
                            <div class="form-text text-muted">This text will scroll horizontally in the notice bar.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Ends At</label>
                            <input type="datetime-local" name="ends_at" class="form-control">
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="new_announcement_active" checked>
                                <label class="form-check-label fw-bold text-dark" for="new_announcement_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn-ash px-4 py-2">Create Announcement</button>
                    </div>
                </form>
            </div>

            <!-- Announcements List Table -->
            <div class="panel">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Announcement Info</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($announcements as $announcement)
                                <tr>
                                    <td>
                                        <div class="text-dark">
                                            <strong class="text-black fs-6">{{ $announcement->title }}</strong>
                                            <p class="mb-0 text-muted mt-1" style="max-width: 500px;">{{ $announcement->message }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><strong>Starts:</strong> {{ $announcement->starts_at ? $announcement->starts_at->format('M d, Y h:i A') : 'Immediately' }}</div>
                                            <div class="mt-1"><strong>Ends:</strong> {{ $announcement->ends_at ? $announcement->ends_at->format('M d, Y h:i A') : 'Indefinitely' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $announcement->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button class="btn btn-sm btn-outline-dark px-3" data-bs-toggle="modal" data-bs-target="#editAnnouncementModal-{{ $announcement->id }}">Edit</button>
                                            <form action="{{ route('manager.homepage.announcements.destroy', $announcement->id) }}" method="post" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger px-3">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Announcement Modal -->
                                <div class="modal fade" id="editAnnouncementModal-{{ $announcement->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <form action="{{ route('manager.homepage.announcements.update', $announcement->id) }}" method="post" class="modal-content text-start">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-black text-black">Edit Announcement</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-dark">Title *</label>
                                                        <input type="text" name="title" class="form-control" value="{{ $announcement->title }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-dark">Starts At</label>
                                                        <input type="datetime-local" name="starts_at" class="form-control" value="{{ $announcement->starts_at ? $announcement->starts_at->format('Y-m-d\TH:i') : '' }}">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-bold text-dark">Notification Message *</label>
                                                        <textarea name="message" class="form-control" rows="2" required>{{ $announcement->message }}</textarea>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-dark">Ends At</label>
                                                        <input type="datetime-local" name="ends_at" class="form-control" value="{{ $announcement->ends_at ? $announcement->ends_at->format('Y-m-d\TH:i') : '' }}">
                                                    </div>
                                                    <div class="col-md-6 d-flex align-items-end pb-2">
                                                        <div class="form-check">
                                                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="edit_announcement_active-{{ $announcement->id }}" @checked($announcement->is_active)>
                                                            <label class="form-check-label fw-bold text-dark" for="edit_announcement_active-{{ $announcement->id }}">Active</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-ash">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No announcements configured. Add one above.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
