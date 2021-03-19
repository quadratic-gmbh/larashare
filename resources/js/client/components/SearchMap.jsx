import React, { Component } from 'react';
import { withTranslation } from 'react-i18next';
import { urlBikeShow} from '../util/url';
import Map from "ol/Map";
import View from "ol/View";
import { defaults as defaultControls } from 'ol/control';
import { transform, transformExtent } from 'ol/proj';
import WMTSCapabilities from 'ol/format/WMTSCapabilities';
import WMTS, { optionsFromCapabilities } from 'ol/source/WMTS';
import VectorSource from 'ol/source/Vector';
import Feature from 'ol/Feature';
import Cluster from 'ol/source/Cluster';
import TileLayer from "ol/layer/Tile";
import VectorLayer from 'ol/layer/Vector';
import Point from 'ol/geom/Point';
import Style from 'ol/style/Style';
import Icon from 'ol/style/Icon';
import Text from 'ol/style/Text';
import Fill from 'ol/style/Fill';
import Overlay from 'ol/Overlay';
import PointerInteraction from 'ol/interaction/Pointer';
import { DEVICE_PIXEL_RATIO } from 'ol/has';
import { urlAsset } from '../util/url.jsx';
import throttle from 'lodash/throttle';
import ReactDOM from 'react-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faLink } from '@fortawesome/free-solid-svg-icons'

class SearchMap extends Component {
  constructor( props ) {
    super( props );
    this.state = {}

    this.setMapTarget = this.setMapTarget.bind( this );
    this.initMap = this.initMap.bind( this );
    this.initBaseMap = this.initBaseMap.bind( this );
    this.updateFeatures = this.updateFeatures.bind( this );
    this.updateMap = this.updateMap.bind( this );
    this.styleBikes = this.styleBikes.bind( this );
    this.styleHighlightedBikes = this.styleHighlightedBikes.bind( this );
    this.computeIconColors = this.computeIconColors.bind( this );
    //this.setCurrentPosition = this.setCurrentPosition.bind( this );
    //this.unsetCurrentPosition = this.unsetCurrentPosition.bind( this );
    //this.updateCurrentPosition = this.updateCurrentPosition.bind( this );
    this.applyExtent = this.applyExtent.bind( this );
    this.updateExtent = this.updateExtent.bind( this );
    this.resetExtent = this.resetExtent.bind( this );
    this.highlightInMap = this.highlightInMap.bind(this);
    this.unhighlightInMap = this.unhighlightInMap.bind(this);
    this.handlePointerMove = this.handlePointerMove.bind(this)
    this.handleSingleClick = this.handleSingleClick.bind(this);
    this.showMapOverlay = this.showMapOverlay.bind(this);

    this.mapIconColorRef = React.createRef();
    this.mapIconColor = '#000';
    this.mapIconHighlightColorRef = React.createRef();
    this.mapIconHighlightColor = '#0f0';
    this.mapExtent = {
      minX: null,
      minY: null,
      maxX: null,
      maxY: null
    }
    this.mapHoverActive = false;
    this.mapOverlay = null;    
    //this.positionMarker = null;
    this.styleCache = {};
    this.styleHighlight = null;
    this.initMap();
    this.initBaseMap();
  }
  
  highlightInMap(bike) {
    const {data, searchMode} = this.props;    
    let rentalPlaces = [];
    if (searchMode) {
      rentalPlaces.push(data.rental_places[bike.rental_place_id]);
    } else {
      rentalPlaces = data.rental_places[bike.id];
    }
    
    for (let i = 0; i < rentalPlaces.length; i++) {
      let rp = rentalPlaces[i];      
      let featureId = 'b_' + bike.id + '_' + rp.id;
      let feature = this.featureSource.getFeatureById(featureId);
      this.featureSource.removeFeature(feature);
      this.layerHighlight.getSource().addFeature(feature);
    }
  }
  
  unhighlightInMap() {
    let highlighted = this.layerHighlight.getSource().getFeatures();    
    this.featureSource.addFeatures(highlighted);
    this.layerHighlight.getSource().clear();
  }

  initMap() {
    // layer for current position marker
    this.layerPosition = new VectorLayer( {
      source: new VectorSource(),
      zIndex: 1,
      name: 'location',
      style: [new Style( {
        image: new Icon( {
          imgSize: [100, 100],
          scale: 0.5,
          color: '#000',
          crossOrigin: 'anonymous',
          src: urlAsset( 'img/map_pin.svg' )
        } ),
        zIndex: 10
      } )]
    } )

    // feature layer for bikes
    this.featureSource = new VectorSource( { features: [] } );
    let clusterSource = new Cluster( {
      distance: 50,
      source: this.featureSource
    } );
    this.layerCluster = new VectorLayer( {
      source: clusterSource,
      name: 'clusters',
      zIndex: 20,
      style: this.styleBikes
    } );

    // base layer
    this.layerBase = new TileLayer( {
      zIndex: 0
    } );

    // layer for highlighted bike(s)
    this.layerHighlight = new VectorLayer( {
      name: 'highlights',
      source: new VectorSource(),
      zIndex: 30,
      style: this.styleHighlightedBikes
    } );


    let controls = defaultControls( { attributionOptions: { collapsible: false } } );
    this.map = new Map( {
      layers: [this.layerBase, this.layerPosition, this.layerCluster, this.layerHighlight ],
      controls: controls,
      target: null,
      view: new View( {
        center: [1568293.8200451995, 5975171.472267023],
        zoom: 7.5,
        extent: this.transformExtent( [9.5307487, 46.3722761, 17.160776, 49.0205305] ),
        minZoom: 6
      } )
      //      overlays: []
    } );

    let pointerInteraction = new PointerInteraction({
      handleMoveEvent: throttle(this.handlePointerMove, 200)
    });
    
    this.map.addInteraction(pointerInteraction);
    
    
    // map overlay
    this.mapOverlay = new Overlay({
      positioning:'center-left',
      offset:[18,0]
    })
    this.map.addOverlay(this.mapOverlay);
    this.map.on('singleclick', this.handleSingleClick)
    this.map.getView().on('change:resolution', () => {
      this.mapOverlay.setPosition(undefined);
    });
    
    
    //this.updateCurrentPosition();
    this.updateFeatures();
  }
  handleSingleClick(e) {
    const {t} = this.props;
    this.mapOverlay.setPosition(undefined);
    this.map.forEachFeatureAtPixel(e.pixel, (feature, layer) => {
      let coords=feature.getGeometry().getCoordinates();
      let pixel= this.map.getPixelFromCoordinate(coords);
      let feats = feature.getProperties().features;
      
      let container = document.createElement('div');
      container.classList.add('kel-map-overlay');
      
      let title = document.createElement('div');
      title.classList.add('kel-map-overlay-title');
      title.innerText = t('search.index.popup_title');      
      container.appendChild(title);
      
      let content = document.createElement('div');
      content.classList.add('kel-map-overlay-content');            
      
      if (feats.length > 5) {        
        content.innerHTML = '<p>' + feats.length +' ' + title_cb + '</p>'; 
      } else if (feats.length <= 5 && feats.length > 0) {
        content.innerHTML = '';
        
        const searchFilter = this.props.searchFilter();
        let date = null;
        if (searchFilter) {
          date = searchFilter.state.date;
        }
        for (let i = 0; i < feats.length; i++) {
          let a = document.createElement('a');
          let feat = feats[i];
          let icon = document.createElement('span');
          ReactDOM.render(<FontAwesomeIcon icon={faLink} />, icon);
          a.href = urlBikeShow(feat.get('bikeId'), date);
          a.target = '_blank';
          a.innerHTML = feat.getProperties().name + '&nbsp;' + icon.innerHTML;                    
          let line = document.createElement('p');
          line.appendChild(a);
          content.appendChild(line);          
        }
      }     
      container.appendChild(content);
      
      this.showMapOverlay(pixel,container);
    },{layerFiter: (layer) => layer.get('name') == 'clusters'});
  }
  handlePointerMove(e) {
    let foundFeatures = this.map.getFeaturesAtPixel(e.pixel, {layerFilter: layer => layer.get('name') == 'clusters'});
    let mapElement = this.map.getTargetElement();
    let searchResults = this.props.searchResults();
    if(!searchResults) {
      return;
    }    
    
    if(foundFeatures.length > 0) {
      if(!this.mapHoverActive) {
        this.mapHoverActive = true;
        mapElement.style.cursor = 'pointer';
        let features = foundFeatures[0].get('features');        
        let ids = [];
        for (let i= 0; i < features.length; i++) {
          let feature = features[i];
          let id = 'b_' + feature.get('bikeId');
          if(this.props.searchMode) {
            id += '_' + feature.get('placeId');
          }
          ids.push(id);
        }
        searchResults.setResultsActive(ids);
      }
    } else {
      if(this.mapHoverActive) {
        this.mapHoverActive = false;
        mapElement.style.cursor = '';
        searchResults.setResultsInactive();
      }
    }
  }
  
  showMapOverlay(pixel, content) {
    this.mapOverlay.setElement(content);
    this.mapOverlay.setPosition(this.map.getCoordinateFromPixel(pixel));
  }
  
  initBaseMap() {
    let capabilitiesUrl = 'https://www.basemap.at/wmts/1.0.0/WMTSCapabilities.xml';
    //HiDPI support:
    //* Use 'bmaphidpi' layer (pixel ratio 2) for device pixel ratio > 1
    //* Use 'geolandbasemap' layer (pixel ratio 1) for device pixel ratio == 1
    let hiDPI = DEVICE_PIXEL_RATIO > 1;
    let layer = hiDPI ? 'bmaphidpi' : 'geolandbasemap';
    let tilePixelRatio = hiDPI ? 2 : 1;
    let instance = this;
    fetch( capabilitiesUrl ).then( function( response ) {
      return response.text();
    } ).then( function( text ) {
      let result = new WMTSCapabilities().read( text );
      let options = optionsFromCapabilities( result, {
        layer: layer,
        matrixSet: 'google3857',
        style: 'normal'
      } );
      options.tilePixelRatio = tilePixelRatio;
      options.attributions = '<b>Datenquelle: <a href="https://www.basemap.at" target="_blank">basemap.at</a></b>';
      instance.layerBase.setSource( new WMTS( options ) );
    } );
  }

  applyExtent() {
    let extent = this.mapExtent;
    let center = [( extent.maxX + extent.minX ) / 2, ( extent.maxY + extent.minY ) / 2];

    let view = this.map.getView();

    let resolution = view.getResolutionForExtent( [extent.minX, extent.minY, extent.maxX, extent.maxY] );
    let zoom = Math.round( view.getZoomForResolution( resolution ) ) - 1;
    if ( zoom > 17 )
      zoom = 17
    view.setCenter( center );
    view.setZoom( zoom );
  }

  updateExtent( coordinates ) {
    if ( this.mapExtent.maxX == null || coordinates[0] > this.mapExtent.maxX )
      this.mapExtent.maxX = coordinates[0];
    if ( this.mapExtent.minX == null || coordinates[0] < this.mapExtent.minX )
      this.mapExtent.minX = coordinates[0];
    if ( this.mapExtent.maxY == null || coordinates[1] > this.mapExtent.maxY )
      this.mapExtent.maxY = coordinates[1];
    if ( this.mapExtent.minY == null || coordinates[1] < this.mapExtent.minY )
      this.mapExtent.minY = coordinates[1];
  }

  resetExtent() {
    this.mapExtent = {
      minX: null,
      maxX: null,
      minY: null,
      maxY: null
    }
  }

  /*updateCurrentPosition() {
    const { curPosLon, curPosLat } = this.props;
    if ( curPosLon && curPosLat ) {
      this.setCurrentPosition( curPosLon, curPosLat );
    } else {
      this.unsetCurrentPosition();
    }
  }*/

 /* setCurrentPosition( lon, lat ) {
    let coordinates = this.transformCoordinates( lon, lat );
    if ( !this.positionMarker ) {
      this.positionMarker = new Feature( {
        geometry: new Point( coordinates )
      } )
      this.layerPosition.getSource().addFeature( this.positionMarker );
      this.layerPosition.getSource().changed();
    } else {
      this.positionMarker.getGeometry().setCoordinates( coordinates );
      this.positionMarker.changed();
    }

    this.updateExtent( coordinates );
    this.applyExtent();
  }*/

 /* unsetCurrentPosition() {
    if ( this.positionMarker ) {
      let source = this.layerPosition.getSource();
      source.removeFeature( this.positionMarker );
      //      source.changed();
      this.positionMarker = null;
    }
  }*/

  updateFeatures() {
    const { data, searchMode } = this.props;
    this.featureSource.clear();
    if ( !data || !data.bikes.length ) {
      return;
    }

    let features = [];
    let bikes = data.bikes;
    for ( let i = 0; i < bikes.length; i++ ) {
      let bike = bikes[i];

      let rentalPlaces = [];
      if ( searchMode ) {
        rentalPlaces = [data.rental_places[bike.rental_place_id]];
      } else {
        rentalPlaces = data.rental_places[bike.id];
      }

      for ( let j = 0; j < rentalPlaces.length; j++ ) {
        let rp = rentalPlaces[j];
        let lon = rp.lon;
        let lat = rp.lat;
        if ( !lon || !lat ) {
          continue;
        }

        let coordinates = this.transformCoordinates( lon, lat );
        this.updateExtent( coordinates );
        let feature = new Feature( {
          geometry: new Point( coordinates ),
          name: bike.name + ', ' + rp.name,
          bikeId: bike.id,
          placeId: rp.id
        } )

        feature.setId( 'b_' + bike.id + '_' + rp.id );
        features.push( feature );
      }

    }

    this.applyExtent();
    this.featureSource.addFeatures( features );
  }

  updateMap( prevProps ) {
    //    if (this.props.data != prevProps.data) {  
    this.resetExtent();
    //this.updateCurrentPosition();
    this.updateFeatures();
    //    }
  }

  transformCoordinates( lon, lat ) {
    return transform( [lon, lat], 'EPSG:4326', 'EPSG:3857' )
  }

  transformExtent( extent ) {
    return transformExtent( extent, 'EPSG:4326', 'EPSG:3857' );
  }


  styleHighlightedBikes( feature ) {
    let style = this.styleHighlight;
    if ( !style ) {
      style = [
        new Style( {
          image: new Icon( {
            imgSize: [100, 100],
            scale: 0.5,
            crossOrigin: 'anonymous',
            src: urlAsset( 'img/bike_circle.svg' )
          } ),
          zIndex: 6
        } ),
        new Style( {
          image: new Icon( {
            imgSize: [100, 100],
            scale: 0.5,
            color: this.mapIconHighlightColor,
            crossOrigin: 'anonymous',
            src: urlAsset( 'img/map_pin.svg' )
          } ),
          zIndex: 5
        } )
      ];
      this.styleHighlight = style;
    }
    return style;
  }

  styleBikes( feature ) {
    let size = feature.get( 'features' ).length;

    let styleIndex;
    if ( size > 1 ) {
      styleIndex = size;
    } else {
      styleIndex = 'icon';
    }

    let style = this.styleCache[styleIndex];
    if ( !style ) {
      if ( styleIndex == 'icon' ) {
        let bike = new Style( {
          image: new Icon( {
            imgSize: [100, 100],
            scale: 0.5,
            crossOrigin: 'anonymous',
            src: urlAsset( 'img/bike_circle.svg' )
          } ),
          zIndex: 4
        } );
        let pin = new Style( {
          image: new Icon( {
            imgSize: [100, 100],
            scale: 0.5,
            crossOrigin: 'anonymous',
            color: this.mapIconColor,
            src: urlAsset( 'img/map_pin.svg' )
          } ),
          zIndex: 3
        } );

        style = [pin, bike];
      } else {
        let counter = new Style( {
          image: new Icon( {
            imgSize: [100, 100],
            scale: 0.5,
            crossOrigin: 'anonymous',
            src: urlAsset( 'img/circle.svg' )
          } ),
          text: new Text( {
            text: styleIndex.toString(),
            scale: 1.2,
            offsetY: -4,
            fill: new Fill( { color: '#000' } )
          } ),
          zIndex: 2
        } );
        let pin = new Style( {
          image: new Icon( {
            imgSize: [100, 100],
            scale: 0.5,
            crossOrigin: 'anonymous',
            color: this.mapIconColor,
            src: urlAsset( 'img/map_pin.svg' )
          } ),
          zIndex: 1
        } );
        style = [pin, counter];
      }

      this.styleCache[styleIndex] = style;
    }
    return style;
  }

  setMapTarget() {
    this.map.setTarget( "kel-map" );
  }

  componentDidMount() {
    this.props.childRef( this );
    this.computeIconColors();
    this.setMapTarget();
  }

  componentDidUpdate( prevProps ) {
    this.updateMap( prevProps );
    this.setMapTarget();
  }

  componentWillUnmount() {
    this.props.childRef( null );
  }

  computeIconColors() {    
    this.mapIconColor = window.getComputedStyle( this.mapIconColorRef.current ).backgroundColor;
    this.mapIconHighlightColor = window.getComputedStyle( this.mapIconHighlightColorRef.current ).backgroundColor;
  }

  render() {
    const {t} = this.props;
    return <React.Fragment>
      <div className="kel-map-container">
        <div className="kel-map" id="kel-map"></div>
      </div>
      <p class="kel-text-muted">{ t('search.randomness_hint') }</p>
      <div ref={this.mapIconColorRef} className="kel-hidden kel-bg-primary"></div>
      <div ref={this.mapIconHighlightColorRef} className="kel-hidden kel-bg-success"></div>
    </React.Fragment>
  }
}

export default withTranslation()(SearchMap);