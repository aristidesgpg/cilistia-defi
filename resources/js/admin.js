import "utils/nonce";
import React, {useEffect} from "react";
import ReactDOM from "react-dom";
import {BrowserRouter, Route, Routes} from "react-router-dom";
import {Provider, useDispatch} from "react-redux";
import Bootstrap from "./core/bootstrap";
import Localization from "./core/localization";
import store from "redux/store/admin";
import Middleware from "components/Middleware";
import {auth as authRule, can, guest as guestRule} from "utils/middleware";
import {lazy} from "utils/index";
import router from "router/router";
import InstallerBootstrap from "components/InstallerBootstrap";
import PageRefresh from "components/PageRefresh";
import {
    fetchCountries,
    fetchOperatingCountries,
    fetchSupportedCurrencies,
    fetchWallets
} from "redux/slices/global";
import ScrollToTop from "components/ScrollToTop";

const AdminLayout = lazy(() =>
    import(/* webpackChunkName: 'admin' */ "layouts/Admin")
);

const Application = () => {
    const dispatch = useDispatch();

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
                    <Middleware rules={guestRule("admin.home")}>
                        <PageRefresh />
                    </Middleware>
                }
            />

            <Route
                path={router.getRoutePath("admin")}
                element={
                    <Middleware rules={can("access_control_panel")}>
                        <AdminLayout />
                    </Middleware>
                }
            />

            <Route
                path={router.getRoutePath("main")}
                element={
                    <Middleware rules={authRule("auth.login")}>
                        <PageRefresh />
                    </Middleware>
                }
            />
        </Routes>
    );
};

ReactDOM.render(
    <Provider store={store}>
        <BrowserRouter>
            <Localization>
                <Bootstrap>
                    <InstallerBootstrap>
                        <ScrollToTop />
                        <Application />
                    </InstallerBootstrap>
                </Bootstrap>
            </Localization>
        </BrowserRouter>
    </Provider>,
    document.getElementById("root")
);
