import React from 'react';
import ReactDOM from 'react-dom';
import AppWrapper from './AppWrapper';
import 'babel-polyfill';
import { Provider } from "react-redux";
import configureStore from './redux/configureStore';
import './i18n';

const store = configureStore();



ReactDOM.render(    
    <Provider store={store}>
      <AppWrapper></AppWrapper>
    </Provider>,    
    document.getElementById('kel-widget')
);
