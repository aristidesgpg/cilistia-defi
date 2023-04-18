import React from "react";
import {isString} from "lodash";
import {Box} from "@mui/material";

const IconBuilder = ({icon: svgIcon, sx: iconStyle, ...otherProps}) => {
    if (!isString(svgIcon)) return null;

    return (
        <Box
            component="span"
            {...otherProps}
            sx={{
                display: "flex",
                alignItems: "center",
                flexShrink: 0,
                ...iconStyle
            }}>
            <Box
                component="img"
                sx={{width: "1em", height: "1em"}}
                src={svgIcon}
            />
        </Box>
    );
};

export default IconBuilder;
