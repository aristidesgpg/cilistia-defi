import {createAsyncThunk, createSlice} from "@reduxjs/toolkit";
import {route, thunkRequest} from "services/Http";
import context from "core/context";
import {assign} from "lodash";

const settingsState = {
    layout: "default",
    locales: {},
    modules: {},
    installer: {
        display: false,
        license: false
    },
    windowSize: {
        width: 1200,
        height: 900
    },
    locale: {
        error: null,
        loading: false,
        data: {
            locale: "en",
            messages: {}
        }
    },
    baseCurrency: "USD",
    recaptcha: {
        enable: false,
        sitekey: "",
        size: "normal"
    },
    theme: {
        mode: "dark",
        direction: "ltr",
        color: "orange"
    },
    brand: {
        favicon_url: null,
        logo_url: null,
        support_url: null,
        terms_url: null,
        policy_url: null
    }
};

export const initSettingsState = () => {
    return assign({}, settingsState, context.settings);
};

/**
 * BEGIN: Async Actions
 */
export const fetchLocale = createAsyncThunk(
    "settings/fetchLocale",
    (arg, api) => {
        return thunkRequest(api).post(route("locale.get"));
    }
);

export const updateLocale = createAsyncThunk(
    "settings/updateLocale",
    (locale, api) => {
        return thunkRequest(api).post(route("locale.set"), {locale});
    }
);

export const fetchBrand = createAsyncThunk(
    "settings/fetchBrand",
    (arg, api) => {
        return thunkRequest(api).get(route("settings.brand"));
    }
);

export const fetchTheme = createAsyncThunk(
    "settings/fetchTheme",
    (arg, api) => {
        return thunkRequest(api).get(route("settings.theme"));
    }
);

/**
 * END: Async Actions
 */

const settings = createSlice({
    name: "settings",
    initialState: settingsState,
    reducers: {
        setWindowSize: (state, action) => {
            state.windowSize = action.payload;
        },
        setGridSpacing: (state, action) => {
            state.gridSpacing = action.payload;
        },
        setRecaptcha: (state, action) => {
            state.recaptcha = action.payload;
        },
        setLocaleData: (state, action) => {
            state.localeData = action.payload;
        }
    },
    extraReducers: {
        [fetchLocale.pending]: (state) => {
            state.locale = {
                ...state.locale,
                error: null,
                loading: true
            };
        },
        [fetchLocale.rejected]: (state, action) => {
            state.locale = {
                ...state.locale,
                error: action.error.message,
                loading: false
            };
        },
        [fetchLocale.fulfilled]: (state, action) => {
            state.locale = {
                ...state.locale,
                error: null,
                data: action.payload,
                loading: false
            };
        },

        [updateLocale.pending]: (state) => {
            state.locale = {
                ...state.locale,
                error: null,
                loading: true
            };
        },
        [updateLocale.rejected]: (state, action) => {
            state.locale = {
                ...state.locale,
                error: action.error.message,
                loading: false
            };
        },
        [updateLocale.fulfilled]: (state, action) => {
            state.locale = {
                ...state.locale,
                error: null,
                data: action.payload,
                loading: false
            };
        },
        [fetchBrand.fulfilled]: (state, action) => {
            state.brand = action.payload;
        },
        [fetchTheme.fulfilled]: (state, action) => {
            state.theme = action.payload;
        }
    }
});

export const {setWindowSize, setGridSpacing, setRecaptcha, setLocaleData} =
    settings.actions;

export default settings.reducer;
