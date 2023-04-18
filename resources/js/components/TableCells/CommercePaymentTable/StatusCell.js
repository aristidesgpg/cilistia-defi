import React, {useCallback, useContext} from "react";
import {CircularProgress, Switch} from "@mui/material";
import {errorHandler, route, useRequest} from "services/Http";
import TableContext from "contexts/TableContext";
import {notify, parseDate} from "utils/index";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import dayjs from "utils/dayjs";
import Label from "components/Label";

const messages = defineMessages({
    success: {defaultMessage: "Payment was updated."}
});

const StatusCell = ({payment, onChange}) => {
    const intl = useIntl();
    const {reload: reloadTable} = useContext(TableContext);
    const [request, loading] = useRequest();

    const changeStatus = useCallback(
        (status) => {
            const action = !status
                ? "commerce-payment.disable"
                : "commerce-payment.enable";

            request
                .post(route(action, {id: payment.id}))
                .then(() => {
                    notify.success(intl.formatMessage(messages.success));
                    onChange?.();
                    reloadTable();
                })
                .catch(errorHandler());
        },
        [intl, request, payment, reloadTable, onChange]
    );

    if (loading) {
        return <CircularProgress size="1rem" />;
    }

    if (payment.complete) {
        return (
            <Label variant="ghost" color="success">
                <FormattedMessage defaultMessage="Completed" />
            </Label>
        );
    }

    if (payment.expires_at) {
        const expiresAt = parseDate(payment.expires_at);

        return expiresAt.isSameOrAfter(dayjs()) ? (
            <Label variant="ghost" color="info">
                <FormattedMessage defaultMessage="Active" />
            </Label>
        ) : (
            <Label variant="ghost" color="warning">
                <FormattedMessage defaultMessage="Expired" />
            </Label>
        );
    }

    return (
        <Switch
            checked={payment.status}
            onChange={(_, status) => changeStatus(status)}
        />
    );
};

export default StatusCell;
