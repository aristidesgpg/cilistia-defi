import React from "react";
import {Box, Stack, Typography, Chip} from "@mui/material";
import {useTheme} from "@mui/material/styles";
import {defaultTo} from "lodash";

const ChartLegend = ({label, content, color, active = true}) => {
    const theme = useTheme();
    color = defaultTo(color, theme.palette.primary.main);

    return (
        <Stack
            direction="row"
            justifyContent="space-between"
            sx={{p: 0.3}}
            alignItems="center"
            spacing={2}>
            <Stack
                direction="row"
                alignItems="center"
                sx={{minWidth: 0}}
                spacing={1}>
                <Box
                    sx={{
                        flexShrink: 0,
                        borderRadius: theme.shape.borderRadius,
                        bgcolor: active ? color : "text.disabled",
                        width: 16,
                        height: 16
                    }}
                />

                <Typography variant="body2" color="text.secondary" noWrap>
                    {label}
                </Typography>
            </Stack>

            <Typography variant="body2">
                <Chip
                    component="span"
                    size="small"
                    variant="outlined"
                    label={content}
                />
            </Typography>
        </Stack>
    );
};

export default ChartLegend;
