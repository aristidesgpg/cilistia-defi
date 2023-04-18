import React, {useCallback, useEffect, useState} from "react";
import {FormattedMessage} from "react-intl";
import QRCode from "components/QRCode";
import {errorHandler, route, useRequest} from "services/Http";
import {useActiveWalletAccount} from "hooks/accounts";
import GlobalAccountSelect from "components/GlobalAccountSelect";
import {experimentalStyled as styled} from "@mui/material/styles";
import {Box, Button, Chip, Paper, Stack, Typography} from "@mui/material";
import Copyable from "components/Copyable";
import LoadingFallback from "components/LoadingFallback";
import WalletAccount from "models/WalletAccount";

const Receive = () => {
    const {account} = useActiveWalletAccount();
    const [address, setAddress] = useState();
    const [request, loading] = useRequest();

    useEffect(() => {
        if (!account.isEmpty()) setAddress();
    }, [account]);

    useEffect(() => {
        if (!account.isEmpty()) {
            const url = route("wallet-account.latest-address", {
                id: account.id
            });

            request
                .get(url)
                .then((data) => setAddress(data))
                .catch(errorHandler());
        }
    }, [request, account]);

    const generateAddress = useCallback(() => {
        if (!account.isEmpty()) {
            const url = route("wallet-account.generate-address", {
                id: account.id
            });

            request
                .post(url)
                .then((data) => setAddress(data))
                .catch(errorHandler());
        }
    }, [request, account]);

    return (
        <LoadingFallback
            content={address}
            fallbackIconSize={130}
            loading={loading}>
            {(address) => (
                <AddressDisplay
                    account={account}
                    generateAddress={generateAddress}
                    address={address}
                />
            )}
        </LoadingFallback>
    );
};

const AddressDisplay = ({account, address, generateAddress}) => {
    account = WalletAccount.use(account);
    const nativeAsset = account.wallet.native_asset;
    const coinIcon = account.wallet.coin.svgIcon();

    return (
        <Stack alignItems="center" spacing={2} sx={{p: 3}}>
            <CodeBox
                component={QRCode}
                imageSettings={{
                    excavate: true,
                    src: coinIcon,
                    width: 20,
                    height: 20
                }}
                value={address.address}
                renderAs="svg"
            />

            <GlobalAccountSelect sx={{maxWidth: 256}} />

            <Copyable variant="subtitle2" text={address.address} ellipsis>
                <Chip label={address.address} />
            </Copyable>

            {nativeAsset && (
                <Paper variant="outlined">
                    <Typography variant="body2" sx={{p: 2}}>
                        <FormattedMessage
                            defaultMessage="{name} is a token on the {asset} network. Don't send {symbol} over any other network to this address or your funds will be lost."
                            values={{
                                name: <b>{account.wallet.coin.name}</b>,
                                symbol: <b>{account.wallet.coin.symbol}</b>,
                                asset: <b>{nativeAsset.name}</b>
                            }}
                        />
                    </Typography>
                </Paper>
            )}

            <Button variant="contained" onClick={generateAddress} sx={{borderRadius: '5px'}}>
                <FormattedMessage defaultMessage="Generate New Address" />
            </Button>
        </Stack>
    );
};

const CodeBox = styled(Box)(({theme}) => ({
    maxWidth: 256,
    padding: theme.spacing(1),
    border: `1px dashed ${theme.palette.grey[500_32]}`,
    borderRadius: "5px",
    width: "80%",
    height: "auto",
    margin: "auto"
}));

export default Receive;
