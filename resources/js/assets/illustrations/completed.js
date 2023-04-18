import React from "react";
import {useTheme} from "@mui/material/styles";
import {Box} from "@mui/material";

const Completed = ({...other}) => {
    const theme = useTheme();

    const PRIMARY = theme.palette.success.dark;
    const BACKGROUND = theme.palette.background.neutral;

    return (
        <Box {...other}>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                width="100%"
                height="100%">
                <path
                    d="M13.41 15.41a2 2 0 0 1-2.83 0l-4-4a2 2 0 0 1 0-2.83 2 2 0 0 1 2.82 0l2.6 2.59 6.63-6.63a10 10 0 1 0 3.15 9.53 9.87 9.87 0 0 0-.73-6.29Z"
                    style={{fill: BACKGROUND}}
                />
                <path
                    d="M12 15a1 1 0 0 1-.71-.29l-4-4a1 1 0 1 1 1.42-1.42l3.29 3.3 8.29-8.3a1 1 0 1 1 1.42 1.42l-9 9A1 1 0 0 1 12 15Z"
                    style={{fill: PRIMARY}}
                />
            </svg>
        </Box>
    );
};

export default Completed;
