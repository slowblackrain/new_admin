<form action="{{ route('admin.goods.brand.update', $brand->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="mb-3">
        <label class="form-label">Brand Name (KR)</label>
        <input type="text" name="title" class="form-control" value="{{ $brand->title }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Brand Name (ENG)</label>
        <input type="text" name="title_eng" class="form-control" value="{{ $brand->title_eng }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Brand Code</label>
        <input type="text" class="form-control" value="{{ $brand->category_code }}" readonly disabled>
        <small class="text-muted">Code: {{ $brand->category_code }} (Level: {{ $brand->level }})</small>
    </div>

    <div class="mb-3">
        <label class="form-label">Status</label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="hide" value="0" {{ $brand->hide == 0 ? 'checked' : '' }}>
                <label class="form-check-label">Show</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="hide" value="1" {{ $brand->hide == 1 ? 'checked' : '' }}>
                <label class="form-check-label">Hide</label>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Sort Order</label>
        <input type="number" name="position" class="form-control" value="{{ $brand->position }}">
    </div>

    <hr>
    <button type="submit" class="btn btn-primary">Save Changes</button>
</form>
