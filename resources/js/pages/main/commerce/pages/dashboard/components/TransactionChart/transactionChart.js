import React, {useCallback, useContext, useEffect, useState} from "react";
import {
    Bar,
    CartesianGrid,
    ComposedChart,
    Legend,
    Line,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis
} from "recharts";
import {Card, CardHeader, Typography} from "@mui/material";
import {useTheme} from "@mui/material/styles";
import {FormattedMessage} from "react-intl";
import TooltipContent from "components/TooltipContent";
import {formatDateTime} from "utils/formatter";
import {tap} from "lodash";
import {errorHandler, route, useRequest} from "services/Http";
import {normalizeDates} from "utils/form";
import PeriodContext from "contexts/PeriodContext";
import LoadingFallback from "components/LoadingFallback";

const TransactionChart = () => {
    const [data, setData] = useState([]);
    const {from, to} = useContext(PeriodContext);
    const [request, loading] = useRequest();

    const fetchData = useCallback(() => {
        const parseData = (data) => {
            return data?.map(({date, ...others}) => ({
                date: formatDateTime(date),
                ...others
            }));
        };

        const params = tap({from, to}, (values) => {
            normalizeDates(values, "from", "to");
        });

        request
            .get(route("commerce-transaction.get-chart", params))
            .then((data) => setData(parseData(data)))
            .catch(errorHandler());
    }, [request, from, to]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    return (
        <Card>
            <CardHeader
                title={<FormattedMessage defaultMessage="Transactions" />}
                subheader={<FormattedMessage defaultMessage="Overview" />}
            />

            <LoadingFallback content={data} loading={loading}>
                {(data) => <Chart data={data} />}
            </LoadingFallback>
        </Card>
    );
};

const Chart = ({data = []}) => {
    const theme = useTheme();

    const tooltipFormatter = (value, key, item) => {
        return {total_price: item.formatted_total_price}[key] ?? value;
    };

    return (
        <Typography component="div" variant="body2">
            <ResponsiveContainer width="100%" height={350}>
                <ComposedChart
                    data={data}
                    margin={{top: 4, bottom: 0, left: 4, right: 4}}>
                    <CartesianGrid
                        strokeDasharray="3 5"
                        stroke={theme.palette.text.disabled}
                        vertical={false}
                    />

                    <Tooltip
                        content={<TooltipContent />}
                        cursor={{stroke: theme.palette.text.disabled}}
                        formatter={tooltipFormatter}
                    />

                    <XAxis dataKey="date" hide />

                    <YAxis
                        yAxisId="right"
                        stroke={theme.palette.text.disabled}
                        orientation="right"
                        axisLine={false}
                        tickLine={false}
                    />

                    <YAxis
                        yAxisId="left"
                        stroke={theme.palette.text.disabled}
                        orientation="left"
                        axisLine={false}
                        tickLine={false}
                    />

                    <Legend verticalAlign="top" height={40} />

                    <Bar
                        name={<FormattedMessage defaultMessage="Amount" />}
                        fill={theme.palette.primary.dark}
                        yAxisId="left"
                        dataKey="total_price"
                        radius={[8, 8, 0, 0]}
                        barSize={20}
                    />

                    <Line
                        name={<FormattedMessage defaultMessage="Total" />}
                        stroke={theme.palette.text.secondary}
                        yAxisId="right"
                        type="monotone"
                        dataKey="total"
                    />
                </ComposedChart>
            </ResponsiveContainer>
        </Typography>
    );
};

// const data = new Array(16).fill({}).map(() => {
//     const totalPrice = random(2000, 5000);
//
//     return {
//         date: dayjs().toString(),
//         total_price: totalPrice,
//         formatted_total_price: `$${totalPrice}`,
//         total: random(100, 500)
//     };
// });

export default TransactionChart;
