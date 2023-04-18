import React, {Fragment, useCallback, useContext, useMemo} from "react";
import {useModal} from "utils/modal";
import {errorHandler, route, useFormRequest, useRequest} from "services/Http";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {isEmpty} from "lodash";
import Dropdown from "components/Dropdown";
import {IconButton, MenuItem} from "@mui/material";
import MoreVertIcon from "@mui/icons-material/MoreVert";
import {notify} from "utils/index";
import Form, {TextField} from "components/Form";
import {LoadingButton} from "@mui/lab";
import LoadingIcon from "components/LoadingIcon";
import AntennaIcon from "@mui/icons-material/SettingsInputAntenna";
import RssFeedIcon from "@mui/icons-material/RssFeed";
import CompressIcon from "@mui/icons-material/Compress";
import TableContext from "contexts/TableContext";
import ModalActions from "components/ModalActions";

const messages = defineMessages({
    value: {defaultMessage: "Value"},
    key: {defaultMessage: "Key"},
    webhookReset: {defaultMessage: "Wallet webhook was reset."},
    relayed: {defaultMessage: "Wallet transaction was relayed."},
    consolidate: {defaultMessage: "Consolidate Address"},
    properties: {defaultMessage: "Properties"},
    consolidated: {defaultMessage: "Address was consolidated"},
    relay: {defaultMessage: "Relay Transaction"},
    hash: {defaultMessage: "Hash"},
    address: {defaultMessage: "Address"}
});

const WalletMenu = ({wallet}) => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();
    const {reload: reloadTable} = useContext(TableContext);
    const [request, loading] = useRequest();

    const resetWebhook = useCallback(() => {
        const url = route("admin.wallet.reset-webhook", {
            identifier: wallet.coin.identifier
        });

        request
            .post(url)
            .then(() => {
                notify.success(intl.formatMessage(messages.webhookReset));
                reloadTable();
            })
            .catch(errorHandler());
    }, [request, wallet, intl, reloadTable]);

    const relayTransaction = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.relay),
            content: <RelayTransaction wallet={wallet} />,
            dialog: {fullWidth: true}
        });
    }, [modal, wallet, intl]);

    const consolidate = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.consolidate),
            content: <Consolidate wallet={wallet} />,
            dialog: {fullWidth: true}
        });
    }, [modal, wallet, intl]);

    const menuItems = useMemo(() => {
        const items = [];

        if (wallet.consolidates) {
            items.push(
                <MenuItem key={2} onClick={consolidate}>
                    <CompressIcon sx={{mr: 2}} />
                    <FormattedMessage defaultMessage="Consolidate" />
                </MenuItem>
            );
        }

        items.push(
            <MenuItem key={1} onClick={relayTransaction}>
                <RssFeedIcon sx={{mr: 2}} />
                <FormattedMessage defaultMessage="Relay Transaction" />
            </MenuItem>
        );

        items.push(
            <MenuItem key={0} onClick={resetWebhook}>
                <AntennaIcon sx={{mr: 2}} />
                <FormattedMessage defaultMessage="Reset Webhook" />
            </MenuItem>
        );

        return items;
    }, [wallet, resetWebhook, relayTransaction, consolidate]);

    if (isEmpty(menuItems)) {
        return null;
    }

    return (
        <Fragment>
            <Dropdown menuItems={menuItems} component={IconButton}>
                <LoadingIcon component={MoreVertIcon} loading={loading} />
            </Dropdown>

            {modalElements}
        </Fragment>
    );
};

const RelayTransaction = ({closeModal, wallet}) => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const [formRequest, formLoading] = useFormRequest(form);
    const {reload: reloadTable} = useContext(TableContext);

    const submitForm = useCallback(
        (values) => {
            const url = route("admin.wallet.relay-transaction", {
                identifier: wallet.coin.identifier
            });

            formRequest
                .post(url, values)
                .then(() => {
                    notify.success(intl.formatMessage(messages.relayed));
                    closeModal();
                    reloadTable();
                })
                .catch(errorHandler());
        },
        [closeModal, formRequest, intl, wallet, reloadTable]
    );

    return (
        <Form form={form} onFinish={submitForm}>
            <Form.Item name="hash" label={intl.formatMessage(messages.hash)}>
                <TextField fullWidth />
            </Form.Item>

            <ModalActions>
                <LoadingButton
                    variant="contained"
                    type="submit"
                    loading={formLoading}>
                    <FormattedMessage defaultMessage="Submit" />
                </LoadingButton>
            </ModalActions>
        </Form>
    );
};

const Consolidate = ({closeModal, wallet}) => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const [formRequest, formLoading] = useFormRequest(form);
    const {reload: reloadTable} = useContext(TableContext);

    const submitForm = useCallback(
        (values) => {
            const url = route("admin.wallet.consolidate", {
                identifier: wallet.coin.identifier
            });

            formRequest
                .post(url, values)
                .then(() => {
                    notify.success(intl.formatMessage(messages.consolidated));
                    closeModal();
                    reloadTable();
                })
                .catch(errorHandler());
        },
        [closeModal, formRequest, intl, wallet, reloadTable]
    );

    return (
        <Form form={form} onFinish={submitForm}>
            <Form.Item
                name="address"
                label={intl.formatMessage(messages.address)}>
                <TextField fullWidth />
            </Form.Item>

            <ModalActions>
                <LoadingButton
                    variant="contained"
                    type="submit"
                    loading={formLoading}>
                    <FormattedMessage defaultMessage="Submit" />
                </LoadingButton>
            </ModalActions>
        </Form>
    );
};

export default WalletMenu;
