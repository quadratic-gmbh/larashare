import React, { Component } from 'react';
import ApiService from '../service/ApiService';
import SearchFilter from './SearchFilter';
import Loading from './Loading';
import SearchResults from './SearchResults';
import SearchMap from './SearchMap';

export default class Search extends Component {

  constructor( props ) {
    super( props );
    this.state = {
      data: null,
      errors: null,
      loading: false      
    };
    
    this.apiService = new ApiService();
    this.searchFilter = null;
    this.searchMap = null;
    this.searchResults = null;

    this.performSearch = this.performSearch.bind(this);
    this.handleSearchResult = this.handleSearchResult.bind(this);
    this.handleSearchError = this.handleSearchError.bind(this);
    this.getSearchFilter = this.getSearchFilter.bind(this);
    this.getSearchMap = this.getSearchMap.bind(this);
    this.getSearchResults = this.getSearchResults.bind(this);
  }
  
  getSearchResults() {
    return this.searchResults;
  }
  
  getSearchFilter() {
    return this.searchFilter;
  }  
  
  getSearchMap() {
    return this.searchMap;
  }
  
  componentDidMount() {
    this.performSearch();    
  }
  
  performSearch(params) {
    this.setState({
      loading: true
    });
    this.apiService.searchQuery(params)
    .then(this.handleSearchResult)
    .catch(this.handleSearchError);
  }
  
  handleSearchResult(result) {    
    this.setState({
      data: result.data,
      errors: null,
      loading: false
    })    
    
    let location = result.data.location;
    this.getSearchFilter().updateLocation(location);
  }
  
  handleSearchError(error) {
    let newState = {
      loading: false
    }
    if (error.response) {
      if (error.response.status == 400) {
        newState.errors = error.response.data.errors;
      } else {
        newState.errors = true;        
      }                 
    } else if (error.request) {
//      console.error(error.request);
      newState.errors = true;      
    } else {
//      console.error(error.message);
      newState.errors = true;
    }
    
    this.setState(newState);
  }

  render()  {
    const {config} = this.props;
    let curPosLon = null;
    let curPosLat = null;
    if (this.state.data && this.state.data.location) {      
      curPosLon = this.state.data.location.lon;
      curPosLat = this.state.data.location.lat; 
    }
    let searchMode = this.state.data ? this.state.data.search_mode : null;
    
    return <React.Fragment>
      <SearchFilter childRef={el => {this.searchFilter = el}} performSearch={this.performSearch} searchMap={this.getSearchMap} location={location} config={config} errors={this.state.errors} />
  		<SearchMap childRef={el => {this.searchMap = el}} searchResults={this.getSearchResults} curPosLon={curPosLon} curPosLat={curPosLat} searchFilter={this.getSearchFilter} data={this.state.data} searchMode={searchMode}/>
      {!this.state.loading && <SearchResults childRef={el => {this.searchResults = el}} searchFilter={this.getSearchFilter} searchMap={this.getSearchMap} data={this.state.data} searchMode={searchMode}/>}
      {this.state.loading && <Loading size="8x" />}
    </React.Fragment>;
  }
}