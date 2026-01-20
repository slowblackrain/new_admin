<ul>
    @foreach($categories as $cate)
        <li onclick="window.location.href='{{ route('goods.catalog', ['code' => $cate->category_code]) }}'">
            <span>{{ $cate->title }}</span>
        </li>
    @endforeach
</ul>