import React, { Component } from 'react';
import { withTranslation } from 'react-i18next';
import SearchResult from './SearchResult';
import Lightbox from 'react-image-lightbox';

class SearchResults extends Component {

  constructor() {
      super();
      this.state = {
        active: {},
        lightboxOpen: false,
        lightboxSrc: null
      };
      this.setResultsActive = this.setResultsActive.bind(this);
      this.setResultsInactive = this.setResultsInactive.bind(this);
      this.renderResults = this.renderResults.bind(this);
      this.renderLightbox = this.renderLightbox.bind(this);
      this.openLightbox = this.openLightbox.bind(this);
      this.closeLightbox = this.closeLightbox.bind(this);
  }

  componentDidMount() {
    this.props.childRef(this);
  }
  
  componentWillUnmount() {
    this.props.childRef(null);
  }
  
  openLightbox(src) {
    this.setState({
      lightboxOpen: true,
      lightboxSrc: src
    });
  }
  
  closeLightbox() {
    this.setState({
      lightboxOpen: false,
      lightboxSrc: null
    });
  }
  
  setResultsActive(ids) {
    let newActive = {}
    for(let id of ids) {
      newActive[id] = true;
    }
    this.setState({
      active: newActive
    });
  }
  
  setResultsInactive() {
    this.setState({active: {}})
  }

  renderResults() {
    const {data, t, searchMode} = this.props;
    if(!data) {
      return;
    }
    
    let items = [];
    if (!data.bikes.length) {
      return <p>{t('search.no_result')}</p>;
    }
    for (let i = 0; i < data.bikes.length; i++) {
      let bike = data.bikes[i]; 
      let bId = bike.id;
      let imgId = data.bike_images[bId];      
      let rentalPlace = null;
      let bikeInstance = null;
      let id = 'b_' + bId;
      if (searchMode) {
        rentalPlace = data.rental_places[bike.rental_place_id];
        id += '_' + rentalPlace.id;
        bikeInstance = data.bike_instances[bike.rental_place_id];
      } else {
        rentalPlace = data.rental_places[bId];
      }                 
      let active = this.state.active[id];
      items.push(<SearchResult active={active} openLightbox={this.openLightbox} searchFilter={this.props.searchFilter} searchMap={this.props.searchMap} key={i} bike={bike} bikeInstance={bikeInstance} imgId={imgId} rentalPlace={rentalPlace} searchMode={searchMode}/>);
    }
    return items;
  }
  
  renderLightbox() {
    if (!this.state.lightboxOpen) {
      return
    }
    const src = this.state.lightboxSrc;
    
    return <Lightbox mainSrc={src} onCloseRequest={this.closeLightbox}/>;
  }
  
  render() {    
    return <React.Fragment>      
      <div className="kel-search-results">
        {this.renderResults()}
      </div>
      {this.renderLightbox()}
    </React.Fragment>;
  }
}

export default withTranslation()( SearchResults);