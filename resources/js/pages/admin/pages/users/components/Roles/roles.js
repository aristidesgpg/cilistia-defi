import React, {useMemo} from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {route} from "services/Http";
import {Card, Chip, Stack, Typography} from "@mui/material";
import ActionBar from "./components/ActionBar";
import RoleDelete from "./components/RoleDelete";
import RoleEdit from "./components/RoleEdit";
import TrapScrollBox from "components/TrapScrollBox";
import AsyncTable from "components/AsyncTable";
import {lowerCase} from "lodash";

const messages = defineMessages({
    name: {defaultMessage: "Name"},
    permissions: {defaultMessage: "Permissions"},
    rank: {defaultMessage: "Rank"},
    action: {defaultMessage: "Action"},
    users: {defaultMessage: "Users"}
});

const Roles = () => {
    const intl = useIntl();

    const columns = useMemo(
        () => [
            {
                field: "name",
                minWidth: 100,
                flex: 1,
                headerName: intl.formatMessage(messages.name)
            },
            {
                field: "permissions",
                minWidth: 100,
                flex: 1,
                headerName: intl.formatMessage(messages.permissions),
                renderCell: ({value: permissions}) => {
                    if (permissions.length === 0) {
                        return (
                            <Typography variant="body2">
                                <FormattedMessage defaultMessage="No permissions" />
                            </Typography>
                        );
                    }

                    const label = lowerCase(permissions[0].name);
                    const extra = permissions.length - 1;

                    return (
                        <Stack
                            direction="row"
                            sx={{minWidth: 100}}
                            spacing={0.5}>
                            <Chip
                                label={label}
                                variant="outlined"
                                size="small"
                            />

                            {extra >= 1 && (
                                <Chip
                                    variant="filled"
                                    label={`+${extra}`}
                                    size="small"
                                />
                            )}
                        </Stack>
                    );
                }
            },
            {
                field: "rank",
                minWidth: 100,
                flex: 0.5,
                headerName: intl.formatMessage(messages.rank)
            },
            {
                field: "users_count",
                minWidth: 100,
                flex: 0.5,
                headerName: intl.formatMessage(messages.users)
            },
            {
                field: "action",
                minWidth: 100,
                flex: 0.5,
                headerAlign: "right",
                headerName: intl.formatMessage(messages.action),
                align: "right",
                renderCell: ({row: role}) => {
                    return (
                        <Stack direction="row" spacing={1}>
                            <RoleDelete role={role} />
                            <RoleEdit role={role} />
                        </Stack>
                    );
                }
            }
        ],
        [intl]
    );

    const url = route("admin.role.paginate");

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

export default Roles;
