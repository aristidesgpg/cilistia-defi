import React, {useCallback, useEffect, useState} from "react";
import {useTheme} from "@mui/material/styles";
import ResponsiveCard from "../ResponsiveWidgets/responsiveCard";
import RadialChart from "../RadialChart";
import {CardContent, CardHeader, Stack} from "@mui/material";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import ChartLegend from "../ChartLegend";
import {errorHandler, route, useRequest} from "services/Http";
import LoadingFallback from "components/LoadingFallback";

const messages = defineMessages({
    available: {defaultMessage: "Available"},
    onTrade: {defaultMessage: "On Trade"}
});

const WalletAccountChart = () => {
    const theme = useTheme();
    const [request, loading] = useRequest();
    const [aggregate, setAggregate] = useState({});
    const intl = useIntl();

    const fetchAggregate = useCallback(() => {
        request
            .get(route("wallet-account.aggregate-price"))
            .then((data) => setAggregate(data))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        fetchAggregate();
    }, [fetchAggregate]);

    return (
        <ResponsiveCard>
            <CardHeader title={<FormattedMessage defaultMessage="Wallet" />} />

            <CardContent sx={{flexGrow: 1}}>
                <Stack justifyContent="space-between" sx={{height: "100%"}}>
                    <LoadingFallback
                        content={aggregate}
                        fallbackIconSize={100}
                        loading={loading}>
                        {(aggregate) => (
                            <RadialChart
                                name={aggregate.currency}
                                color={theme.palette.chart.blue[0]}
                                value={aggregate.available}
                                total={aggregate.balance}
                                height={200}
                            />
                        )}
                    </LoadingFallback>

                    <Stack>
                        <ChartLegend
                            color={theme.palette.chart.blue[0]}
                            label={intl.formatMessage(messages.available)}
                            content={aggregate.formatted_available}
                            active={true}
                        />

                        <ChartLegend
                            color={theme.palette.chart.blue[0]}
                            label={intl.formatMessage(messages.onTrade)}
                            content={aggregate.formatted_balance_on_trade}
                            active={false}
                        />
                    </Stack>
                </Stack>
            </CardContent>
        </ResponsiveCard>
    );
};

WalletAccountChart.dimensions = {
    lg: {w: 3, h: 3, isResizable: false},
    md: {w: 3, h: 3, isResizable: false},
    sm: {w: 1, h: 3, isResizable: false},
    xs: {w: 1, h: 3, isResizable: false}
};

export default WalletAccountChart;
