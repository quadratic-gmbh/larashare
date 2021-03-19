import React, { Component } from 'react';
import Search from './components/Search';
import Browse from './components/Browse';
import Loading from './components/Loading';
import ApiService from './service/ApiService';
import { withTranslation } from 'react-i18next';


class App extends Component {

  constructor() {
    super();
    this.state = {
      configLoaded: false,
      config: null,
      configError: null
    };

    this.apiService = new ApiService();
    this.handleConfigLoaded = this.handleConfigLoaded.bind( this );
    this.handleConfigError = this.handleConfigError.bind( this );
    this.renderError = this.renderError.bind( this );
  }

  componentDidMount() {
    this.apiService.getClientConfig()
      .then( this.handleConfigLoaded )
      .catch( this.handleConfigError );
  }

  handleConfigLoaded( res ) {
    this.setState( {
      configLoaded: true,
      config: res.data
    } );
  }

  handleConfigError( error ) {
    this.setState( {
      configLoaded: true,
      configError: error
    } );
  }

  renderError() {
    const { t } = this.props;
    return t( 'error.config_failed' );
  }

  render() {
    if ( !this.state.configLoaded ) {
      return <Loading size="6x" />;
    }

    if ( this.state.configLoaded && this.state.configError != null ) {
      return <React.Fragment>{this.renderError()}</React.Fragment>;
    }

    let widget = null;
    switch ( kelWidget ) {
      case 'browse':
        widget = <Browse config={this.state.config} />
        break;
      case 'search':
      default:
        widget = <Search config={this.state.config} />
    }

    return widget;
  }
}

export default withTranslation()( App ); 
