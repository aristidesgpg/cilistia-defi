import "utils/nonce";
import React, {useEffect} from "react";
import ReactDOM from "react-dom";
import {BrowserRouter, Route, Routes} from "react-router-dom";
import {Provider, useDispatch} from "react-redux";
import Bootstrap from "./core/bootstrap";
import Localization from "./core/localization";
import store from "redux/store/main";
import Middleware from "components/Middleware";
import router from "router/router";
import {auth as authRule, can, guest as guestRule} from "utils/middleware";
import {lazy} from "utils/index";
import {useAuth} from "models/Auth";
import PageRefresh from "components/PageRefresh";
import {fetchWalletAccounts} from "redux/slices/wallet";
import {fetchCommerceAccount} from "redux/slices/commerce";
import {fetchPaymentAccount} from "redux/slices/payment";
import {fetchVerification} from "redux/slices/user";
import {
    fetchCountries,
    fetchOperatingCountries,
    fetchSupportedCurrencies,
    fetchWallets
} from "redux/slices/global";
import ScrollToTop from "components/ScrollToTop";
import WagmiClient from 'services/Wagmi'
import { WagmiConfig } from 'wagmi'
import "./global.css"

const MainLayout = lazy(() =>
    import(/* webpackChunkName: 'main' */ "layouts/Main")
);

const AuthLayout = lazy(() =>
    import(/* webpackChunkName: 'auth' */ "layouts/Auth")
);

const Application = () => {
    const auth = useAuth();
    const dispatch = useDispatch();

    useEffect(() => {
        if (auth.check()) {
            dispatch(fetchWalletAccounts());
            dispatch(fetchCommerceAccount());
            dispatch(fetchPaymentAccount());
            dispatch(fetchVerification());
        }
    }, [dispatch, auth]);

    useEffect(() => {
        dispatch(fetchCountries());
        dispatch(fetchSupportedCurrencies());
        dispatch(fetchOperatingCountries());
        dispatch(fetchWallets());
    }, [dispatch]);

    return (
        <Routes>
            <Route
                path={router.getRoutePath("auth")}
                element={
                    <Middleware rules={guestRule("main.home")}>
                        <AuthLayout />
                    </Middleware>
                }
            />

            <Route
                path={router.getRoutePath("admin")}
                element={
                    <Middleware rules={can("access_control_panel")}>
                        <PageRefresh />
                    </Middleware>
                }
            />

            <Route
                path={router.getRoutePath("main")}
                element={
                    <Middleware rules={authRule("auth.login")}>
                        <MainLayout />
                    </Middleware>
                }
            />
        </Routes>
    );
};

ReactDOM.render(
    <Provider store={store}>
        <WagmiConfig client={WagmiClient}>
            <BrowserRouter>
                <Localization>
                    <Bootstrap>
                        <ScrollToTop />
                        <Application />
                    </Bootstrap>
                </Localization>
            </BrowserRouter>
        </WagmiConfig>
    </Provider>,
    document.getElementById("root")
);
