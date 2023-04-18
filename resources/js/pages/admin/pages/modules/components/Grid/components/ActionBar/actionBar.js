import React from "react";
import {defineMessages, useIntl} from "react-intl";
import SearchTable from "components/SearchTable";
import ActionToolbar from "components/ActionToolbar";

const messages = defineMessages({
    search: {defaultMessage: "Search grid..."}
});

const ActionBar = () => {
    const intl = useIntl();

    return (
        <ActionToolbar>
            <SearchTable
                placeholder={intl.formatMessage(messages.search)}
                field="name"
            />
        </ActionToolbar>
    );
};

export default ActionBar;
