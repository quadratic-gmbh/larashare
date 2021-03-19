'use strict';
let ol_map = null;
$(function() {  
  let popup = null;
//  let ol_map = null;
  let features_source = null;
  let highlight_source = null;
  let bikes = [];  
  let style_cache = {};
  let map_popup = $('#map-popup');  
  let map_hover_active = false;
  const search_result = $('#search-result');
  const search_mode = search_result.data('search-mode');
  const date = search_result.data('date');
  const location_input = $('#location');
  const location_old = $('#location_old');
  const extent = {
    min_x: null,
    max_x: null,
    min_y: null,
    max_y: null
  };
  let customPointer = function() {
    ol.interaction.Pointer.call(this, {
      handleMoveEvent: _.throttle(handlePointerMoveEvent, 100)   
    });
  };
  ol.inherits(customPointer, ol.interaction.Pointer);
  // get primary color
  const map_icon_color = $('#map-icon-color').css('background-color') ? $('#map-icon-color').css('background-color') : '#000';
  const map_highlight_color = $('#map-highlight-color').css('background-color') ? $('#map-highlight-color').css('background-color') : '#0f0';
  initMap(); 
    
  $('.search-result-item').on('mouseenter', highlightInMap);
  $('.search-result-item').on('mouseleave', unhighlightInMap);
  $('.map-popup-content').on('click','.map-popup-link', function(e) {
    $('.search-result-item.active').removeClass('active');
    let a = $(this);
    $(a.attr('href')).addClass('active');
  });
    
  if (location_input.length && location_old.val() == '' && navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(onGetCurrentPosition);
  }
  
  function onGetCurrentPosition(position) {
    let lon = position.coords.longitude;
    let lat = position.coords.latitude;               
    let placeholder = $('#location_placeholder').data('value');
    location_input.attr('value',placeholder);
    location_old.attr('value',placeholder);
    $('#location_lon').attr('value',lon);
    $('#location_lat').attr('value',lat);         

    let coordinates = transformCoordinates(lon,lat);
    updateExtent(coordinates);
    //addLocationMarker(coordinates);  
    applyExtentToMap();
  }
  
  function highlightInMap() {
    let bike = $(this);
    let b_id = bike.data('id');
    let places = bike.find('.rental-place');
    let f_list = [];
    for (let i = 0; i < places.length; i++) {
      let p_id = $(places[i]).data('id');
      let f_id = 'b_' + b_id + '_' + p_id;
      let f = features_source.getFeatureById(f_id);
      features_source.removeFeature(f);
      highlight_source.addFeature(f);
    }
  }
  
  function unhighlightInMap() {
    let highlighted = highlight_source.getFeatures();    
    features_source.addFeatures(highlighted);
    highlight_source.clear();
  }
  
  function transformCoordinates(lon,lat){
    return ol.proj.transform([lon,lat],'EPSG:4326','EPSG:3857');
  }     
  
  function transformExtent(extent) {
    return ol.proj.transformExtent(extent,'EPSG:4326','EPSG:3857')
  }
  
  function initMap(){     
    let map = $('#map');
    let map_container = map.parent();    
    popup = new ol.Overlay({
      element:document.getElementById('map-popup'),
      positioning:'center-left',offset:[18,0]
    });
    let raster= new ol.layer.Tile({source:new ol.source.OSM(),zIndex: 0});        
    let default_center =  [ 1060958.092162654, 6278345.70855703 ];
    let zoom = 7.5;
    let extent = transformExtent([9.5307487, 46.3722761, 17.160776, 49.0205305]);    
    ol_map=new ol.Map({
      layers:[raster],
//      layers: [],
      controls: ol.control.defaults({attributionOptions: { collapsible: false }}),
      target:'map',
      overlays:[popup],   
      view: new ol.View({
        center:default_center,
        zoom:zoom,
//        extent: extent,
        minZoom: 6
      })
    });
    ol_map.on('singleclick',onSingleclick);
    ol_map.getView().on('change:resolution',function(){
      popup.setPosition(undefined);
    });
    
    ol_map.addInteraction(new customPointer());    
    
    
//    initBaseMap();
    initHighlightLayer();
    initFeatures();  
    
  }    
  
  function initBaseMap()
  {
    var capabilitiesUrl = 'https://www.basemap.at/wmts/1.0.0/WMTSCapabilities.xml';
    //HiDPI support:
    //* Use 'bmaphidpi' layer (pixel ratio 2) for device pixel ratio > 1
    //* Use 'geolandbasemap' layer (pixel ratio 1) for device pixel ratio == 1
    var hiDPI = ol.has.DEVICE_PIXEL_RATIO > 1;
    var layer = hiDPI ? 'bmaphidpi' : 'geolandbasemap';
    var tilePixelRatio = hiDPI ? 2 : 1;
       
    fetch(capabilitiesUrl).then(function(response) {
      return response.text();
    }).then(function(text) {
      let result = new ol.format.WMTSCapabilities().read(text);
      let options = ol.source.WMTS.optionsFromCapabilities(result, {
        layer: layer,
        matrixSet: 'google3857',
        style: 'normal'
      });
      options.tilePixelRatio = tilePixelRatio;
      options.attributions = '<b>Datenquelle: <a href="https://www.basemap.at" target="_blank">basemap.at</a></b>';        
      ol_map.addLayer(new ol.layer.Tile({
        source: new ol.source.WMTS(options),
        zIndex: 0
      }));
      
//      ol_map.addControl(new ol.control.Attribution({
//        label: '<b>&copy; <a href="https://www.basemap.at" target="_blank">basemap.at</a></b>',
//        collapsed: false,
//        collapsible: false
//      }));      
    });
  }
  
  function initHighlightLayer()
  {
    highlight_source = new ol.source.Vector({
      features: new ol.Collection()
    });
    let highlight_layer = new ol.layer.Vector({
      name: 'highlights',
      source: highlight_source,
      zIndex: 30,
      style : [
        new ol.style.Style({
          image: new ol.style.Icon({
            imgSize: [100,100],
            scale: 0.5, 
            src: '/img/bike_circle.svg'
          }),
          zIndex: 6
        }),
        new ol.style.Style({
          image: new ol.style.Icon({
            imgSize: [100,100],
            scale: 0.5,
            color: map_highlight_color,
            src: '/img/map_pin.svg'
          }),
          zIndex: 5
        })
      ]
    });
    ol_map.addLayer(highlight_layer);
  }
  
  function handlePointerMoveEvent(evt) {        
    let found_features = ol_map.getFeaturesAtPixel(evt.pixel, {
      layerFilter: function(layer) {        
        return layer.get('name') == 'clusters';
      }
    });
    let element = ol_map.getTargetElement();
    if(found_features != null) {                  
      if (!map_hover_active) {
        map_hover_active = true;
        element.style.cursor = 'pointer';
        let features = found_features[0].get('features');
        for (let i = 0; i < features.length; i++){
          let feat = features[i];
          let id = '#bike-' + feat.get('bike_id');
          if (search_mode) {
            id += '-' + feat.get('place_id');
          }
          $(id).addClass('active');
        }
      }
      
    } else {
      if (map_hover_active) {
        map_hover_active = false;
        element.style.cursor = 'auto';
        $('.search-result-item.active').removeClass('active');
      }
    }
  }  
  
  function onSingleclick(e){        
    popup.setPosition(undefined);
    ol_map.forEachFeatureAtPixel(e.pixel,function(feature,layer){      
      let coords=feature.getGeometry().getCoordinates();
      let pixel=ol_map.getPixelFromCoordinate(coords);
      let feats = feature.getProperties().features;
      let content = '';            

      if (feats.length > 5) {
        content = '<p>' + feats.length +' ' + title_cb + '</p>';
      } else if (feats.length <= 5 && feats.length > 0) {
        content = '';
        for (let i = 0; i < feats.length; i++) {
          let feat = feats[i];
          content+='<p><a href="/bike/' + feat.get('bike_id')+ '?date=' + date + '">';
          content+= feat.getProperties().name;
          content+=' <span class="fas fa-link"></span>';
          content+='</a></p>';
        }
      }     
      
      showPopup(pixel,content);
    },{
      layerFilter: function(layer) {        
        return layer.get('name') == 'clusters';
      }
    });
  }
  
  function updateExtent(coordinates) 
  {    
    if (extent.max_x == null || coordinates[0] > extent.max_x)
      extent.max_x = coordinates[0];
    if (extent.min_x == null || coordinates[0] < extent.min_x) 
      extent.min_x = coordinates[0];
    if (extent.max_y == null || coordinates[1] > extent.max_y)
      extent.max_y = coordinates[1];
    if (extent.min_y == null || coordinates[1] < extent.min_y) 
      extent.min_y = coordinates[1];
  }
  
  function addLocationMarker(coordinates) 
  {   
    let marker = new ol.Feature({
      geometry: new ol.geom.Point(coordinates)          
    });
    marker.setId('location');
    
    let marker_layer = new ol.layer.Vector({
      source: new ol.source.Vector({features: [marker]}),
      zIndex: 1,
      style:[
        new ol.style.Style({
          image: new ol.style.Icon({
            imgSize: [100,100],
            scale: 0.5,
            color: '#000',
            src: '/img/map_pin.svg'
          }),              
          zIndex: 10
        })
      ]
    });
    marker_layer.set('name','location');
    ol_map.addLayer(marker_layer);
  }
  
  function applyExtentToMap()
  {
    let center = [(extent.max_x+extent.min_x) / 2, (extent.max_y+extent.min_y) / 2];           
    let resolution = ol_map.getView().getResolutionForExtent([extent.min_x,extent.min_y,extent.max_x,extent.max_y]);
    let zoom = Math.round(ol_map.getView().getZoomForResolution(resolution)) - 1;
    if (zoom > 17)
      zoom = 17    
    ol_map.getView().setCenter(center);
    ol_map.getView().setZoom(zoom);
  }
  
  function showPopup(pixel,content){
    let element= $(popup.getElement());
    popup.setPosition(ol_map.getCoordinateFromPixel(pixel));
    
    map_popup.find('.map-popup-content').html(content);        
  }
  function initFeatures(){    
    let search_items = $('.search-result-item');
    let features = [];
//    let style_cache={};

    let loc_lon = $('#location_lon');
    let loc_lat = $('#location_lat');
    let has_location = false;
         
    if (loc_lon.length && loc_lat.length && loc_lon.val() != '' && loc_lat.val() != '') {      
      let lon = parseFloat(loc_lon.val());
      let lat = parseFloat(loc_lat.val());
      let center = transformCoordinates(lon,lat);     
      updateExtent(center);
      //addLocationMarker(center);
      has_location = true;
    }    
    
    if(!search_items.length && !has_location){
    	return;
    }
    
    for(let i = 0; i < search_items.length; i++) {      
      let item = $(search_items[i]);
      let bike_id = item.data('id');
      let bike_name = item.data('name');
      let rental_places = item.find('.rental-place');
      for(let j = 0; j < rental_places.length; j++) {
        let rp = $(rental_places[j]);
        let lon = rp.data('lon');
        let lat = rp.data('lat');
        let place_name = rp.data('name');
        let place_id = rp.data('id');
        
        if(lon == "" || lat == "") { 
          continue;
        }
        
        let coordinates = transformCoordinates(parseFloat(lon),parseFloat(lat));       
        updateExtent(coordinates);          
        
        let feature = new ol.Feature({
          geometry: new ol.geom.Point(coordinates),
          name: bike_name + ', ' + place_name,        
          bike_id: bike_id,
          place_id: place_id
        });
        
        feature.setId('b_' + bike_id + '_' + place_id);
        features.push(feature);                  
      }
    }         
  
    applyExtentToMap();      
        
    features_source = new ol.source.Vector({features:features});
    let clusterSource = new ol.source.Cluster({
      distance:50,
      source:features_source}
    );
    
    let clusters = new ol.layer.Vector({
      source:clusterSource,
      name: 'clusters',
      zIndex: 20,
      style:function(feature){
        let size=feature.get('features').length;

        let style_index;
        if  (size > 1) {
          style_index = size;
        } else {          
          style_index = 'icon';
        }
        
        let style = style_cache[style_index];
        if(!style){        
          if (style_index == 'icon') {    
            let bike = new ol.style.Style({
              image: new ol.style.Icon({
                imgSize: [100,100],
                scale: 0.5, 
                src: '/img/bike_circle.svg'
              }),
              zIndex: 4
            });
            let pin = new ol.style.Style({
              image: new ol.style.Icon({
                imgSize: [100,100],
                scale: 0.5,
                color: map_icon_color,
                src: '/img/map_pin.svg'
              }),
              zIndex: 3
            });
            
            style = [pin,bike];
          } else {   
            let counter = new ol.style.Style({
              image: new ol.style.Icon({
                imgSize: [100,100],
                scale: 0.5, 
                src: '/img/circle.svg'
              }),
              text: new ol.style.Text({
                text: style_index.toString(),
                scale: 1.2,
                offsetY: -4,
                fill: new ol.style.Fill({color:'#000'})
              }),              
              zIndex: 2
            });            
            let pin = new ol.style.Style({
              image: new ol.style.Icon({
                imgSize: [100,100],
                scale: 0.5,
                color: map_icon_color,
                src: '/img/map_pin.svg'
              }),              
              zIndex: 1
            });
            style = [pin,counter];
                         
          }
                   
          style_cache[style_index] = style;
          }
        return style;
      }
    });   
    ol_map.addLayer(clusters);
    ol_map.changed();
    ol_map.render();
  }     
});
