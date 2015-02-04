<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
        <script src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
        <script type='text/javascript' src="http://maps.google.com/maps/api/js?sensor=false&libraries=geometry"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
        <title>Localizador de direcciones.</title>
        <style type="text/css">
            html, body, #map-canvas {
                height: 100%;
                margin: 0px;
                padding: 0px
            }
            div#map{height: 100%; width: 100%;}
            div#log{overflow-y:auto; height:800px; min-height: 100%;}
            p.ok{ color: green; }
            p.marker{ border: 1px solid #ccc; }
            p.title{ font-weight: bold;}
            p.search{ padding-left: 20px;}
            p.error{ color: red; padding-left: 5px;}
            div.panel dl{margin-bottom: 0px;}
            div.panel dt{ width: 120px; display: inline-block; vertical-align: top;}
            div.panel dd{ display: inline-block; padding: 0px; }
            div.panel .bg-success{ clear: both; padding-top: 10px;}
            .no-padding{padding: 0px;}
            form .row{ margin-bottom: 5px;}
        </style>
        <script type="text/javascript">
            var urlGerAddress = 'googlemap.getAddress.php';
            var debug = true;
            var draggable = false;

            var map;
            var geocoder;
            var infowindow;
            var address;
            var title;
            var cont = 0;
            var markers = [];
            var minDistance = 250; // 250m
            var iconsBasePath = 'http://maps.google.com/mapfiles/ms/icons/';
            var iconsBasePath2 = 'http://maps.google.com/mapfiles/kml/pal3/';
            var markersChanged = [];

            function writeLog(data, style){
                $('#log').append("<p class='" + style  + "'>" + data + "</p>");
            }

            function initMap(){
                writeLog('Cargando Mapa');

                var latlng = new google.maps.LatLng(43.34914966389313, -8.41827392578125);
                var mapOptions = {
                    zoom: 8,
                    center: latlng
                };

                geocoder = new google.maps.Geocoder();
                infowindow = new google.maps.InfoWindow();
                map = new google.maps.Map(document.getElementById('map'), mapOptions);

            }

            /**
             * Carga las direcciones a través de una petición.
             * @returns {undefined}
             */
            function loadAddress(){
                $.getJSON(urlGerAddress, {'opcion': 'getData'}, function() {
                    writeLog('Peticion ok', 'ok');
                }).done(function(data){
                    address = data.data;
                    $('#info_head').html(data.title);
                    writeLog(Object.keys(address).length + ' direcciones cargadas correctamente'), 'ok';
                    renderMarkers();
                }).fail(function(){
                   writeLog('Error cargando direcciones', 'error');
                });
            }

            /**
             * Devuelve la siguiente dirección
             * @returns {Object|null}
             */
            function getNextItem(){
                var tmp = address[Object.keys(address)[cont]];
                return (tmp !== undefined)?tmp:null;
            }

            /**
             * Pinta en el mapa las posiciones.
             * @returns {undefined}
             */
            function renderMarkers(){
                var currentItem = getNextItem();
                if (currentItem !== null && currentItem !== undefined){
                    if (typeof currentItem.lat !== "undefined" && parseFloat(currentItem.lat) !== 0 && parseFloat(currentItem.lon) !== 0 ){
                        var addressLatLon = new google.maps.LatLng(currentItem.lat, currentItem.lon);
                        log("se añade: "+currentItem.address+" en la posicion:  "+currentItem.lat+","+currentItem.lon);
                        addMarker(addressLatLon, currentItem.address, currentItem.itemId, true);
                    }else{
                        var addressItem = currentItem.address;
                        var localityItem = currentItem.locality;
                        $.getJSON('https://maps.googleapis.com/maps/api/geocode/json?',{
                            'key': 'AIzaSyCq1u8AIeMoG_g7q2keWHUsPAvDpOLUllo', 'address': addressItem, 'componentRestrictions':{'country':'ES', 'locality': localityItem }
                        }).done(function(data){
                            var firstItem = 0;
                            if (data.status == 'OK'){
                                $.each(data.results, function(key, value){
                                    currentItem.lat = value.geometry.location.lat;
                                    currentItem.lon = value.geometry.location.lng;
                                    log('Se ha buscado '+addressItem+' y se ha encontrado en '+currentItem.lat+","+currentItem.lon)
                                    addMarker(value.geometry.location, addressItem, currentItem.itemId, false, firstItem );
                                    firstItem ++;
                                });
                            }else{
                                writeLog('No se ha encontrado la dirección ' + currentItem.address, 'error');
                                cont++;
                                renderMarkers();
                            }
                        }).fail(function( jqxhr, textStatus, error ) {
                                var err = textStatus + ", " + error;
                                writeLog( "Request Failed: " + err , 'error');
                                cont++;
                                renderMarkers();
                        });
                    }
                }
            }
            /**
             * Añade un marcador al mapa
             * @returns {undefined}
             */
            function addMarker(location, text, itemId, existente, moreResults){
                var image =  iconsBasePath + "red.png";
                if (existente){
                    image =  iconsBasePath + "ylw-pushpin.png";
                }
                if (moreResults > 0){
                    image =  iconsBasePath2 + "icon59.png";
                }

                marker = new google.maps.Marker({
                    id: itemId,
                    map: map,//el mapa creado en el paso anterior
                    position: location,//objeto con latitud y longitud
                    draggable: draggable, //que el marcador se pueda arrastrar
                    animation: google.maps.Animation.DROP,
                    title: text,
                    cursor: cont.toString(),
                    icon: image
                });
                markers[cont] = marker;
                var nuevo = (existente)?' - Existente':'';
                writeLog("<p>" + text+"<br/><a href='javascript:getMarker(\"" + cont + "\");'>Marcador a&ntilde;adido</a>" +nuevo+ "</p>", "marker");

                google.maps.event.addListener(marker, 'click', function() {
                    infowindow.open(map, this);
                    var itemId = this.id;
                    $.get( urlGerAddress + "?opcion=info&itemId=" + itemId, function( data ) {
                        infowindow.setContent( data );
                        getNearby(itemId, true);
                        getSameDirection(itemId, true);
                    });
                });

                google.maps.event.addListener(marker, 'dragstart', function() {
                    infowindow.close();
                });
                google.maps.event.addListener(marker, 'dragend', function(obj) {
                    this.position = new google.maps.LatLng(obj.latLng.lat(), obj.latLng.lng());
                    var newIcon = iconsBasePath2 + "icon37.png";
                    this.setIcon(newIcon);
                    /*
                     * Creamos un nuevo objecto para almacenar los datos
                     * del marcador que acabamos de cambiar.
                     */
                    var markchanged = new Object();
                    markchanged.id = this.id;
                    markchanged.lng = obj.latLng.lng();
                    markchanged.lat = obj.latLng.lat();
                    markersChanged.push(markchanged);

                    $('#changesNum').html(markersChanged.length);
                });

                cont++;
                renderMarkers();
            }

            /**
             * Realiza el evento click de un marcador según su localizacion
             * @returns {undefined}
             */
            function getMarker(location){
                google.maps.event.trigger(markers[location], 'click');
            }

            /**
             * Busca en las direcciones por el id indicado
             * @param {Double} id
             * @returns {Object}
             */
            function getAddressById(id){
                var result = null;
                $.each(address,function(key, value){
                    if (key === id){
                        result = value;
                    }
                });
                if (result !== null){
                    return result[0];
                }
                return null;
            }

            function getMarkerById(id){
                var result = null;
                for (var i = 0; i<markers.length; i++){
                    if (markers[i].id === id){
                        result = markers[i];
                    }
                };
                return result;
            }


            /**
             * Busca los puntos cercanos al indicado
             * @param {String} itemId
             * @returns {array}
             */
            function getNearby(itemId, returnData){
                log('Buscando cerca de '+itemId);
                var cercanos = [];
                var posActual = getAddressById(itemId);
                if (posActual !== null){
                    $.each(markers,function(){
                        if (itemId != this.id){
                            var pos = new google.maps.LatLng(posActual.lat, posActual.lon);
                            var distance = google.maps.geometry.spherical.computeDistanceBetween(this.getPosition(),pos);
                            if (distance < minDistance){
                                var meters = distance / 1000;
                                this.distance = meters.toFixed(2)+" km";
                                cercanos.push(this);
                            }
                        }
                    });
                }
                cercanos.sort(function(a,b){ return parseFloat(a.distance) - parseFloat(b.distance) });
                if (typeof returnData !== 'undefined'){
                    log("Se han encontrado cercanos" + cercanos.length);
                    $('#badge_nearby').html(cercanos.length);
                }else{
                    console.log(cercanos);
                    renderItems("Próximas a la direción actual", cercanos);
                }
            }
            /**
             * Búsqueda de direcciones en las mismas coordenadas.
             * @returns {undefined}
             */
            function getSameDirection(itemId, returnData){
                var sameDirection = [];
                var posActual = getAddressById(itemId);
                if (posActual !== null){
                    $.each(markers,function(){
                        var posActual_coord = new google.maps.LatLng(posActual.lat, posActual.lon);
                        var marker_coord = this.getPosition();
                        if(posActual_coord.equals(marker_coord)){
                            if (itemId !== this.id){
	                            sameDirection.push(this);
                            }
                        }
                    });
                }
                if (typeof returnData !== 'undefined'){
                    log("Se han encontrado en la misma dirección" + sameDirection.length);
                    $('#badge_sameDirection').html(sameDirection.length);
                }else{
                    renderItems("En la misma dirección", sameDirection);
                }
            }
            /**
             * Renderiza las ordenes en el panel de información
             * @param {string} title
             * @param {string} data
             */
            function renderItems(title, data){
                var result = '<div class="panel panel-info"><div class="panel-heading">' + title + '</div><div class="panel-body">';
                if (data.length > 0){
                    result += "<ul>";
                    $.each(data,function(){
                        result += "<li><a href='javascript:getMarker(" + this.cursor + ");'>"+this.getTitle()+" ("+this.distance+")</a>";
                    });
                    result += "</ul>";
                }else{
                    result += "<p>No se han encontrado resultados</p>";
                }
                result += '</div></div>';
                $('#addAppointment').hide();
                $('#moreInfo').show().html(result);
                resizeInfoWindow();
            }
            /**
             * Realiza un submit de las posiciones que han cambiado
             * @returns {Boolean}
             */
            function savePositions(){
                if (markersChanged.length > 0){
                    $.post(urlGerAddress, {'opcion': 'savePositions', 'data': JSON.stringify(markersChanged)} ).done(function( data ) {
                        var result = JSON.parse(data);
                        if (result.status == 'OK'){
                            markersChanged = [];
                            $('#changesNum').html(markersChanged.length);
                            alert(result.message);
                        }else{
                            console.log(result);
                        }
                    });
                }
            }

            /**
             * Escribe a través de consola un mensaje
             * @type type
             */
            function log(data){
                if (debug){
                    console.log(data);
                }
            }
            /**
             * Muestra los detalles de las órdenes anteriores
             * @param {string} data
             * @param {Object} ele
             */
            function showDetails(data, ele){
                if($('#'+data).is(':visible')){
                    $('#showDetails').html('Mostrar detalles');
                }else{
                    $('#showDetails').html('Ocultar detalles');
                }
                $('#'+data).toggle();
                resizeInfoWindow();
            }
            /**
             *
             * @param {type} value
             * @returns {Boolean}
             */
            function addAppointment(){
                $('#moreInfo').hide();
                $('#addAppointment').show();
                resizeInfoWindow();
            }
            /**
             * Validación de Fecha
             * @param {type} value
             * @returns {Boolean}
             */
            function isValidDateTime(value){
                var result = false;
                var matches = value.match(/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/);
                if (matches === null) {
                    result = false;
                }else{
                    var year = parseInt(matches[3], 10);
                    var month = parseInt(matches[2], 10) - 1; // months are 0-11
                    var day = parseInt(matches[1], 10);
                    var hour = parseInt(matches[4], 10);
                    var minute = parseInt(matches[5], 10);
                    var date = new Date(year, month, day, hour, minute);

                    console.log(day+'/'+month+'/'+year+' '+hour+':'+minute);

                    if (date.getFullYear() !== year
                      || date.getMonth() != month
                      || date.getDate() !== day
                      || date.getHours() !== hour
                      || date.getMinutes() !== minute
                    ) {
                        result = false;
                    }else{
                        result = true;
                    }
                }
                return result;
            }
            /**
             * Guardar las nuevas citas.
             * @returns {undefined}
             */
            function saveAppointment(){
                var fecha = $('#fecha').val();
                var operario = $('#operario').val();
                var operario = $('#operario').val();

                if (!isValidDateTime(fecha)){
                    alert('Debe introducir una fecha y hora correctas. El formato apropiado es dd/mm/aaaa hh:mm');
                    return;
                }
                if (operario == ''){
                    alert('Debe seleccionar un operario');
                    return;
                }
                $.post(urlGerAddress, $('#frmAppointment').serialize() ).done(function( data ) {
                    var result = JSON.parse(data);
                    if (result.status == 'OK'){
                        removeMarker($('form#frmAppointment input[name=itemId]').val());
                        $('#addAppointment').hide('fast');
                        $('#moreInfo').show().html(result.message);
                    }
                });
            }
            function removeMarker(id){
                for (var i = 0; i<markers.length; i++){
                    if (markers[i].id === id){
                        markers[i].setMap(null);
                    }
                };
            }

            function resizeInfoWindow(){
                console.log('resize');
                var content = $('#infowindow_content').html();
                infowindow.setContent(content);
            }
            function blockMarkers(){
                var val = (draggable)?false:true;
                var valueTxt = (draggable)?"Desbloquear":"Bloquear";
                $.each(markers,function(key, value){
                    value.setDraggable(val);
                });
                $('#blockMarkers').html(valueTxt);
                draggable = val;
            }

            document.addEventListener('DOMContentLoaded', function() {
                initMap();
            }, false);

            window.addEventListener('load', function() {
                loadAddress();
            }, false);

        </script>
    </head>
    <body>
        <div class="content">
            <div class="row">
                <div class="col-xs-10">
                    <div id="map"></div>
                </div>
                <div class="col-xs-2 no-padding">
                    <div id="btn_actions" class="col-xs-12">
                        <div class="panel panel-primary">
                            <div class="panel-heading">Acciones</div>
                            <div class="panel-body">
                                <button type="button" class="btn btn-success" aria-label="Left Align" onclick="javascript:savePositions();">
                                    <span class="glyphicon glyphicon-floppy-saved" aria-hidden="true"></span>
                                    Guardar cambios
                                    <span class="badge" id="changesNum">0</span>
                                </button>
                                <button type="button" class="btn btn-danger" aria-label="Left Align" onclick="javascript:blockMarkers();">
                                    <span id="blockMarkers">Desbloquear</span>
                                    <span class="badge" id="changesNum">0</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="maplog" class="col-xs-12">
                        <div class="panel panel-primary">
                            <div class="panel-heading" id="info_head">Información</div>
                            <div class="panel-body" id="log"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
