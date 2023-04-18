import React from "react";
import {useTheme} from "@mui/material/styles";
import {formatValue} from "utils/formatter";
import {defaultTo} from "lodash";
import {lightenColor} from "utils/helpers";
import {
    LabelList,
    PolarAngleAxis,
    RadialBar,
    RadialBarChart,
    ResponsiveContainer
} from "recharts";
import {useUniqueId} from "hooks/useUniqueId";

const RadialChart = ({name, value, total, color, height = 300}) => {
    const theme = useTheme();
    const getUniqueId = useUniqueId("radial_");

    color = defaultTo(color, theme.palette.primary.main);

    const data = [{name, value}];

    return (
        <ResponsiveContainer width="100%" height={height}>
            <RadialBarChart data={data} innerRadius="60%" barSize={20}>
                <PolarAngleAxis
                    type="number"
                    domain={[0, total]}
                    angleAxisId={0}
                    tick={false}
                />

                <defs>
                    <linearGradient
                        x1="0"
                        y1="0"
                        x2="1"
                        y2="1"
                        id={getUniqueId("bar")}>
                        <stop
                            offset="5%"
                            stopColor={lightenColor(color, 20)}
                            stopOpacity={0.9}
                        />
                        <stop
                            offset="95%"
                            stopColor={color}
                            stopOpacity={0.9}
                        />
                    </linearGradient>
                </defs>

                <RadialBar
                    dataKey="value"
                    fill={`url(#${getUniqueId("bar")})`}
                    background={{fill: theme.palette.background.neutral}}
                    cornerRadius={10}
                    clockWise>
                    <LabelList position="center" content={<CustomLabel />} />
                </RadialBar>
            </RadialBarChart>
        </ResponsiveContainer>
    );
};

const CustomLabel = ({name, value, cx, cy}) => {
    const theme = useTheme();

    return (
        <React.Fragment>
            <text
                x="50%"
                y={cy - 12}
                dominantBaseline="middle"
                textAnchor="middle">
                <tspan
                    style={{
                        ...theme.typography.body2,
                        fill: theme.palette.text.secondary
                    }}>
                    {name}
                </tspan>
            </text>

            <text
                x="50%"
                y={cy + 12}
                dominantBaseline="middle"
                textAnchor="middle">
                <tspan
                    style={{
                        ...theme.typography.subtitle2,
                        fill: theme.palette.text.primary
                    }}>
                    {formatValue(value)}
                </tspan>
            </text>
        </React.Fragment>
    );
};

export default RadialChart;
