import React from "react";
import {
    Box,
    Card,
    CardContent,
    CardHeader,
    Chip,
    Stack,
    Typography
} from "@mui/material";
import {FormattedMessage} from "react-intl";
import useCommercePayment from "hooks/useCommercePayment";
import Scrollbar from "components/Scrollbar";
import Divider from "@mui/material/Divider";
import Affix from "@uiw/react-affix";
import {useTheme} from "@mui/material/styles";

const Sidebar = () => {
    const theme = useTheme();
    const {payment} = useCommercePayment();

    return (
        <Affix offsetTop={parseInt(theme.spacing(3))}>
            <Card>
                <CardHeader
                    title={
                        <FormattedMessage defaultMessage="Payment Details" />
                    }
                />

                <CardContent>
                    <Scrollbar sx={{maxHeight: 360}}>
                        <Typography variant="body2">
                            {payment.description}
                        </Typography>
                    </Scrollbar>

                    <Box sx={{mx: -0.5, my: 2}}>
                        {payment.wallets.map((wallet) => (
                            <Chip
                                key={wallet.id}
                                label={wallet.coin.name}
                                sx={{m: 0.5}}
                            />
                        ))}
                    </Box>
                </CardContent>

                <Divider sx={{borderStyle: "dashed"}} />

                <CardContent>
                    <Stack
                        direction="row"
                        justifyContent="space-between"
                        sx={{width: 1, minWidth: 0}}
                        spacing={2}>
                        <Typography variant="subtitle1" noWrap>
                            <FormattedMessage defaultMessage="Amount" />
                        </Typography>

                        <Typography variant="subtitle1" flexShrink={0}>
                            {payment.formatted_amount}
                        </Typography>
                    </Stack>
                </CardContent>
            </Card>
        </Affix>
    );
};

export default Sidebar;
