import {assign} from "lodash";
import context from "core/context";
import {normalizeResource} from "redux/helper";
import {createAsyncThunk, createSlice} from "@reduxjs/toolkit";
import {route, thunkRequest} from "services/Http";

const commercePaymentState = {
    id: null,
    settings: {
        pending_transactions: null,
        transaction_interval: null
    },
    resource: {
        error: null,
        loading: false,
        data: null
    }
};

export const fetchCommercePayment = createAsyncThunk(
    "commercePayment/fetch",
    (arg, api) => {
        const url = route("page.commerce-payment.get", {
            payment: api.getState("commercePayment.id")
        });

        return thunkRequest(api).get(url);
    }
);

export const initCommercePaymentState = () => {
    const normalized = normalizeResource(context.commercePayment, "resource");
    return assign({}, commercePaymentState, normalized);
};

const commercePayment = createSlice({
    name: "commercePayment",
    initialState: commercePaymentState,
    extraReducers: {
        [fetchCommercePayment.pending]: (state) => {
            state.resource = {
                ...state.resource,
                error: null,
                loading: true
            };
        },
        [fetchCommercePayment.rejected]: (state, action) => {
            state.resource = {
                ...state.resource,
                error: action.error.message,
                loading: false
            };
        },
        [fetchCommercePayment.fulfilled]: (state, action) => {
            state.resource = {
                ...state.resource,
                error: null,
                data: action.payload,
                loading: false
            };
        }
    }
});

export default commercePayment.reducer;
