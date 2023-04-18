import React, {forwardRef} from "react";
import {alpha, styled} from "@mui/material/styles";
import {CircularProgress} from "@mui/material";
import cssStyle from "utils/cssStyle";

const SpinContainer = styled("div", {
    shouldForwardProp: (prop) => !["spinning", "size", "compact"].includes(prop)
})(({theme, spinning, size, compact}) => ({
    position: "relative",
    transition: "opacity 0.3s",
    clear: "both",
    "&  > .spinner": {
        position: "absolute",
        left: compact ? "50%" : 0,
        right: compact ? "auto" : 0,
        top: compact ? "50%" : 0,
        height: compact ? size * 1.5 : "100%",
        width: compact ? size * 1.5 : "100%",
        minHeight: compact ? 0 : size * 2.5,
        display: spinning ? "flex" : "none",
        transition: "all 0.3s",
        alignItems: "center",
        justifyContent: "center",
        borderRadius: compact ? theme.shape.borderRadius : 0,
        background: alpha(theme.palette.background.paper, 0.48),
        ...cssStyle(theme).bgBlur({opacity: 0.1, blurRate: 1}),
        transform: compact ? "translate(-50%, -50%)" : "none",
        "& .MuiCircularProgress-root": {zIndex: 9},
        padding: theme.spacing(1)
    }
}));

const Spin = forwardRef((props, ref) => {
    const {
        spinning,
        children,
        sx,
        size = 40,
        thickness = 3.6,
        compact = false
    } = props;

    return (
        <SpinContainer
            ref={ref}
            size={size}
            spinning={spinning}
            compact={compact}
            sx={sx}>
            {children}

            <div className="spinner">
                <CircularProgress size={size} thickness={thickness} />
            </div>
        </SpinContainer>
    );
});

export default Spin;
