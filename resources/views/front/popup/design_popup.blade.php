<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>{{ $popup->title }}</title>
    <style>
        body { margin:0; padding:0; overflow:hidden; }
        .designPopupBar {
            height: 25px;
            overflow: hidden;
            background-color: {{ $popup->bar_background_color }};
            color: #fff; /* Default white, adjust via style if needed */
            font-size: 12px;
            line-height: 25px;
            padding: 0 10px;
        }
        .designPopupBar label { cursor: pointer; }
        .designPopupTodaymsg { float: left; }
        .designPopupClose { float: right; cursor: pointer; font-weight: bold; }
        .designPopupContent img { vertical-align: top; border: 0; }
        
        /* Banner Styles (Simplified) */
        .popup-banner-container { position: relative; width: 100%; height: 100%; }
        .popup-banner-item { display: none; position: absolute; top: 0; left: 0; }
        .popup-banner-item.active { display: block; }
    </style>
    <script>
    function closePopup(today) {
        if (today) {
            // Set cookie for 1 day
            var date = new Date();
            date.setTime(date.getTime() + (24 * 60 * 60 * 1000));
            document.cookie = "designPopup" + {{ $popup->popup_seq }} + "=done; expires=" + date.toUTCString() + "; path=/";
        }
        self.close();
    }
    </script>
</head>
<body>

<div class="designPopupBody">
    <div class="designPopupContent">
        @if($popup->contents_type == 'image')
            @if($popup->link)
                <a href="{{ $popup->link }}" target="{{ $popup->target ?? '_opener' }}" onclick="self.close()">
            @endif
            <img src="/data/popup/{{ $popup->image }}" style="width:{{ $popup->width }}px; height:{{ $popup->height }}px;" onerror="this.src='/images/no_image.gif'">
            @if($popup->link)
                </a>
            @endif
        @elseif($popup->contents_type == 'text')
            {!! $popup->contents !!}
        @elseif($banner)
            {{-- Simple Banner Implementation --}}
            <div class="popup-banner-container" style="width:{{ $banner->image_width }}px; height:{{ $banner->image_height }}px;">
                @foreach($bannerItems as $index => $item)
                    <div class="popup-banner-item {{ $index == 0 ? 'active' : '' }}">
                        <a href="{{ $item->link }}" target="{{ $item->target ?? '_blank' }}">
                            <img src="/data/popup/{{ $item->image }}" style="width:100px; height:auto;">
                        </a>
                    </div>
                @endforeach
            </div>
            {{-- Note: Full slider logic omitted for simplicity unless requested --}}
        @endif
    </div>
    
    <div class="designPopupBar" style="background-color:{{ $popup->bar_background_color }};">
        <div class="designPopupTodaymsg">
            <label onclick="closePopup(true)">
                <input type="checkbox"> {{ $popup->bar_msg_today_text }}
            </label>
        </div>
        <div class="designPopupClose" onclick="closePopup(false)">
            {{ $popup->bar_msg_close_text }}
        </div>
    </div>
</div>

</body>
</html>
