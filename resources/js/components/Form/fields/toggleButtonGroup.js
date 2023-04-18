import React, {useContext} from "react";
import {defaultTo, isEmpty} from "lodash";
import {
    FormControl,
    FormHelperText,
    ToggleButtonGroup as BaseToggleButtonGroup
} from "@mui/material";
import {FormInputContext} from "components/Form/contexts";

const ToggleButtonGroup = ({value, onChange, helperText, ...otherProps}) => {
    const {
        isRequired,
        validateStatus,
        errors = []
    } = useContext(FormInputContext);

    const control = {required: isRequired};

    switch (validateStatus) {
        case "error":
            control.error = true;
            break;
        case "success":
            control.color = "primary";
            break;
        default:
            control.color = "info";
    }

    helperText = isEmpty(errors) ? helperText : errors.join(", ");

    return (
        <FormControl {...control}>
            <BaseToggleButtonGroup
                color={control.color}
                onChange={(_, change) => onChange?.(change)}
                value={defaultTo(value, "")}
                {...otherProps}
            />

            {helperText && <FormHelperText>{helperText}</FormHelperText>}
        </FormControl>
    );
};

export default ToggleButtonGroup;
