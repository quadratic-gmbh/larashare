import React, {Component, Suspense} from 'react';
import {Route, withRouter} from 'react-router-dom';
import ReactDOM from 'react-dom';
import App from "./App";
import Loading from "./components/Loading";
  
class AppWrapper extends Component {
  constructor(props) {
      super(props);              
      let currentScriptURL = new URL(document.currentScript.src); 
      let embedId = currentScriptURL.searchParams.get("id")      
      let widget = currentScriptURL.searchParams.get("widget")
      window.kelEmbedId = embedId;      
      window.kelWidget = widget;
  }
    
  componentDidMount() {
  }
    
  componentDidUpdate(prevProps) {
    if (this.props.location !== prevProps.location) {
      window.scrollTo(0, 0)
    }
  }

  render() {            
    return <Suspense fallback={(<Loading />)}>
      <App/>
    </Suspense>;  
  }
}



export default AppWrapper;