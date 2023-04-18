import React from "react";
import SearchTable from "components/SearchTable";
import {defineMessages, useIntl} from "react-intl";
import ActionToolbar from "components/ActionToolbar";

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
