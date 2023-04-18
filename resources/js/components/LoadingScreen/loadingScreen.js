import React from "react";
import {experimentalStyled as styled} from "@mui/material/styles";
import {CircularProgress} from "@mui/material";

const BaseStyle = styled("div")(({size}) => ({
    display: "flex",
    minHeight: size * 2.5,
    height: "100%",
    justifyContent: "center",
    alignItems: "center"
}));

export default function LoadingScreen({size = 40, thickness = 3.6, ...other}) {
    return (
        <BaseStyle size={size} {...other}>
            <CircularProgress size={size} thickness={thickness} />
        </BaseStyle>
    );
}
