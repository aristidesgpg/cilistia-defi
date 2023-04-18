import React, {forwardRef} from "react";
import {Stack} from "@mui/material";

const ModalContent = forwardRef(({sx, ...otherProps}, ref) => {
    return <Stack ref={ref} sx={{pb: 1, ...sx}} {...otherProps} />;
});

export default ModalContent;
