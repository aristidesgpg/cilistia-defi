import {defineMessages} from "react-intl";
import {forEach} from "lodash";
export {getValidationMessages} from "./validationMessage";

const messages = defineMessages({
    invalidPhone: {defaultMessage: "{field} is an invalid phone number"},
    passwordUnmatched: {defaultMessage: "password does not match."}
});

export function normalizeDates(values, ...params) {
    forEach(params, (key) => {
        if (values[key]?.isValid()) {
            values[key] = values[key].utc().format();
        } else {
            values[key] = undefined;
        }
    });
}

export function passwordConfirmation(intl, field = "password") {
    return (form) => ({
        validator(rule, value) {
            if (value && form.getFieldValue(field) !== value) {
                const message = intl.formatMessage(messages.passwordUnmatched);
                return Promise.reject(new Error(message));
            }
            return Promise.resolve();
        }
    });
}
