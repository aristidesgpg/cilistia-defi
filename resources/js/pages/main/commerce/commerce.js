import React from "react";
import router from "router/router";
import {Navigate, Route, Routes} from "react-router-dom";
import Result404 from "components/Result404";
import Dashboard from "./pages/dashboard";
import Middleware from "components/Middleware";
import Account from "./pages/account";
import {useCommerceAccount} from "hooks/accounts";
import Payments from "./pages/payments";
import Payment from "./pages/payment";
import Customers from "./pages/customers";
import Customer from "./pages/customer";
import Transactions from "./pages/transactions";

const Commerce = () => {
    const {account} = useCommerceAccount();
    const indexRoute = router.getRoutePath("main.commerce.dashboard");

    return (
        <Routes>
            <Route index element={<Navigate to={indexRoute} />} />

            <Route
                path={router.getRoutePath("main.commerce.dashboard")}
                element={
                    <Middleware rules={requireAccount(account)}>
                        <Dashboard />
                    </Middleware>
                }
            />

            <Route
                path={router.getRoutePath("main.commerce.payment")}
                element={
                    <Middleware rules={requireAccount(account)}>
                        <Payment />
                    </Middleware>
                }
            />

            <Route
                path={router.getRoutePath("main.commerce.payments")}
                element={
                    <Middleware rules={requireAccount(account)}>
                        <Payments />
                    </Middleware>
                }
            />

            <Route
                path={router.getRoutePath("main.commerce.transactions")}
                element={
                    <Middleware rules={requireAccount(account)}>
                        <Transactions />
                    </Middleware>
                }
            />

            <Route
                path={router.getRoutePath("main.commerce.customer")}
                element={
                    <Middleware rules={requireAccount(account)}>
                        <Customer />
                    </Middleware>
                }
            />

            <Route
                path={router.getRoutePath("main.commerce.customers")}
                element={
                    <Middleware rules={requireAccount(account)}>
                        <Customers />
                    </Middleware>
                }
            />

            <Route
                path={router.getRoutePath("main.commerce.account")}
                element={<Account />}
            />

            <Route path="*" element={<Result404 />} />
        </Routes>
    );
};

export function requireAccount(account) {
    return (next) => (auth) => {
        if (account.isEmpty()) {
            return (
                <Navigate
                    to={router.generatePath("main.commerce.account")}
                    replace
                />
            );
        }

        return next(auth);
    };
}

export default Commerce;
