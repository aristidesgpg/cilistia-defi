import React, {useMemo} from "react";
import Label from "components/Label";
import {FormattedMessage} from "react-intl";

const StatusCell = ({status}) => {
    return useMemo(() => {
        switch (status) {
            case "pending":
                return (
                    <Label variant="ghost" color="info">
                        <FormattedMessage defaultMessage="Pending" />
                    </Label>
                );
            case "completed":
                return (
                    <Label variant="ghost" color="success">
                        <FormattedMessage defaultMessage="Completed" />
                    </Label>
                );
            case "canceled":
                return (
                    <Label variant="ghost" color="error">
                        <FormattedMessage defaultMessage="Canceled" />
                    </Label>
                );
            default:
                return null;
        }
    }, [status]);
};

export default StatusCell;
