import {createAsyncThunk, createSlice} from "@reduxjs/toolkit";
import {route, thunkRequest} from "services/Http";
import context from "core/context";
import {assign} from "lodash";

const authState = {
    user: null,
    credential: "email",
    userSetup: false
};

export const initAuthState = () => {
    return assign({}, authState, context.auth);
};

export const fetchUser = createAsyncThunk("auth/fetchUser", (arg, api) => {
    return thunkRequest(api).get(route("user.get"));
});

const auth = createSlice({
    name: "auth",
    initialState: authState,
    extraReducers: {
        [fetchUser.fulfilled]: (state, action) => {
            state.user = action.payload;
        }
    }
});

export default auth.reducer;
