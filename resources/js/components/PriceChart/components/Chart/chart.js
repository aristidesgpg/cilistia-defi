import React, {useEffect, useState} from "react";
import {
    Area,
    AreaChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis
} from "recharts";
import {lightenColor} from "utils";
import {defaultTo} from "lodash";
import {useTheme} from "@mui/material/styles";
import {errorHandler, route, useRequest} from "services/Http";
import Spin from "components/Spin";
import {formatDateTime} from "utils/formatter";
import TooltipContent from "components/TooltipContent";
import {defineMessages, useIntl} from "react-intl";

const messages = defineMessages({
    price: {defaultMessage: "Price"}
});

const Chart = ({selectedWallet, range}) => {
    const [data, setData] = useState([]);
    const [request, loading] = useRequest();

    useEffect(() => {
        if (selectedWallet.isNotEmpty()) {
            const parseData = (data) => {
                return data?.map(({timestamp, ...others}) => ({
                    date: formatDateTime(timestamp),
                    ...others
                }));
            };

            const url = route("wallet.market-chart", {
                wallet: selectedWallet.id
            });

            request
                .post(url, {range})
                .then((data) => setData(parseData(data)))
                .catch(errorHandler());
        }
    }, [selectedWallet, request, range]);

    if (selectedWallet.isEmpty()) {
        return <ChartBody data={data} />;
    }

    const coin = selectedWallet.coin;

    return (
        <Spin sx={{height: "100%"}} spinning={loading}>
            <ChartBody color={coin.color} name={coin.identifier} data={data} />
        </Spin>
    );
};

const ChartBody = ({color, name = "unknown", data = []}) => {
    const theme = useTheme();
    const intl = useIntl();

    color = defaultTo(color, theme.palette.primary.main);

    const id = name + "Color";
    const toColor = lightenColor(color, 20);
    const fromColor = color;

    const tooltipFormatter = (value, key, item) => {
        return {price: item.formatted_price}[key] ?? value;
    };

    return (
        <ResponsiveContainer width="100%" height="100%">
            <AreaChart
                data={data}
                margin={{top: 5, right: 0, left: 0, bottom: 0}}>
                <Tooltip
                    content={<TooltipContent />}
                    cursor={{stroke: theme.palette.text.disabled}}
                    formatter={tooltipFormatter}
                />

                <defs>
                    <linearGradient x1="0" y1="0" x2="1" y2="1" id={id}>
                        <stop
                            offset="5%"
                            stopColor={fromColor}
                            stopOpacity={0.9}
                        />
                        <stop
                            offset="95%"
                            stopColor={toColor}
                            stopOpacity={0.9}
                        />
                    </linearGradient>
                </defs>

                <XAxis dataKey="date" hide />
                <YAxis domain={["dataMin", "dataMax"]} hide />

                <Area
                    name={intl.formatMessage(messages.price)}
                    dataKey="price"
                    strokeWidth={0}
                    stroke={fromColor}
                    type="linear"
                    fill={`url(#${id})`}
                    fillOpacity={1}
                />
            </AreaChart>
        </ResponsiveContainer>
    );
};

export default Chart;
