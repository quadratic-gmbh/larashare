import React, { Component } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSpinner } from '@fortawesome/free-solid-svg-icons'

export default class Loading extends Component {

  constructor() {
    super();
    this.state = {};
  }


  render()  {
    const {style, size} = this.props;
    return <div style={style} className="kel-loading">
      <FontAwesomeIcon icon={faSpinner} spin={true} size={size}/>
    </div>
  }
}