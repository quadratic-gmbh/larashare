import React, { Component } from 'react';
import { withTranslation } from 'react-i18next';
import Collapse from './Collapse';
import FormError from './FormError';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faMapMarker, faCalendar, faClock, faCaretDown, faCaretUp } from '@fortawesome/free-solid-svg-icons'
import { timeToMinutes, minutesToTime, getFieldIfSet } from '../util/misc.jsx';
import Flatpickr from "react-flatpickr";
import classNames from 'classnames';
import Nouislider from "nouislider-react";

class SearchFilter extends Component {

  constructor( props ) {
    super( props );

    this.state = this.getDefaultState( props )
    this.state.detailsOpen = false;

    this.onGetCurrentPosition = this.onGetCurrentPosition.bind(this);
    this.onInputChange = this.onInputChange.bind( this );
    this.onSingleSliderSet = this.onSingleSliderSet.bind( this );
    this.onTimeSliderSet = this.onTimeSliderSet.bind( this );
    this.onTimeSliderStart = this.onTimeSliderStart.bind( this );
    this.onFlatpickrChange = this.onFlatpickrChange.bind( this );    
    this.onDateChange = this.onDateChange.bind(this);
    
    this.renderLocation = this.renderLocation.bind( this );
    this.renderDate = this.renderDate.bind( this );
    this.renderDuration = this.renderDuration.bind( this );
    this.renderFlex = this.renderFlex.bind( this );
    this.renderMisc = this.renderMisc.bind( this );
    this.renderTime = this.renderTime.bind( this );
    this.renderCargoWeight = this.renderCargoWeight.bind( this );
    this.renderCargoLength = this.renderCargoLength.bind( this );
    this.renderCargoWidth = this.renderCargoWidth.bind( this );

    this.toggleDetails = this.toggleDetails.bind( this );
    this.reset = this.reset.bind( this );
    this.search = this.search.bind( this );
    this.updateLocation = this.updateLocation.bind(this);
    
    this.flatpickrOptions = {
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
      minuteIncrement: 30,
      time_24hr: true
    }

    
    this.timeslider = null;
  }
  
  componentDidMount() {
    let {location, locationOld} = this.state;
    if(location.length && locationOld == null && navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(this.onGetCurrentPosition);
    }
    
    this.props.childRef(this);
  }
  
  componentWillUnmount() {
    this.props.childRef(null);
  }

//  componentDidUpdate( prevProps, prevState ) {
//    const { location } = this.props;
//    if ( location && this.state.location != this.state.locationOld ) {
//      this.setState( {
//        location: location.cur,
//        locationOld: location.old,
//        locationLon: location.lon,
//        locationLat: location.lat
//      } );
//    }    
//  }
//  
  updateLocation(location) {
    this.setState( {
      location: location.cur,
      locationOld: location.old,
      locationLon: location.lon,
      locationLat: location.lat
    } );
  }
  
  getDefaultState( props ) {
    const defaults = props.config.search.defaults;
    return {
      location: defaults.location != undefined ? defaults.location : '',
      locationOld: null,
      locationLon: null,
      locationLat: null,
      duration: getFieldIfSet( defaults, 'duration', 2 ),
      durationType: getFieldIfSet( defaults, 'duration_type', 'h' ),
      date: getFieldIfSet( defaults, 'date', '' ),
      flex: getFieldIfSet( defaults, 'flex', '' ),
      cargoWeight: getFieldIfSet( defaults, 'cargo_weight', 0 ),
      cargoWidth: getFieldIfSet( defaults, 'cargo_width', 0 ),
      cargoLength: getFieldIfSet( defaults, 'cargo_length', 0 ),
      timeFrom: getFieldIfSet( defaults, 'time_from', '00:00' ),
      timeTo: getFieldIfSet( defaults, 'time_to', '23:59' ),
      boxType: getFieldIfSet( defaults, 'box_type_id', '' ),
      wheels: getFieldIfSet( defaults, 'wheels', '' ),
      children: getFieldIfSet( defaults, 'children', '' ),
      electric: getFieldIfSet( defaults, 'electric', '' )
    }
  }
  
  onGetCurrentPosition(position) {
    const {t} = this.props;
    let lon = position.coords.longitude;
    let lat = position.coords.latitude;               
    
    let placeholder= t('search.current_location');
    this.setState({
      location: placeholder,
      locationOld: placeholder,
      locationLon: lon,
      locationLat: lat
    });
    
    let searchMap = this.props.searchMap();
    if(searchMap) {
      searchMap.setCurrentPosition(lon,lat);
    }    
  }

  onDateChange(event) {
    this.setState({
      date: event.target.value
    })   
  }
  
  onInputChange( event, field ) {
    this.setState( {
      [field]: event.target.value
    } );
  }

  onSingleSliderSet( values, handle, field ) {
    this.setState( {
      [field]: values[handle]
    } );
  }

  onTimeSliderStart() {
    this.timeslider.classList.remove( 'hide-tooltips' );
  }

  onTimeSliderSet( values, handle ) {
    this.timeslider.classList.add( 'hide-tooltips' );
    let field = "time" + ( handle == 0 ? 'From' : 'To' );

    this.setState( {
      [field]: values[handle]
    } );
  }

  onFlatpickrChange( date, field ) {
    this.setState( {
      [field]: date
    } )
  }

  toggleDetails() {
    this.setState( {
      detailsOpen: !this.state.detailsOpen
    } );
  }

  reset() {
    let defaultState = this.getDefaultState( this.props );
    this.setState( defaultState );
    this.props.performSearch();
  }
  search() {
    let state = this.state;
    let params = {
      location: state.location,
      location_old: state.locationOld,
      location_lon: state.locationLon,
      location_lat: state.locationLat,
      duration: state.duration,
      duration_type: state.durationType,
      flex: state.flex,
      date: state.date,
      cargo_weight: Number.parseInt( state.cargoWeight ),
      cargo_width: Number.parseInt( state.cargoWidth ),
      cargo_length: Number.parseInt( state.cargoLength ),
      time_from: state.timeFrom,
      time_to: state.timeTo,
      box_type_id: state.boxType,
      wheels: state.wheels,
      children: state.children,
      electric: state.electric
    }


    this.props.performSearch( params );
  }

  renderLocation() {
    return <React.Fragment>
      <div className="kel-input-group">
        <div className="kel-input-group-prepend">
          <span className="kel-input-group-text">
            <FontAwesomeIcon icon={faMapMarker} />
          </span>
        </div>
        <input type="text" className="kel-form-control" value={this.state.location} onChange={e => { this.onInputChange( e, 'location' ) }} />
      </div>
      <FormError errors={this.props.errors} field="location" />
    </React.Fragment>
  }

  renderDate() {
    return <React.Fragment>
      <div className="kel-input-group">
        <div className="kel-input-group-prepend">
          <span className="kel-input-group-text">
            <FontAwesomeIcon icon={faCalendar} />
          </span>
        </div>
        <input type="date" className="kel-form-control" value={this.state.date} onChange={this.onDateChange} />
      </div>
      <FormError errors={this.props.errors}  field="date"/>
    </React.Fragment>
  }

  renderDuration() {
    const { t } = this.props;
    return <React.Fragment>
      <label className="kel-d-block kel-d-sm-block">&nbsp;</label>
      <div className="kel-input-group">
        <div className="kel-input-group-prepend">
          <span className="kel-input-group-text">
            <FontAwesomeIcon icon={faClock} />
          </span>
        </div>
        <input type="number" className="kel-form-control" value={this.state.duration} onChange={e => { this.onInputChange( e, 'duration' ) }} />
        <select className="kel-form-control" value={this.state.durationType} onChange={e => { this.onInputChange( e, 'durationType' ) }}>
          <option value="h">{t( 'hour_plural' )}</option>
          <option value="d">{t( 'day_plural' )}</option>
        </select>
      </div>
      <FormError errors={this.props.errors}  field='duration'/>
      <FormError errors={this.props.errors}  field='duration_type'/>
    </React.Fragment>
  }

  renderFlex() {
    const { t } = this.props;
    return <React.Fragment>
      <label><b>{t( 'search.form.flex.flex' )}</b></label>
      <select className="kel-form-control" value={this.state.flex} onChange={e => { this.onInputChange( e, 'flex' ) }}>
        <option>{t( 'no' )}</option>
        <option value="1">{t( 'search.form.flex.1' )}</option>
        <option value="2">{t( 'search.form.flex.2' )}</option>
        <option value="3">{t( 'search.form.flex.3' )}</option>
      </select>
      <FormError errors={this.props.errors}  field='flex'/>
    </React.Fragment>
  }

  renderTime() {
    const { t } = this.props;

    return <React.Fragment>
      <label><b>{t( 'search.form.time' )}</b></label>
      <div className="kel-slider-container kel-slider-dual kel-search-time">
        <Nouislider start={this.state.cargoWidth}
          instanceRef={instance => {
            if ( instance && !this.timeslider ) {
              this.timeslider = instance;
            }
          }}
          className="hide-tooltips kel-slider"
          range={{ min: 0, max: 1439 }}
          start={[this.state.timeFrom, this.state.timeTo]}
          step={30}
          connect={true}
          tooltips={true}
          format={{ from: timeToMinutes, to: minutesToTime }}
          onStart={this.onTimeSliderStart}
          onSet={( values, handle ) => { this.onTimeSliderSet( values, handle ) }} />
        <div className="kel-slider-labels">
          <span>00:00</span>
          <span>23:59</span>
        </div>
        <div className="kel-slider-inputs">
          <div>
            <Flatpickr className="kel-form-control" options={this.flatpickrOptions} value={this.state.timeFrom} onChange={( date, dateStr ) => { this.onFlatpickrChange( dateStr, 'timeFrom' ) }} />
            <FormError errors={this.props.errors}  field='time_from'/>
          </div>
          <div>
            <Flatpickr className="kel-form-control" options={this.flatpickrOptions} value={this.state.timeTo} onChange={( date, dateStr ) => { this.onFlatpickrChange( dateStr, 'timeTo' ) }} />
            <FormError errors={this.props.errors}  field='time_to'/>
          </div>
        </div>
      </div>
    </React.Fragment>;
  }

  renderMisc() {
    return <div className="kel-row">
      <div className="kel-form-group kel-col-12 kel-col-sm-6 kel-col-lg-auto kel-search-wheels">{this.renderWheels()}</div>
      <div className="kel-form-group kel-col-12 kel-col-sm-6 kel-col-lg-auto kel-search-children">{this.renderChildren()}</div>
      <div className="kel-form-group kel-col-12 kel-col-sm-6 kel-col-lg-auto kel-search-electric">{this.renderElectric()}</div>
      <div className="kel-form-group kel-col-12 kel-col-sm-6 kel-col-lg-auto kel-search-box-type">{this.renderBoxType()}</div>
    </div>;
  }

  renderWheels() {
    const { t } = this.props;
    return <React.Fragment>
      <label><b>{t( 'search.form.wheels' )}</b></label>
      <select className="kel-form-control" value={this.state.wheels} onChange={e => { this.onInputChange( e, 'wheels' ) }}>
        <option value="">{t( 'whatever' )}</option>
        <option value="2">{t( 'bike.wheels.2' )}</option>
        <option value="3">{t( 'bike.wheels.3' )}</option>
        <option value="4">{t( 'bike.wheels.4' )}</option>
      </select>
      <FormError errors={this.props.errors}  field='wheels'/>
    </React.Fragment>
  }

  renderChildren() {
    const { t } = this.props;

    let options = [<option key={0} value={0}>{t( 'bike.children0' )}</option>];
    for ( let i = 1; i < 5; i++ ) {
      options.push( <option key={i} value={i}>{t( 'bike.children', { count: i } )}</option> );
    }
    return <React.Fragment>
      <label><b>{t( 'search.form.children' )}</b></label>
      <select className="kel-form-control" value={this.state.children} onChange={e => { this.onInputChange( e, 'children' ) }}>
        <option value="">{t( 'whatever' )}</option>
        {options}
      </select>
      <FormError errors={this.props.errors}  field='children'/>
    </React.Fragment>
  }

  renderElectric() {
    const { t } = this.props;
    return <React.Fragment>
      <label><b>{t( 'search.form.electric' )}</b></label>
      <select className="kel-form-control" value={this.state.electric} onChange={e => { this.onInputChange( e, 'electric' ) }}>
        <option value="">{t( 'whatever' )}</option>
        <option value="2">{t( 'bike.electric.0' )}</option>
        <option value="1">{t( 'bike.electric.1' )}</option>
      </select>
      <FormError errors={this.props.errors}  field='electric'/>
    </React.Fragment>
  }

  renderBoxType() {
    const { t, config} = this.props;
    
    let options = [];
    if (config.box_type_ids) {
      let values = config.box_type_ids;
      for(const field in values) {
        let value = values[field];
        options.push(<option value={value} key={value}>{t('bike.box_type.' + field)}</option> );
      }
    }
    return <React.Fragment>
      <label><b>{t( 'search.form.box_type_id' )}</b></label>
      <select className="kel-form-control" value={this.state.boxType} onChange={e => { this.onInputChange( e, 'boxType' ) }}>
        <option value="">{t( 'whatever' )}</option>
        {options}
      </select>
      <FormError errors={this.props.errors}  field='box_type_id'/>
    </React.Fragment>
  }

  renderCargoWidth() {
    const { t } = this.props;
    return <React.Fragment>
      <label><b>{t( 'search.form.cargo_width' )}</b></label>
      <div className="kel-slider-container">
        <Nouislider start={this.state.cargoWidth}
          className="kel-slider"
          range={{ min: 0, max: 150 }}
          step={1}
          connect="upper"
          tooltips={true}
          onSet={( values, handle ) => { this.onSingleSliderSet( values, handle, 'cargoWidth' ) }} />
        <div className="kel-slider-labels">
          <span>0 cm</span>
          <span>150 cm</span>
        </div>
      </div>
      <FormError errors={this.props.errors}  field='cargo_width'/>
    </React.Fragment>;
  }

  renderCargoLength() {
    const { t } = this.props;
    return <React.Fragment>
      <label><b>{t( 'search.form.cargo_length' )}</b></label>
      <div className="kel-slider-container">
        <Nouislider start={this.state.cargoLength}
          className="kel-slider"
          range={{ min: 0, max: 300 }}
          step={1}
          connect="upper"
          tooltips={true}
          onSet={( values, handle ) => { this.onSingleSliderSet( values, handle, 'cargoLength' ) }} />
        <div className="kel-slider-labels">
          <span>0 cm</span>
          <span>300 cm</span>
        </div>
      </div>
      <FormError errors={this.props.errors}  field='cargo_length'/>
    </React.Fragment>;
  }

  renderCargoWeight() {
    const { t } = this.props;
    return <React.Fragment>
      <label><b>{t( 'search.form.cargo_weight' )}</b></label>
      <div className="kel-slider-container">
        <Nouislider start={this.state.cargoWeight}
          className="kel-slider"
          range={{ min: 0, max: 200 }}
          step={1}
          connect="upper"
          tooltips={true}
          onSet={( values, handle ) => { this.onSingleSliderSet( values, handle, 'cargoWeight' ) }} />
        <div className="kel-slider-labels">
          <span>0 kg</span>
          <span>200 kg</span>
        </div>
      </div>
      <FormError errors={this.props.errors}  field='cargo_weight'/>
    </React.Fragment>;
  }


  renderDetails() {
    return <Collapse active={this.state.detailsOpen}>
      <div className="kel-row">
        <div className="kel-form-group kel-col-12 kel-col-md-6 kel-col-lg-3 mb-md-0 kel-search-time">{this.renderTime()}</div>
        <div className="kel-form-group kel-col-12 kel-col-md-6 kel-col-lg-9">{this.renderMisc()}</div>
      </div>
      <div className="kel-row">
        <div className="kel-form-group kel-col-12 kel-col-sm-4 kel-search-cargo-weight">{this.renderCargoWeight()}</div>
        <div className="kel-form-group kel-col-12 kel-col-sm-4 kel-search-cargo-length">{this.renderCargoLength()}</div>
        <div className="kel-form-group kel-col-12 kel-col-sm-4 kel-search-cargo-width">{this.renderCargoWidth()}</div>
      </div>
    </Collapse>
  }

  render() {
    const { config, t } = this.props;
    return <div className="kel-search-filter">
      <div className="kel-row">
        <div className="kel-form-group kel-col-12 kel-col-sm-6 kel-col-lg-4 kel-col-xl-3 kel-search-location">
          {this.renderLocation()}
        </div>
        <div className="kel-form-group kel-col-12 kel-col-sm-6 kel-col-lg-4 kel-col-xl-3 kel-search-date">
          {this.renderDate()}
        </div>
      </div>
      <div className="kel-row ">
        <div className="kel-form-group kel-col-12 kel-col-sm-6 kel-col-lg-4 kel-col-xl-3 kel-search-duration">
          {this.renderDuration()}
        </div>
        <div className="kel-form-group kel-col-12 kel-col-sm-6 kel-col-lg-4 kel-col-xl-3 kel-search-flex">
          {this.renderFlex()}
        </div>
      </div>
      <button className="kel-btn kel-btn-link" onClick={this.toggleDetails}>
        {t( 'search.form.details' )}&nbsp;<FontAwesomeIcon icon={( this.state.detailsOpen ? faCaretUp : faCaretDown )} />
      </button>
      {this.renderDetails()}
      <div className="kel-form-group">
        <button className="kel-btn kel-btn-primary px-5 mr-2" onClick={this.search}>{t( 'search.search' )}</button>
        <button className="kel-btn kel-btn-link" onClick={this.reset}>{t( 'search.form.reset' )}</button>
      </div>
    </div>;
  }
}

export default withTranslation()( SearchFilter );