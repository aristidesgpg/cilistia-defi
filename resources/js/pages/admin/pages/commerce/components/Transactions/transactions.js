import React, {useCallback, useEffect, useMemo, useState} from "react";
import {defineMessages, useIntl} from "react-intl";
import {errorHandler, route, useRequest} from "services/Http";
import Label from "components/Label";
import Table from "./components/Table";
import Spin from "components/Spin";
import CardTabs from "components/CardTabs";
import {Card} from "@mui/material";

const messages = defineMessages({
    pending: {defaultMessage: "Pending"},
    completed: {defaultMessage: "Completed"},
    canceled: {defaultMessage: "Canceled"},
    all: {defaultMessage: "All"}
});

const Transactions = () => {
    const intl = useIntl();
    const [request, loading] = useRequest();

    const [statistics, setStatistics] = useState({
        pending: 0,
        completed: 0,
        canceled: 0,
        all: 0
    });

    const fetchStatistics = useCallback(() => {
        request
            .get(route("admin.commerce-transaction.get-status-statistics"))
            .then((data) => setStatistics(data))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        fetchStatistics();
    }, [fetchStatistics]);

    const tabs = useMemo(
        () => [
            {
                label: intl.formatMessage(messages.all),
                icon: <Label color="default">{statistics.all}</Label>,
                component: <Table />
            },
            {
                label: intl.formatMessage(messages.pending),
                icon: <Label color="info">{statistics.pending}</Label>,
                component: <Table status="pending" />
            },
            {
                label: intl.formatMessage(messages.completed),
                icon: <Label color="success">{statistics.completed}</Label>,
                component: <Table status="completed" />
            },
            {
                label: intl.formatMessage(messages.canceled),
                icon: <Label color="error">{statistics.canceled}</Label>,
                component: <Table status="canceled" />
            }
        ],
        [intl, statistics]
    );

    return (
        <Card>
            <Spin spinning={loading}>
                <CardTabs
                    variant="scrollable"
                    allowScrollButtonsMobile
                    scrollButtons="auto"
                    tabs={tabs}
                />
            </Spin>
        </Card>
    );
};

export default Transactions;
