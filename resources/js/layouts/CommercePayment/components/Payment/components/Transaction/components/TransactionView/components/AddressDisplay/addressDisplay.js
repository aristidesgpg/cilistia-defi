import React, {forwardRef, useContext} from "react";
import Wallet from "models/Wallet";
import {Box, Chip, Paper, Stack, Typography} from "@mui/material";
import QRCode from "components/QRCode";
import {experimentalStyled as styled} from "@mui/material/styles";
import Copyable from "components/Copyable";
import {FormattedMessage} from "react-intl";
import CommerceTransactionContext from "contexts/CommerceTransactionContext";

const AddressDisplay = forwardRef((props, ref) => {
    const {transaction} = useContext(CommerceTransactionContext);
    const wallet = Wallet.use(transaction.wallet);
    const nativeAsset = wallet.native_asset;

    return (
        <Stack alignItems="center" spacing={2} ref={ref}>
            <StyledPaper elevation={1}>
                <CodeBox
                    component={QRCode}
                    value={transaction.address}
                    renderAs="svg"
                    imageSettings={{
                        excavate: true,
                        src: wallet.coin.svgIcon(),
                        width: 20,
                        height: 20
                    }}
                />
            </StyledPaper>

            {nativeAsset && (
                <Paper elevation={1}>
                    <Typography variant="body2" sx={{p: 1}}>
                        <FormattedMessage
                            defaultMessage="{name} is a token on the {asset} network. If you send {symbol} over any other network, your tokens will be lost."
                            values={{
                                name: <b>{wallet.coin.name}</b>,
                                symbol: <b>{wallet.coin.symbol}</b>,
                                asset: <b>{nativeAsset.name}</b>
                            }}
                        />
                    </Typography>
                </Paper>
            )}

            <Copyable variant="subtitle2" text={transaction.address} ellipsis>
                <Chip variant="outlined" label={transaction.address} />
            </Copyable>
        </Stack>
    );
});

const StyledPaper = styled(Paper)(({theme}) => ({
    display: "flex",
    width: "80%",
    padding: theme.spacing(1),
    maxWidth: 250
}));

const CodeBox = styled(Box)(({theme}) => ({
    padding: theme.spacing(1),
    borderRadius: "5px",
    width: "100%",
    height: "auto",
    margin: "auto"
}));

export default AddressDisplay;
