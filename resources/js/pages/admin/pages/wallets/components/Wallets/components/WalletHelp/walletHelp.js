import React, {Fragment, useCallback, useEffect, useState} from "react";
import {Alert, IconButton, Paper, Stack, Typography} from "@mui/material";
import HelpIcon from "@mui/icons-material/Help";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {useModal} from "utils/modal";
import ModalContent from "components/ModalContent";
import {errorHandler, route, useRequest} from "services/Http";
import LoadingFallback from "components/LoadingFallback";
import IconBuilder from "components/IconBuilder";
import Copyable from "components/Copyable";

const messages = defineMessages({
    instructions: {defaultMessage: "{name} Instructions"}
});

const WalletHelp = ({wallet}) => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const showHelp = useCallback(() => {
        const name = wallet.coin.name;

        modal.confirm({
            title: intl.formatMessage(messages.instructions, {name}),
            content: <HelpCard wallet={wallet} />,
            dialog: {fullWidth: true}
        });
    }, [modal, intl, wallet]);

    if (!wallet.native_asset) return null;

    return (
        <Fragment>
            <IconButton onClick={showHelp}>
                <HelpIcon />
            </IconButton>

            {modalElements}
        </Fragment>
    );
};

const HelpCard = ({wallet}) => {
    const [feeAddress, setFeeAddress] = useState();
    const [request, loading] = useRequest();

    const fetchFeeAddress = useCallback(() => {
        const url = route("admin.wallet.get-fee-address", {
            identifier: wallet.coin.identifier
        });

        request
            .get(url)
            .then((data) => setFeeAddress(data))
            .catch(errorHandler());
    }, [wallet, request]);

    useEffect(() => {
        fetchFeeAddress();
    }, [fetchFeeAddress]);

    return (
        <LoadingFallback
            content={feeAddress}
            fallbackIconSize={200}
            loading={loading}>
            {(feeAddress) => (
                <ModalContent spacing={2} sx={{pb: 2}}>
                    <Typography variant="body2">
                        <FormattedMessage defaultMessage="Coins are native to a blockchain, such as Bitcoin or Ether. Tokens represent an asset on some other blockchain, transactions of token requires the native asset of the blockchain to be used as fee." />
                    </Typography>

                    <Typography variant="body2">
                        <FormattedMessage
                            defaultMessage="{token} is a token on the {asset} blockchain and it requires sufficient {symbol} to be maintained on the following address to ensure transactions and consolidation of token works well."
                            values={{
                                asset: <b>{wallet.native_asset.name}</b>,
                                symbol: <b>{wallet.native_asset.symbol}</b>,
                                token: <b>{wallet.coin.name}</b>
                            }}
                        />
                    </Typography>

                    <Paper variant="outlined">
                        <Stack
                            direction="row"
                            justifyContent="center"
                            alignItems="center"
                            spacing={2}
                            sx={{p: 2}}>
                            <IconBuilder
                                icon={wallet.native_asset.icon}
                                sx={{fontSize: "50px"}}
                            />

                            <Stack sx={{minWidth: 100}}>
                                <Typography variant="subtitle2" noWrap>
                                    <FormattedMessage
                                        defaultMessage="{name} ({symbol})"
                                        values={{
                                            symbol: wallet.native_asset.symbol,
                                            name: wallet.native_asset.name
                                        }}
                                    />
                                </Typography>

                                <Copyable ellipsis text={feeAddress.address}>
                                    {feeAddress.address}
                                </Copyable>
                            </Stack>
                        </Stack>
                    </Paper>

                    <Alert severity="warning" icon={false}>
                        <FormattedMessage defaultMessage="Don't make the mistake of sending the wrong asset into the provided address. You should also ensure that just the right amount of asset needed for operation is deposited, because any asset sent into it is irrecoverable." />
                    </Alert>
                </ModalContent>
            )}
        </LoadingFallback>
    );
};

export default WalletHelp;
