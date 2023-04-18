import React, {useCallback, useEffect, useState} from "react";
import {
    Area,
    AreaChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis
} from "recharts";
import {useTheme} from "@mui/material/styles";
import {errorHandler, route, useRequest} from "services/Http";
import {formatDate} from "utils/formatter";
import {defineMessages, useIntl} from "react-intl";
import TooltipContent from "components/TooltipContent";

const messages = defineMessages({
    received: {defaultMessage: "Received"}
});

const Chart = () => {
    const theme = useTheme();
    const intl = useIntl();
    const [data, setData] = useState([]);
    const [request] = useRequest();

    const fetchData = useCallback(() => {
        const parseData = (data) => {
            return data?.map(({date, ...others}) => ({
                date: formatDate(date),
                ...others
            }));
        };

        request
            .get(route("payment.daily-chart"))
            .then((data) => setData(parseData(data)))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    const tooltipFormatter = (value, key, item) => {
        return {total_received: item.formatted_total_received}[key] ?? value;
    };

    const color = theme.palette.primary.main;

    return (
        <ResponsiveContainer width="100%" height={120}>
            <AreaChart
                data={data}
                margin={{top: 5, right: 0, left: 0, bottom: 0}}>
                <Tooltip
                    content={<TooltipContent />}
                    cursor={{stroke: theme.palette.text.disabled}}
                    formatter={tooltipFormatter}
                />

                <XAxis dataKey="date" hide />
                <YAxis domain={[0, "dataMax"]} hide />

                <Area
                    name={intl.formatMessage(messages.received)}
                    type="monotone"
                    dataKey="total_received"
                    stroke={color}
                    strokeWidth={4}
                    fillOpacity={0.3}
                    fill={color}
                />
            </AreaChart>
        </ResponsiveContainer>
    );
};

export default Chart;
