import {configureStore} from "@reduxjs/toolkit";
import {cloneDeep} from "lodash";

export function createStore(storeReducer, initialState) {
    return configureStore({
        devTools: process.env.NODE_ENV !== "production",
        reducer: storeReducer,
        preloadedState: initialState
    });
}

export function normalizeResource(state, ...params) {
    const normalized = cloneDeep(state);

    params.forEach((key) => {
        normalized[key] = {
            loading: false,
            data: state[key],
            error: null
        };
    });

    return normalized;
}
