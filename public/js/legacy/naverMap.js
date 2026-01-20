// x:위도, y:경도, width:맵가로사이즈, height:맵세로사이즈, title_txt:라벨
// naver map v3 로 업그레이드 widht,height 는 그려지는 부모객체의 크기를 따라간다
function callMap(id_name,x,y,width,height,title_txt,zoom_use){

	var mapOptions = {
		center :				new naver.maps.LatLng(x, y),
		enableWheelZoom :		true,
		enableDragPan :			true,
		enableDblClickZoom :	false,
		activateTrafficMap :	false,
		activateBicycleMap :	false,
		mapOptions:				false,
		zoom :					11,
		mapMode :				0,
		minMaxLevel :			[ 1, 14 ],
        mapTypeControl :		true,
        mapTypeControlOptions : {
            style: naver.maps.MapTypeControlStyle.BUTTON,
            position: naver.maps.Position.TOP_RIGHT
        }
	};

	if	(zoom_use == 'Y') 
		mapOptions.zoomControl = true;
	
	var oMap = new naver.maps.Map(id_name, mapOptions);
	
	var marker = new naver.maps.Marker({
		position: new naver.maps.LatLng(x, y),
		map: oMap
	});

	if	(title_txt != '') {

		var market_info = new naver.maps.InfoWindow({
			content: title_txt
		});

		naver.maps.Event.addListener(marker, "click", function(e) {
			if (market_info.getMap()) {
				market_info.close();
			} else {
				market_info.open(oMap, marker);
			}
		});

		market_info.open(oMap, marker);

	}
}