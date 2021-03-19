import React, { Component } from 'react';
import classNames from 'classnames';


export default class Collapse extends Component {

  constructor(props) {
    super(props);
    this.state = {
      active: props.active,
      transition: false,
      height: null
    };
    
    this.container = React.createRef();
    
    this.show = this.show.bind(this);
    this.hide = this.hide.bind(this);
    this.containerHeight = this.containerHeight.bind(this);
  }
  
  componentDidUpdate(prevProps) {
    if (prevProps.active != this.props.active) {          
      if(this.props.active) {
        this.show();
      } else {
        this.hide();
      }           
    }
  }
  
  show() {
    this.setState({      
      transition: true,
      height: 0
    });
    
    setTimeout(() => {
      this.setState({      
        height: this.containerHeight() + "px"       
      });
      
      setTimeout(() => {
        this.setState({
          transition: false,
          height: null,
          active: true
        })
      }, 350);
    },100)
  }
  
  hide() {    
    this.setState({      
      transition: true,
      height: this.containerHeight() + "px"
    });    
    setTimeout(() => {      
      this.setState({
        height: 0
      });
      
      setTimeout(() => {
        this.setState({
          transition: false,
          height: null,
          active: false
        })
      }, 350);
    },100);
  }
  
  containerHeight() {
    if (!this.container.current) {
      return null;
    }
    
    return this.container.current.scrollHeight;
  }

  render()  {        
    let wrapperClasses = classNames(      
      {"kel-collapsing": this.state.transition},
      {"kel-collapse show": !this.state.transition && this.state.active},
      {"kel-collapse": !this.state.transition && !this.state.active}
    );    
    
    let style = {};
    if(this.state.height != null) {
      style.height = this.state.height;
    }
    
    return <div ref={this.container} className={wrapperClasses} style={style}>
      {this.props.children}
    </div>
  }
}
