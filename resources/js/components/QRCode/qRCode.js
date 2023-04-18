import React, {forwardRef} from "react";
import BaseQRCode from "qrcode.react";
import {useTheme} from "@mui/material/styles";

const QRCode = forwardRef((props, ref) => {
    const theme = useTheme();

    return (
        <BaseQRCode
            ref={ref}
            bgColor={theme.palette.background.paper}
            fgColor={theme.palette.text.primary}
            {...props}
        />
    );
});

export default QRCode;
