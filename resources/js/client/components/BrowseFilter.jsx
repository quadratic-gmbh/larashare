import React, { Component } from 'react';
import { withTranslation } from 'react-i18next';
import { getFieldIfSet } from '../util/misc.jsx';
import classNames from 'classnames';
import Nouislider from "nouislider-react";
import FormError from './FormError';

class BrowseFilter extends Component {

  constructor( props ) {
    super( props );

    this.state = this.getDefaultState( props )

    this.onInputChange = this.onInputChange.bind( this );
    this.onSingleSliderSet = this.onSingleSliderSet.bind( this );    
        
    this.renderCargoWeight = this.renderCargoWeight.bind( this );
    this.renderCargoLength = this.renderCargoLength.bind( this );
    this.renderCargoWidth = this.renderCargoWidth.bind( this );

    this.reset = this.reset.bind( this );
    this.search = this.search.bind( this );   
  }
  
  componentDidMount() {       
    this.props.childRef(this);
  }
  
  componentWillUnmount() {
    this.props.childRef(null);
  }

  
  getDefaultState( props ) {    
    const defaults = props.config.browse ? props.config.browse.defaults : {};
    return {            
      cargoWeight: getFieldIfSet( defaults, 'cargo_weight', 0 ),
      cargoWidth: getFieldIfSet( defaults, 'cargo_width', 0 ),
      cargoLength: getFieldIfSet( defaults, 'cargo_length', 0 ),    
      boxType: getFieldIfSet( defaults, 'box_type_id', '' ),
      wheels: getFieldIfSet( defaults, 'wheels', '' ),
      children: getFieldIfSet( defaults, 'children', '' ),
      electric: getFieldIfSet( defaults, 'electric', '' ),      
    }
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
  
  reset() {
    this.setState( this.getDefaultState( this.props ) );
    this.props.performSearch();
  }
  search() {
    let state = this.state;
    let params = {     
      cargo_weight: Number.parseInt( state.cargoWeight ),
      cargo_width: Number.parseInt( state.cargoWidth ),
      cargo_length: Number.parseInt( state.cargoLength ),
      box_type_id: state.boxType,
      wheels: state.wheels,
      children: state.children,
      electric: state.electric
    }

    this.props.performSearch( params );
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
      <FormError errors={this.props.errors}  field="wheels"/>
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
      <FormError errors={this.props.errors}  field="children"/>
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
      <FormError errors={this.props.errors}  field="electric"/>
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
      <FormError errors={this.props.errors}  field="cargo_width"/>
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
      <FormError errors={this.props.errors}  field="cargo_length"/>
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
      <FormError errors={this.props.errors}  field="cargo_weight"/>
    </React.Fragment>;
  }


  render() {
    const { config, t } = this.props;
    return <div className="kel-browse-filter">
      <div className="kel-row">
        <div className="kel-form-group kel-col-12 kel-col-sm-6 kel-col-md-3 kel-browse-wheels">{this.renderWheels()}</div>
        <div className="kel-form-group kel-col-12 kel-col-sm-6 kel-col-md-3 kel-browse-children">{this.renderChildren()}</div>
        <div className="kel-form-group kel-col-12 kel-col-sm-6 kel-col-md-3 kel-browse-electric">{this.renderElectric()}</div>
        <div className="kel-form-group kel-col-12 kel-col-sm-6 kel-col-md-3 kel-browse-box-type">{this.renderBoxType()}</div>
      </div>
      <div className="kel-row">
        <div className="kel-form-group kel-col-12 kel-col-sm-4 kel-browse-cargo-weight">{this.renderCargoWeight()}</div>
        <div className="kel-form-group kel-col-12 kel-col-sm-4 kel-browse-cargo-length">{this.renderCargoLength()}</div>
        <div className="kel-form-group kel-col-12 kel-col-sm-4 kel-browse-cargo-width">{this.renderCargoWidth()}</div>
      </div>
      <div className="kel-form-group">
        <button className="kel-btn kel-btn-primary kel-mr-2" onClick={this.search}>{t( 'search.search' )}</button>
        <button className="kel-btn kel-btn-link" onClick={this.reset}>{t( 'search.form.reset' )}</button>
      </div>
    </div>;
  }
}

export default withTranslation()( BrowseFilter );