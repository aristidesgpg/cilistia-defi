import React, {useMemo} from "react";
import {defineMessages, useIntl} from "react-intl";
import fileList from "@iconify-icons/ri/file-list-2-fill";
import history from "@iconify-icons/ri/history-fill";
import percent from "@iconify-icons/ri/percent-fill";
import Wallets from "./components/Wallets";
import Transactions from "./components/Transactions";
import Fee from "./components/Fee";
import PageTabs from "components/PageTabs";

const messages = defineMessages({
    wallets: {defaultMessage: "Wallets"},
    transactions: {defaultMessage: "Transactions"},
    fee: {defaultMessage: "Fee"},
    title: {defaultMessage: "Wallets"}
});

const WalletsPage = () => {
    const intl = useIntl();

    const tabs = useMemo(() => {
        return [
            {
                value: "wallets",
                label: intl.formatMessage(messages.wallets),
                icon: fileList,
                component: <Wallets />
            },
            {
                value: "transactions",
                label: intl.formatMessage(messages.transactions),
                icon: history,
                component: <Transactions />
            },
            {
                value: "fee",
                label: intl.formatMessage(messages.fee),
                icon: percent,
                component: <Fee />
            }
        ];
    }, [intl]);

    return (
        <PageTabs
            initial="wallets"
            title={intl.formatMessage(messages.title)}
            tabs={tabs}
        />
    );
};

export default WalletsPage;
