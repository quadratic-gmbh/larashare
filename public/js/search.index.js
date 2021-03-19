'use strict';
$(function() {
  let popup = null;
  let ol_map = null;
  let bikes = [];  
  let map_popup = $('#map-popup');
  const search_result = $('#search-result');
  const date = search_result.data('date');
  function transformCoordinates(lon,lat){
    return ol.proj.transform([lon,lat],'EPSG:4326','EPSG:3857');
  } 
  
  function initMap(){ 
    let map = $('#map');
    let map_container = map.parent();    
    popup = new ol.Overlay({
      element:document.getElementById('map-popup'),
      positioning:'center-left',offset:[18,0]
    });
    let raster= new ol.layer.Tile({source:new ol.source.OSM()});

    let default_center = [ 1568293.8200451995, 5975171.472267023 ];
    
    let zoom = 7.5;
    ol_map=new ol.Map({
      layers:[raster],
      target:'map',
      overlays:[popup],
      view:new ol.View({center:default_center,zoom:zoom})
    });
    ol_map.on('singleclick',onSingleclick);
    ol_map.getView().on('change:resolution',function(){
      popup.setPosition(undefined);
    });
    
    ol_map.addInteraction(new customPointer());       
    
    initFeatures();       
  }
  
  
  let customPointer = function() {
    ol.interaction.Pointer.call(this, {
      handleMoveEvent: handlePointerMoveEvent
    });
  };
  ol.inherits(customPointer, ol.interaction.Pointer);
  
  function handlePointerMoveEvent(evt) {    
    let feature = ol_map.forEachFeatureAtPixel(evt.pixel,
        function(feature) {
          return feature;
        });
    let element = ol_map.getTargetElement();
    if (feature) {
      element.style.cursor = 'pointer';
    } else {
      element.style.cursor = 'auto';
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
    });
  }
  
  function showPopup(pixel,content){
    let element= $(popup.getElement());
    popup.setPosition(ol_map.getCoordinateFromPixel(pixel));
    
    map_popup.find('.map-popup-content').html(content);        
  }
  function initFeatures(){    
    let search_items = $('.search-result-item');
    let features = [];
        
    let calc_center = true;
    let center = null;
    let loc_lon = $('#location_lon').val();
    let loc_lat = $('#location_lat').val();
    if (loc_lon !== '' && loc_lat !== '') {
      loc_lon = parseFloat(loc_lon);
      loc_lat = parseFloat(loc_lat);
      calc_center = false;
      center = transformCoordinates(loc_lon,loc_lat);
      ol_map.getView().setCenter(center);
      ol_map.getView().setZoom(13);
      
      var marker = $('<span></span>').addClass('map-marker').append($('<span></span>').addClass('fas fa-map-marker'));      
      var popup = new ol.Overlay({
        element: marker[0],
        position: center,
        offset: [0,6],
        positioning: 'bottom-center',
      });
      ol_map.addOverlay(popup);
    }
    
    let min_x = null;
    let max_x = null;
    let min_y = null;
    let max_y = null;
    
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
        if (max_x == null || coordinates[0] > max_x)
          max_x = coordinates[0];
        if (min_x == null || coordinates[0] < min_x) 
          min_x = coordinates[0];
        if (max_y == null || coordinates[1] > max_y)
          max_y = coordinates[1];
        if (min_y == null || coordinates[1] < min_y) 
          min_y = coordinates[1];
        
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

    if (calc_center) {
      center = [(max_x+min_x) / 2, (max_y+min_y) / 2];           
      let resolution = ol_map.getView().getResolutionForExtent([min_x,min_y,max_x,max_y]);
      let zoom = Math.round(ol_map.getView().getZoomForResolution(resolution)) - 1;
      if (zoom > 17)
        zoom = 17
      ol_map.getView().setCenter(center);
      ol_map.getView().setZoom(zoom);
    }
    
    let source = new ol.source.Vector({features:features});
    let clusterSource = new ol.source.Cluster({
      distance:20,
      source:source}
    );
    let styleCache={};
    let clusters = new ol.layer.Vector({
      source:clusterSource,
      style:function(feature){
        let size=feature.get('features').length;

        let style_index;
        if  (size > 1) {
          style_index = size;
        } else {          
          style_index = 'icon';
        }
        
        let style = styleCache[style_index];
        if(!style){
          let image;
          let text;
          
          if (style_index == 'icon') {    
            image = new ol.style.Icon({
              imgSize: [100,100],
              scale: 0.5,
              src: '/img/map_pin_bike.svg'
            });
            text = undefined;
          } else {          
            image = new ol.style.Circle({
              radius: 15,
              stroke: new ol.style.Stroke({color: '#fff'}),
              fill: new ol.style.Fill({color: '#000'})
            });
            text = new ol.style.Text({
              text: style_index.toString(),
              fill: new ol.style.Fill({color:'#fff'})
            });
          }
          
          style = [
            new ol.style.Style({
              image: image,
              text: text
            })
          ];
          styleCache[style_index] = style;
          }
        return style;
      }
    });
    ol_map.addLayer(clusters);
    ol_map.changed();
    ol_map.render();
  }
  
  initMap();
  
});
