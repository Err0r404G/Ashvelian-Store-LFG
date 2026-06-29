@if (session('status'))
    <div class="alert alert-success border-0 rounded-2 shadow-sm">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger border-0 rounded-2 shadow-sm">
        {{ $errors->first() }}
    </div>
@endif
