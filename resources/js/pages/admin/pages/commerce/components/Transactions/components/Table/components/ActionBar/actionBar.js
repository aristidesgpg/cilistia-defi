import React from "react";
import {defineMessages, useIntl} from "react-intl";
import ActionToolbar from "components/ActionToolbar";
import SearchTable from "components/SearchTable";

const messages = defineMessages({
    search: {defaultMessage: "Search user..."}
});

const ActionBar = () => {
    const intl = useIntl();

    return (
        <ActionToolbar>
            <SearchTable
                field="searchUser"
                placeholder={intl.formatMessage(messages.search)}
                withParams={true}
            />
        </ActionToolbar>
    );
};

export default ActionBar;
