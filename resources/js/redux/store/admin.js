import {createStore} from "redux/helper";
import {combineReducers} from "@reduxjs/toolkit";
import settingsReducer, {initSettingsState} from "redux/slices/settings";
import authReducer, {initAuthState} from "redux/slices/auth";
import globalReducer from "redux/slices/global";
import layoutReducer from "redux/slices/layout";

const reducer = combineReducers({
    auth: authReducer,
    settings: settingsReducer,
    global: globalReducer,
    layout: layoutReducer
});

const admin = createStore(reducer, {
    auth: initAuthState(),
    settings: initSettingsState()
});

export default admin;
