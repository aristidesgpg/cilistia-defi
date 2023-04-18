import {createStore} from "redux/helper";
import {combineReducers} from "@reduxjs/toolkit";
import settingsReducer, {initSettingsState} from "redux/slices/settings";
import authReducer, {initAuthState} from "redux/slices/auth";
import giftcardsReducer from "redux/slices/giftcards";
import globalReducer from "redux/slices/global";
import paymentReducer from "redux/slices/payment";
import commerceReducer from "redux/slices/commerce";
import layoutReducer from "redux/slices/layout";
import landingReducer from "redux/slices/landing";
import userReducer from "redux/slices/user";
import walletReducer from "redux/slices/wallet";

const reducer = combineReducers({
    auth: authReducer,
    giftcards: giftcardsReducer,
    global: globalReducer,
    payment: paymentReducer,
    commerce: commerceReducer,
    settings: settingsReducer,
    layout: layoutReducer,
    landing: landingReducer,
    user: userReducer,
    wallet: walletReducer
});

const main = createStore(reducer, {
    auth: initAuthState(),
    settings: initSettingsState()
});

export default main;
