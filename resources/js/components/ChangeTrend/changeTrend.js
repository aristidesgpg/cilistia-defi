import React, {useMemo} from "react";
import {Stack, Typography} from "@mui/material";
import {isNumber, round} from "lodash";
import {
    alpha,
    experimentalStyled as styled,
    useTheme
} from "@mui/material/styles";
import {Icon} from "@iconify/react";
import minusFill from "@iconify/icons-eva/minus-fill";
import trendingDownFill from "@iconify/icons-eva/trending-down-fill";
import trendingUpFill from "@iconify/icons-eva/trending-up-fill";

const ChangeTrend = ({change = 0, description}) => {
    return (
        <Stack
            direction="row"
            alignItems="center"
            sx={{minWidth: 0}}
            spacing={1}>
            <TrendIcon change={change} />

            <Typography variant="subtitle2" sx={{whiteSpace: "nowrap"}}>
                {isNumber(change) ? `${round(change, 2)}%` : "--"}
            </Typography>

            {description && (
                <Typography variant="subtitle2" color="text.secondary" noWrap>
                    {description}
                </Typography>
            )}
        </Stack>
    );
};

const TrendIcon = ({change = 0}) => {
    const theme = useTheme();

    const color = useMemo(() => {
        switch (true) {
            case change < 0:
                return theme.palette.error.main;
            case change > 0:
                return theme.palette.success.main;
            default:
                return theme.palette.grey[600];
        }
    }, [change, theme]);

    const icon = useMemo(() => {
        switch (true) {
            case change > 0:
                return trendingUpFill;
            case change < 0:
                return trendingDownFill;
            default:
                return minusFill;
        }
    }, [change]);

    return (
        <TrendContainer sx={{color, bgcolor: alpha(color, 0.16)}}>
            <Icon width={16} icon={icon} height={16} />
        </TrendContainer>
    );
};

const TrendContainer = styled("div")(() => ({
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    borderRadius: "50%",
    width: 24,
    height: 24
}));

export default ChangeTrend;
