import React, {
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState
} from "react";
import {FormattedMessage} from "react-intl";
import {Box, Grid} from "@mui/material";
import SimpleStatistic from "components/SimpleStatistic";
import {errorHandler, route, useRequest} from "services/Http";
import PeriodContext from "contexts/PeriodContext";
import {normalizeDates} from "utils/form";
import {tap} from "lodash";

const Statistics = () => {
    const [request, loading] = useRequest();
    const {from, to} = useContext(PeriodContext);
    const [statistics, setStatistics] = useState({});
    const [customer, setCustomer] = useState({});

    const params = useMemo(() => {
        return tap({from, to}, (values) => {
            normalizeDates(values, "from", "to");
        });
    }, [from, to]);

    const fetchTransaction = useCallback(() => {
        request
            .get(route("commerce-transaction.get-statistics", params))
            .then((data) => setStatistics(data))
            .catch(errorHandler());
    }, [request, params]);

    useEffect(() => {
        fetchTransaction();
    }, [fetchTransaction]);

    const fetchCustomer = useCallback(() => {
        request
            .get(route("commerce-customer.get-statistics", params))
            .then((data) => setCustomer(data))
            .catch(errorHandler());
    }, [request, params]);

    useEffect(() => {
        fetchCustomer();
    }, [fetchCustomer]);

    return (
        <Box component="div">
            <Grid container spacing={3}>
                <Grid item xs={12} md={4}>
                    <SimpleStatistic
                        title={
                            <FormattedMessage defaultMessage="Transactions" />
                        }
                        content={statistics.total}
                        change={statistics.total_change}
                        loading={loading}
                    />
                </Grid>

                <Grid item xs={12} md={4}>
                    <SimpleStatistic
                        title={
                            <FormattedMessage defaultMessage="Total Amount" />
                        }
                        content={statistics.formatted_price}
                        change={statistics.price_change}
                        loading={loading}
                    />
                </Grid>

                <Grid item xs={12} md={4}>
                    <SimpleStatistic
                        title={
                            <FormattedMessage defaultMessage="New Customers" />
                        }
                        content={customer.total}
                        change={customer.change}
                        loading={loading}
                    />
                </Grid>
            </Grid>
        </Box>
    );
};

export default Statistics;
