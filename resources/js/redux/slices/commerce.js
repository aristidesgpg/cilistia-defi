import {createAsyncThunk, createSlice} from "@reduxjs/toolkit";
import {route, thunkRequest} from "services/Http";

const commerceState = {
    account: {
        error: null,
        loading: false,
        data: null
    }
};

export const fetchCommerceAccount = createAsyncThunk(
    "commerce/fetchAccount",
    (arg, api) => thunkRequest(api).get(route("commerce-account.get"))
);

const commerce = createSlice({
    name: "commerce",
    initialState: commerceState,
    extraReducers: {
        [fetchCommerceAccount.pending]: (state) => {
            state.account = {
                ...state.account,
                error: null,
                loading: true
            };
        },
        [fetchCommerceAccount.rejected]: (state, action) => {
            state.account = {
                ...state.account,
                error: action.error.message,
                loading: false
            };
        },
        [fetchCommerceAccount.fulfilled]: (state, action) => {
            state.account = {
                ...state.account,
                error: null,
                data: action.payload,
                loading: false
            };
        }
    }
});

export default commerce.reducer;
