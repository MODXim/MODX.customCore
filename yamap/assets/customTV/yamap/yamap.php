<?
	global $modx;
	$p_ymkey = $modx->getChunk('yamap_key');
?>
<script src="<?=$p_ymkey;?>" type="text/javascript"></script>
<script type="text/javascript"> 
// <!-- 

// Определение координат по адресам (Яндекс.Карты) 
function showAddress (value, tvid) {
        var geocoder = new YMaps.Geocoder(value, {results: 1});
        YMaps.Events.observe(geocoder, geocoder.Events.Load, function(){
                if(this.length()) {
                        var geoResult = this.get(0);
                        document.getElementById("tv"+tvid).value = geoResult.getGeoPoint().toString();
                        map[tvid].setBounds(geoResult.getBounds());                      
                        placemark[tvid].setGeoPoint(geoResult.getGeoPoint());
                        setBalloonInfo(placemark, geoResult.getGeoPoint(), geoResult.text, tvid);
                        placemark[tvid].openBalloon();
                } else {alert("Ничего не найдено");}
        });
}
        
function setBalloonInfo (placemark, geoPoint, text, tvid) {
        var content = "Объект";
        if(text) content = text;
        placemark[tvid].setBalloonContent(content);
        document.getElementById("tv"+tvid).value = geoPoint.toString();
}

var map=[]; 
var placemark=[]; 
var pointCenter=[];
// -->
</script>

<?
if(!trim($field_value)) 
        $tv_val = $default_text;
else
        $tv_val = $field_value;
$addr_def = 'Новосибирск, ';
?>
<input type="text" id="tv<?php echo $field_id;?>" name="tv<?php echo $field_id;?>" value="<?php echo $field_value;?>"/>
<input type="text" id="tv<?php echo $field_id;?>Addr" value="<?=$addr_def;?>"/> <input id="tv<?php echo $field_id;?>Submit" type="button" value=" → " onclick="showAddress(document.getElementById('tv<?php echo $field_id;?>Addr').value, <?php echo $field_id;?>);"/>

<div id="tv<?php echo $field_id;?>Ymap" style="width:500px;height:360px; border: 1px solid #ccc;"></div>

<script type="text/javascript"> // Определение координат по адресам (Яндекс.Карты) 
tvid = <?=$field_id;?>;
tvcoords = document.getElementById("tv"+tvid).value.split(",",2);
pointCenter[tvid] = new YMaps.GeoPoint( tvcoords[0],tvcoords[1] );
map[tvid] = new YMaps.Map(document.getElementById("tv"+tvid+"Ymap"));
map[tvid].setCenter(pointCenter[tvid], 10);
map[tvid].enableScrollZoom();
map[tvid].addControl(new YMaps.Zoom());
map[tvid].addControl(new YMaps.ToolBar());
//map[tvid].addControl(new YMaps.TypeControl());
YMaps.MapType.PMAP.getName = function () { return "Народная"; }
map[tvid].addControl(new YMaps.TypeControl([
            YMaps.MapType.MAP,
            YMaps.MapType.SATELLITE,
            YMaps.MapType.HYBRID,
            YMaps.MapType.PMAP
], [0, 1, 2, 3]));
//map[tvid].addControl(new YMaps.SearchControl());

placemark[tvid] = new YMaps.Placemark(pointCenter[tvid], {draggable: true, hideIcon: false});
setBalloonInfo(placemark, pointCenter[tvid], "", tvid);
map[tvid].addOverlay(placemark[tvid]);

YMaps.Events.observe(placemark[tvid], placemark[tvid].Events.Drag, function (mEvent) {
        setBalloonInfo(placemark, mEvent.getGeoPoint(), "", tvid);
});

YMaps.Events.observe(map[tvid], map[tvid].Events.Click, function (mEvent) {
        var newGeoPoint = mEvent.getGeoPoint();
        placemark[tvid].setGeoPoint(newGeoPoint);
        setBalloonInfo(placemark, newGeoPoint, "", tvid);
});
</script>