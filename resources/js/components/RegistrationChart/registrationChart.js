import React, {useCallback, useEffect, useState} from "react";
import {CardHeader, MenuItem, TextField} from "@mui/material";
import {
    Area,
    AreaChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis
} from "recharts";
import ResponsiveCard from "../ResponsiveWidgets/responsiveCard";
import {useTheme} from "@mui/material/styles";
import {errorHandler, route, useRequest} from "services/Http";
import Spin from "../Spin";
import {dayjs} from "utils";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {formatDate} from "utils/formatter";
import TooltipContent from "components/TooltipContent";

const messages = defineMessages({
    selectMonth: {defaultMessage: "Month"},
    total: {defaultMessage: "Total"}
});

const RegistrationChart = () => {
    const theme = useTheme();
    const intl = useIntl();
    const [data, setData] = useState([]);
    const [request] = useRequest();
    const [period, setPeriod] = useState(() => formatPeriod(dayjs()));

    const changePeriod = useCallback((e) => {
        setPeriod(e.target.value);
    }, []);

    const fetchData = useCallback(() => {
        const parseData = (data) => {
            return data?.map(({date, ...others}) => ({
                date: formatDate(date),
                ...others
            }));
        };

        const url = route("admin.statistics.registration-chart", {
            year: period.split(",")[1],
            month: period.split(",")[0]
        });

        request
            .get(url)
            .then((data) => setData(parseData(data)))
            .catch(errorHandler());
    }, [request, period]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    return (
        <ResponsiveCard>
            <CardHeader
                title={<FormattedMessage defaultMessage="Registration" />}
                subheader={<FormattedMessage defaultMessage="Statistics" />}
                action={
                    <SelectPeriod period={period} onChange={changePeriod} />
                }
            />

            <Spin sx={{flexGrow: 1, height: "100%"}}>
                <ResponsiveContainer width="100%" height="100%">
                    <AreaChart
                        data={data}
                        margin={{top: 50, right: 0, left: 0, bottom: 0}}>
                        <Tooltip
                            content={<TooltipContent />}
                            cursor={{stroke: theme.palette.text.disabled}}
                        />

                        <defs>
                            <linearGradient
                                id="registration"
                                x1="0"
                                y1="0"
                                x2="0"
                                y2="1">
                                <stop
                                    offset="5%"
                                    stopColor={theme.palette.primary.main}
                                    stopOpacity={0.9}
                                />
                                <stop
                                    offset="95%"
                                    stopColor={theme.palette.primary.dark}
                                    stopOpacity={0.9}
                                />
                            </linearGradient>
                        </defs>

                        <XAxis dataKey="date" hide />
                        <YAxis domain={[0, "dataMax"]} hide />

                        <Area
                            name={intl.formatMessage(messages.total)}
                            dataKey="total"
                            fill={`url(#registration)`}
                            fillOpacity={1}
                            stroke={theme.palette.primary.main}
                            strokeWidth={1}
                            type="monotone"
                        />
                    </AreaChart>
                </ResponsiveContainer>
            </Spin>
        </ResponsiveCard>
    );
};

const SelectPeriod = ({period, onChange}) => {
    const intl = useIntl();
    const options = [dayjs()];

    for (let i = 1; i < 12; i++) {
        options.push(dayjs().subtract(i, "month"));
    }

    return (
        <TextField
            size="small"
            onChange={onChange}
            value={period}
            label={intl.formatMessage(messages.selectMonth)}
            select>
            {options.map((option, key) => (
                <MenuItem value={formatPeriod(option)} key={key}>
                    {option.format("MMM, YYYY")}
                </MenuItem>
            ))}
        </TextField>
    );
};

const formatPeriod = (date) => {
    return `${date.month() + 1},${date.year()}`;
};

RegistrationChart.dimensions = {
    lg: {w: 8, h: 3, minW: 8, minH: 3},
    md: {w: 8, h: 3, minW: 8, minH: 3},
    sm: {w: 2, h: 3, minW: 2, minH: 3},
    xs: {w: 1, h: 3, minW: 1, minH: 3}
};

export default RegistrationChart;
