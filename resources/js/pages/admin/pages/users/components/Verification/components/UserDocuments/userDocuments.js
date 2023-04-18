import React, {useMemo} from "react";
import {defineMessages, useIntl} from "react-intl";
import {route} from "services/Http";
import AsyncTable from "components/AsyncTable";
import TrapScrollBox from "components/TrapScrollBox";
import {Card, Stack, Typography} from "@mui/material";
import ActionBar from "../ActionBar";
import DocumentView from "./components/DocumentView";
import UserCell from "components/TableCells/UserCell";
import {formatDateFromNow} from "utils/formatter";
import StatusCell from "components/TableCells/StatusCell";

const messages = defineMessages({
    status: {defaultMessage: "Status"},
    user: {defaultMessage: "User"},
    requirement: {defaultMessage: "Type"},
    submitted: {defaultMessage: "Submitted"},
    pending: {defaultMessage: "Pending"},
    approved: {defaultMessage: "Approved"},
    action: {defaultMessage: "Action"},
    rejected: {defaultMessage: "Rejected"}
});

const UserDocuments = () => {
    const intl = useIntl();

    const columns = useMemo(
        () => [
            {
                field: "user",
                minWidth: 200,
                flex: 1,
                headerName: intl.formatMessage(messages.user),
                renderCell: ({value: user}) => <UserCell user={user} />
            },
            {
                field: "requirement",
                minWidth: 200,
                flex: 1,
                headerName: intl.formatMessage(messages.requirement),
                renderCell: ({value}) => value.name
            },
            {
                field: "status",
                headerName: intl.formatMessage(messages.status),
                minWidth: 140,
                flex: 0.5,
                type: "singleSelect",
                valueOptions: ["pending", "approved", "rejected"],
                filterable: true,
                renderCell: ({value: status}) => <StatusCell status={status} />
            },
            {
                field: "created_at",
                headerName: intl.formatMessage(messages.submitted),
                minWidth: 150,
                flex: 0.5,
                renderCell: ({row}) => (
                    <Typography variant="body2" color="text.secondary" noWrap>
                        {formatDateFromNow(row.created_at)}
                    </Typography>
                )
            },
            {
                field: "action",
                minWidth: 100,
                flex: 0.5,
                headerName: intl.formatMessage(messages.action),
                align: "right",
                headerAlign: "right",
                renderCell: ({row: document}) => {
                    return (
                        <Stack direction="row" spacing={1}>
                            <DocumentView document={document} />
                        </Stack>
                    );
                }
            }
        ],
        [intl]
    );

    const url = route("admin.user-verification.document-paginate");

    return (
        <Card>
            <TrapScrollBox>
                <AsyncTable
                    columns={columns}
                    components={{Toolbar: ActionBar}}
                    url={url}
                />
            </TrapScrollBox>
        </Card>
    );
};

export default UserDocuments;
