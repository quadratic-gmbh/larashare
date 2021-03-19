import React, { Component } from 'react';

export default class FormError extends Component {
  constructor(props) {
    super(props);
  }
  
  render() {
    const { errors, field } = this.props;
    if ( !errors || errors[field] == undefined ) {
      return null;
    }

    return <div className="kel-form-error">
      {errors[field][0]}
    </div>
  }
}