import React, { Component } from 'react';
import ApiService from '../service/ApiService';
import BrowseFilter from './BrowseFilter';
import Loading from './Loading';
import SearchResults from './SearchResults';
import SearchMap from './SearchMap';

export default class Browse extends Component {

  constructor( props ) {
    super( props );
    this.state = {
      data: null,
      errors: null,
      loading: false      
    };
    
    this.apiService = new ApiService();
    this.browseFilter = null;
    this.searchMap = null;
    this.searchResults = null;

    this.performSearch = this.performSearch.bind(this);
    this.handleSearchResult = this.handleSearchResult.bind(this);
    this.handleSearchError = this.handleSearchError.bind(this);
    this.getBrowseFilter = this.getBrowseFilter.bind(this);
    this.getSearchMap = this.getSearchMap.bind(this);
    this.getSearchResults = this.getSearchResults.bind(this);
  }
  
  getSearchResults() {
    return this.searchResults;
  }
  
  getBrowseFilter() {
    return this.browseFilter;
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
    this.apiService.browseQuery(params)
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
    this.getBrowseFilter().updateLocation(location);
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
    
    return <React.Fragment>
      <BrowseFilter childRef={el => {this.browseFilter = el}} performSearch={this.performSearch} searchMap={this.getSearchMap} location={location} config={config} errors={this.state.errors} />
      <SearchMap childRef={el => {this.searchMap = el}} searchResults={this.getSearchResults} browseFilter={this.getBrowseFilter} data={this.state.data} searchMode={false}/>
      {!this.state.loading && <SearchResults childRef={el => {this.searchResults = el}} browseFilter={this.getBrowseFilter} searchMap={this.getSearchMap} data={this.state.data} searchMode={false}/>}
      {this.state.loading && <Loading size="8x" />}
    </React.Fragment>;
  }
}