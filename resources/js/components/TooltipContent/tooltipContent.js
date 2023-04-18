import React from "react";
import {Paper, Stack, Typography} from "@mui/material";
import {isArray, isFunction} from "lodash";

const TooltipContent = ({active, label, payload, formatter}) => {
    if (!active) {
        return null;
    }

    const formatContent = (data) => {
        return isFunction(formatter)
            ? formatter(data.value, data.dataKey, data.payload)
            : data.value;
    };

    return (
        <Paper elevation={3}>
            <Stack sx={{p: 1}}>
                {label && (
                    <Typography
                        variant="body2"
                        color="text.secondary"
                        align="center">
                        {label}
                    </Typography>
                )}

                {payload?.map((data, index) => {
                    const content = formatContent(data, index);

                    return (
                        <Stack key={index} direction="row" spacing={1}>
                            <Typography
                                variant="subtitle2"
                                sx={{fontWeight: "bold", color: data.color}}>
                                {isArray(content) ? content[1] : data.name}:
                            </Typography>

                            <Typography variant="body2" noWrap>
                                {isArray(content) ? content[0] : content}
                            </Typography>
                        </Stack>
                    );
                })}
            </Stack>
        </Paper>
    );
};

export default TooltipContent;
