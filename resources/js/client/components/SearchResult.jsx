import React, { Component } from 'react';
import { withTranslation } from 'react-i18next';
import { urlBikeTos, urlBikeShow, urlBikeImg, urlBikeImgDefault, IMG_SZ_1000, IMG_SZ_300 } from '../util/url';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSearch, faCheck, faTimes } from '@fortawesome/free-solid-svg-icons'
import classNames from 'classnames';

class SearchResult extends Component {

  constructor() {
    super();
    this.state = {};

    this.onMouseEnter = this.onMouseEnter.bind(this);
    this.onMouseLeave = this.onMouseLeave.bind(this);
    this.onLightboxClick = this.onLightboxClick.bind(this);
    this.renderImg = this.renderImg.bind(this);
  }

  onMouseEnter() {
    const searchMap = this.props.searchMap();
    if (searchMap) {
      searchMap.highlightInMap(this.props.bike);
    }
  }
  
  onMouseLeave() {
    const searchMap = this.props.searchMap();
    if (searchMap) {
      searchMap.unhighlightInMap();
    }
  }
  
  onLightboxClick(e, src) {
    e.preventDefault();
    
    this.props.openLightbox(src);
  }
  
  renderRentalPlace( rentalPlace, searchMode ) {
    if ( !searchMode ) {
      let items = [];
      for ( let i = 0; i < rentalPlace.length; i++ ) {
        let rp = rentalPlace[i];
        let name = `${rp.name}, ${rp.postal_code} ${rp.city}`;
        items.push( <li key={i} className="kel-search-result-rental-place">{name}</li> )
      }

      return <ul className="kel-list-unstyled">{items}</ul>;
    } else {
      let name = `${rentalPlace.name}, ${rentalPlace.postal_code} ${rentalPlace.city}`;
      return <p className="kel-search-result-rental-place">{name}</p>;
    }
  }

  renderImg( showUrl ) { 
    const { imgId } = this.props;
    // render a default img
    if ( imgId == undefined ) {
      return <div className="kel-search-result-image-container">
        <a target="_blank" href={showUrl}>
          <img src={urlBikeImgDefault()} alt="image" className="kel-search-result-image" />
        </a>
      </div>;
    }

    let lbSrc = urlBikeImg(imgId, IMG_SZ_1000);
    
    return <div className="kel-search-result-image-container">
      <a href={showUrl} target="_blank">
        <img src={urlBikeImg( imgId, IMG_SZ_300 )} alt="image" className="kel-search-result-image" />
      </a>
      <a href={lbSrc} target="_blank" onClick={(e) => {this.onLightboxClick(e, lbSrc)}} className="kel-search-result-image-icon">
        <FontAwesomeIcon icon={faSearch} size="2x" />
      </a>
    </div>
  }

  renderTitle( bike, bikeInstance, t ) {
    if ( bikeInstance != undefined ) {
      return <React.Fragment>
        {bike.name}
        <br />
        <small>{t( 'bike.rental_place' )}&nbsp;{bikeInstance}</small>
      </React.Fragment>;
    } else {
      return bike.name
    }
  }

  renderPricing( bike, t ) {
    let p_string = t( 'bike.pricing_type.' + bike.pricing_type );
    let p_values = [];
    if ( bike.pricing_type == 'FIXED' ) {
      p_string += ': ';
      for(const value of ['hourly', 'daily', 'weekly']) {
        if (bike.pricing_values[value]) {
          p_values.push(<li key={value}>{bike.pricing_values[value] + '€ /' + t( 'bike.pricing_rate.' + value.toUpperCase() )}</li>)
        }
      }
    }
    
    return <ul className="kel-list-unstyled">
      <li>{p_string}</li>
      {p_values}
      {bike.pricing_deposit && <li>{t( 'bike.pricing.deposit' ) + ': ' + bike.pricing_deposit + '€'}</li>}
    </ul>
  }

  renderDownloadTos( bike, t ) {
    if ( bike.terms_of_use_file ) {
      return <p>
        <a href={urlBikeTos( bike.id )} target="_blank">{t( 'bike.terms_of_use' )}</a>
      </p>
    }
  }

  renderChildren( bike, t ) {
    let icon = null;
    if ( bike.children > 0 ) {
      icon = <FontAwesomeIcon className="kel-ml-2 kel-text-success" icon={faCheck} />
    } else {
      icon = <FontAwesomeIcon className="kel-ml-2 kel-text-danger" icon={faTimes} />
    }

    return <p>
      {t( 'search.result.children' )}{icon}
    </p>
  }

  renderElectric( bike, t ) {
    let icon = null;
    if ( bike.electric > 0 ) {
      icon = <FontAwesomeIcon className="kel-ml-2 kel-text-success" icon={faCheck} />
    } else {
      icon = <FontAwesomeIcon className="kel-ml-2 kel-text-danger" icon={faTimes} />
    }

    return <p>
      {t( 'search.form.electric' )}{icon}
    </p>
  }

  renderCargo( bike, t ) {
    return <ul className="kel-list-unstyled">
      <li>{t( 'search.result.cargo_weight', { n: bike.cargo_weight } )}</li>
      <li>{t( 'search.result.cargo_length', { n: bike.cargo_length } )}</li>
      <li>{t( 'search.result.cargo_width', { n: bike.cargo_width } )}</li>
    </ul>;
  }

  render() {
    const {bike, bikeInstance, rentalPlace, searchMode, t, active} = this.props;
    
    let date = null;
    if (this.props.searchFilter) {
      const searchFilter = this.props.searchFilter();
      date = searchFilter.state.date;
    }
    let showUrl = urlBikeShow( bike.id, date );
    let wrapperClass = classNames('kel-search-result', {'active': active})
    return <div className={wrapperClass} onMouseEnter={(e) => this.onMouseEnter()} onMouseLeave={(e) => this.onMouseLeave()}>
      {this.renderImg( showUrl )}
      <div className="kel-search-result-body">
        <h3 className="kel-search-result-title">
          {this.renderTitle( bike, bikeInstance, t )}
        </h3>
        {this.renderRentalPlace( rentalPlace, searchMode )}
        {this.renderPricing( bike, t )}
        {this.renderDownloadTos( bike, t )}
        {this.renderChildren( bike, t )}
        {this.renderElectric( bike, t )}
        {this.renderCargo( bike, t )}
      </div>
      <div className="kel-search-result-footer">
        <a target="_blank" href={showUrl} className="kel-btn kel-btn-primary">{t( 'search.result.reserve_link' )}</a>
      </div>
    </div>;
  }
}

export default withTranslation()( SearchResult );