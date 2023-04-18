import React from "react";
import {Chip} from "@mui/material";
import {FormattedMessage} from "react-intl";

const TypeCell = ({type}) => {
    switch (type) {
        case "multiple":
            return (
                <Chip
                    variant="outlined"
                    size="small"
                    label={<FormattedMessage defaultMessage="Multiple" />}
                />
            );
        case "single":
            return (
                <Chip
                    variant="outlined"
                    size="small"
                    label={<FormattedMessage defaultMessage="Single" />}
                />
            );
        default:
            return null;
    }
};

export default TypeCell;
