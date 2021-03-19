import axios from 'axios';

export default class ApiService {            
  searchQuery(query) {           
    let params = {
      method: 'post', 
      url: window.kelApiUrl + '/api/embed/search',
      data: {}
    };        
    
    if (query != undefined) {
      params.data = query;
    }
    
    if (kelEmbedId) {
      params.data.embed_id = kelEmbedId;
    }
    
    return axios(params);
  } 
  
  browseQuery(query) {
    let params = {
        method: 'post', 
        url: window.kelApiUrl + '/api/embed/browse',
        data: {}
      };        
      
      if (query != undefined) {
        params.data = query;
      }
      
      if (kelEmbedId) {
        params.data.embed_id = kelEmbedId;
      }
      
      return axios(params);
  }
  
  getClientConfig() {    
    let url = window.kelApiUrl + '/api/embed/client_config'
    if (kelEmbedId) {
      url += '?embed_id=' + kelEmbedId
    }
    return axios({
      method: 'get',
      url: url
    });
  }
};
