import React, {useContext} from "react";
import {FormInputContext} from "components/Form/contexts";
import {
    FormControl,
    FormControlLabel,
    FormHelperText,
    Radio,
    RadioGroup as BaseRadioGroup
} from "@mui/material";
import {isEmpty, defaultTo} from "lodash";

const RadioGroup = ({options, value, ...baseProps}) => {
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

    return (
        <FormControl {...control}>
            <BaseRadioGroup row value={defaultTo(value, null)} {...baseProps}>
                {options.map((option) => (
                    <FormControlLabel
                        key={option.value}
                        label={option.label}
                        value={option.value}
                        control={<Radio />}
                    />
                ))}
            </BaseRadioGroup>

            {!isEmpty(errors) && (
                <FormHelperText>{errors.join(", ")}</FormHelperText>
            )}
        </FormControl>
    );
};

export default RadioGroup;
