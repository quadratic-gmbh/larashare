import { createStore, applyMiddleware } from "redux";
import thunkMiddleware from 'redux-thunk';
import { fetchInit } from './actions';
import rootReducer from "./reducers";

export default createStore(
    rootReducer,
    applyMiddleware(thunkMiddleware)
    );
