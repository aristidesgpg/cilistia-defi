import React from "react";
import ResponsiveCard from "../ResponsiveWidgets/responsiveCard";
import RadialChart from "../RadialChart";
import {usePaymentAccount} from "hooks/accounts";
import {CardContent, CardHeader, Stack} from "@mui/material";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import ChartLegend from "../ChartLegend";
import {useTheme} from "@mui/material/styles";
import LoadingFallback from "components/LoadingFallback";

const messages = defineMessages({
    available: {defaultMessage: "Available"},
    onTrade: {defaultMessage: "On Trade"}
});

const PaymentAccountChart = () => {
    const theme = useTheme();
    const {account, loading} = usePaymentAccount();
    const intl = useIntl();

    return (
        <ResponsiveCard>
            <CardHeader title={<FormattedMessage defaultMessage="Payment" />} />

            <CardContent sx={{flexGrow: 1}}>
                <Stack justifyContent="space-between" sx={{height: "100%"}}>
                    <LoadingFallback
                        content={account}
                        fallbackIconSize={100}
                        loading={loading}>
                        {(account) => (
                            <RadialChart
                                name={account.currency}
                                color={theme.palette.primary.main}
                                value={account.available}
                                total={account.balance}
                                height={200}
                            />
                        )}
                    </LoadingFallback>

                    <Stack>
                        <ChartLegend
                            color={theme.palette.primary.main}
                            label={intl.formatMessage(messages.available)}
                            content={account.formatted_available}
                            active={true}
                        />

                        <ChartLegend
                            color={theme.palette.primary.main}
                            label={intl.formatMessage(messages.onTrade)}
                            content={account.formatted_balance_on_trade}
                            active={false}
                        />
                    </Stack>
                </Stack>
            </CardContent>
        </ResponsiveCard>
    );
};

PaymentAccountChart.dimensions = {
    lg: {w: 3, h: 3, isResizable: false},
    md: {w: 3, h: 3, isResizable: false},
    sm: {w: 1, h: 3, isResizable: false},
    xs: {w: 1, h: 3, isResizable: false}
};

export default PaymentAccountChart;
