import React, {forwardRef} from "react";
import classNames from "classnames";
import {experimentalStyled as styled, useTheme} from "@mui/material/styles";
import {Box} from "@mui/material";
import cssStyle from "utils/cssStyle";

const GridItem = forwardRef((props, ref) => {
    const {
        component: GridComponent,
        editable,
        className,
        children,
        ...otherProps
    } = props;

    const theme = useTheme();
    const dark = theme.palette.mode === "dark";

    return (
        <ItemContainer
            ref={ref}
            editable={editable}
            className={classNames(className, {dark})}
            {...otherProps}>
            <GridComponent />
            {children}
        </ItemContainer>
    );
});

const ItemContainer = styled(Box, {
    shouldForwardProp: (prop) => !["editable"].includes(prop)
})(({theme, editable}) => ({
    ...(editable && {
        position: "relative",
        borderRadius: theme.shape.borderRadius * 2,
        cursor: "move",
        overflow: "hidden",
        "&:before": {
            ...cssStyle(theme).bgBlur({opacity: 0.1, blurRate: 1}),
            content: "''",
            zIndex: 3,
            position: "absolute",
            width: "100%",
            height: "100%"
        }
    })
}));

export default GridItem;
