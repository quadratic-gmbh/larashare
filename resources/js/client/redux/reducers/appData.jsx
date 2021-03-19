import { 
  FETCH_INIT_PENDING,
  FETCH_INIT_SUCCESS,
  FETCH_INIT_ERROR,
  APPLY_USER_SETTINGS
} from "../actions";

export default function(
  state = {
    pending: true,
    error: null,
    data: {}    
  }, 
  action
) {
  switch (action.type) {
    case FETCH_INIT_PENDING: {
      return Object.assign({}, state, {
        pending: true
      })
    }
    
    case FETCH_INIT_SUCCESS: {
      const { data } = action.payload
      return Object.assign({}, state, {
        pending: false,
        data: data,
        error: null
      })
    }
    
    case FETCH_INIT_ERROR: {
      const { error } = action.payload
      return Object.assign({}, state, {
        pending: false,
        error: error
      })
    }
    
    case APPLY_USER_SETTINGS: {
      const {settings, category} = action.payload      
      let newState = Object.assign({}, state);
     
      if(!newState.data.settings) {
        newState.data.settings = {};
      }     
      
      newState.data.settings[category] = settings;       
      
      return newState;
    }
    
    default:
      return state;
  }
}

