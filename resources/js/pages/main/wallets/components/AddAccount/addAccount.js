import React, {Fragment, useCallback, useEffect, useState} from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {errorHandler, route, useFormRequest, useRequest} from "services/Http";
import {isEmpty} from "lodash";
import IconBuilder from "components/IconBuilder";
import Wallet from "models/Wallet";
import {fetchWalletAccounts} from "redux/slices/wallet";
import {useDispatch} from "react-redux";
import Form, {AutoComplete} from "components/Form";
import AddIcon from "@mui/icons-material/Add";
import {Box, Button} from "@mui/material";
import {experimentalStyled as styled} from "@mui/material/styles";
import {useModal} from "utils/modal";
import {LoadingButton} from "@mui/lab";
import ModalActions from "components/ModalActions";
import LoadingFallback from "components/LoadingFallback";

const messages = defineMessages({
    title: {defaultMessage: "Add Account"},
    coin: {defaultMessage: "Select Coin"},
    submit: {defaultMessage: "Submit"}
});

const AddAccount = () => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const showModal = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.title),
            content: <ModalContent />
        });
    }, [intl, modal]);

    return (
        <Fragment>
            <Button variant="contained" onClick={showModal} sx={{borderRadius: '5px'}}>
                <AddIcon />
            </Button>

            {modalElements}
        </Fragment>
    );
};

const ModalContent = ({closeModal}) => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const [formRequest, formLoading] = useFormRequest(form);
    const [request, loading] = useRequest();
    const [wallets, setWallets] = useState([]);
    const dispatch = useDispatch();

    const fetchWallets = useCallback(() => {
        request
            .get(route("wallet.unused"))
            .then((data) => setWallets(data))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        fetchWallets();
    }, [fetchWallets]);

    const submitForm = useCallback(
        (values) => {
            const wallet = values.wallet?.id;

            formRequest
                .post(route("wallet.create-account", {wallet}))
                .then(() => {
                    form.resetFields();
                    dispatch(fetchWalletAccounts());
                    closeModal();
                })
                .catch(errorHandler());
        },
        [dispatch, closeModal, formRequest, form]
    );

    return (
        <Form form={form} onFinish={submitForm}>
            <LoadingFallback
                content={wallets}
                fallbackIconSize={130}
                loading={loading}>
                {(wallets) => (
                    <Form.Item
                        name="wallet"
                        label={intl.formatMessage(messages.coin)}
                        rules={[{required: true}]}>
                        <AutoComplete
                            sx={{minWidth: 250}}
                            options={wallets}
                            getOptionLabel={(option) => option.coin.name}
                            isOptionEqualToValue={(option, value) => {
                                return option.id === value.id;
                            }}
                            renderOption={(props, option) => {
                                const wallet = Wallet.use(option);

                                return (
                                    <CoinWrapper {...props} key={wallet.id}>
                                        <IconBuilder
                                            icon={wallet.coin.svgIcon()}
                                            sx={{fontSize: "25px"}}
                                        />

                                        <Box component="span" sx={{ml: 1}}>
                                            {wallet.coin.name}
                                        </Box>
                                    </CoinWrapper>
                                );
                            }}
                        />
                    </Form.Item>
                )}
            </LoadingFallback>

            <ModalActions>
                {!isEmpty(wallets) && (
                    <LoadingButton
                        variant="contained"
                        loading={formLoading}
                        disabled={loading}
                        type="submit">
                        <FormattedMessage defaultMessage="Submit" />
                    </LoadingButton>
                )}
            </ModalActions>
        </Form>
    );
};

const CoinWrapper = styled("li")({
    display: "flex",
    alignItems: "center",
    flexGrow: 1,
    flexBasis: 0
});

export default AddAccount;
