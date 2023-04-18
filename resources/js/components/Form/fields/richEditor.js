import React, {useContext} from "react";
import BaseRichEditor from "components/RichEditor";
import {FormInputContext} from "components/Form/contexts";
import {defaultTo, isEmpty} from "lodash";
import {useTheme} from "@mui/material/styles";
import {FormControl, FormHelperText} from "@mui/material";

const RichEditor = ({value, helperText, disabled = false, ...baseProps}) => {
    const theme = useTheme();
    const {
        isRequired,
        validateStatus,
        errors = []
    } = useContext(FormInputContext);

    switch (validateStatus) {
        case "success":
            baseProps.sx = {
                "&:focus-within": {borderColor: theme.palette.primary.main},
                ...baseProps.sx
            };
            break;
        case "error":
            baseProps.sx = {
                borderColor: theme.palette.error.main,
                ...baseProps.sx
            };
            break;
    }

    helperText = isEmpty(errors) ? helperText : errors.join(", ");

    return (
        <FormControl
            component="div"
            disabled={disabled}
            error={validateStatus === "error"}
            required={isRequired}
            fullWidth>
            <BaseRichEditor
                {...baseProps}
                value={defaultTo(value, "")}
                readOnly={disabled}
            />

            {helperText && <FormHelperText>{helperText}</FormHelperText>}
        </FormControl>
    );
};

export default RichEditor;
