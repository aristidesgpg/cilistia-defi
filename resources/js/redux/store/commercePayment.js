import {createStore} from "redux/helper";
import settingsReducer, {initSettingsState} from "redux/slices/settings";
import commercePaymentReducer, {
    initCommercePaymentState
} from "redux/slices/commercePayment";
import {combineReducers} from "@reduxjs/toolkit";

const reducer = combineReducers({
    commercePayment: commercePaymentReducer,
    settings: settingsReducer
});

const commercePayment = createStore(reducer, {
    commercePayment: initCommercePaymentState(),
    settings: initSettingsState()
});

export default commercePayment;
