import React, {useMemo} from "react";
import {defineMessages, useIntl} from "react-intl";
import PageTabs from "components/PageTabs";
import history from "@iconify-icons/ri/history-fill";
import Transactions from "./components/Transactions";
import percent from "@iconify-icons/ri/percent-fill";
import Fee from "./components/Fee";

const messages = defineMessages({
    title: {defaultMessage: "Commerce"},
    transactions: {defaultMessage: "Transactions"},
    fee: {defaultMessage: "Fee"}
});

const Commerce = () => {
    const intl = useIntl();

    const tabs = useMemo(
        () => [
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
        ],
        [intl]
    );

    return (
        <PageTabs
            initial="transactions"
            title={intl.formatMessage(messages.title)}
            tabs={tabs}
        />
    );
};

export default Commerce;
