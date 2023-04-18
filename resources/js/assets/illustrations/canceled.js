import React from "react";
import {useTheme} from "@mui/material/styles";
import {Box} from "@mui/material";

const Canceled = ({...other}) => {
    const theme = useTheme();

    const PRIMARY = theme.palette.error.dark;
    const BACKGROUND = theme.palette.background.neutral;

    return (
        <Box {...other}>
            <svg
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
                width="100%"
                height="100%">
                <circle cx={12} cy={12} r={10} style={{fill: BACKGROUND}} />
                <path
                    d="m13.41 12 2.3-2.29a1 1 0 0 0-1.42-1.42L12 10.59l-2.29-2.3a1 1 0 0 0-1.42 1.42l2.3 2.29-2.3 2.29a1 1 0 0 0 0 1.42 1 1 0 0 0 1.42 0l2.29-2.3 2.29 2.3a1 1 0 0 0 1.42 0 1 1 0 0 0 0-1.42Z"
                    style={{fill: PRIMARY}}
                />
            </svg>
        </Box>
    );
};

export default Canceled;
