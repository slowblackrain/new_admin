<h1>Global Categories</h1>
<ul>
    @foreach($globalCategories as $cate)
        <li>{{ $cate->title }} ({{ $cate->category_code }})</li>
    @endforeach
</ul>