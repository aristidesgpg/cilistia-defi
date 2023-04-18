import React from "react";
import {styled} from "@mui/material/styles";
import {Card, Stack, Typography} from "@mui/material";
import {Icon} from "@iconify/react";
import creditCardFill from "@iconify/icons-eva/credit-card-fill";
import {FormattedMessage} from "react-intl";
import Chart from "./components/Chart";
import {usePaymentAccount} from "hooks/accounts";
import Spin from "components/Spin";

const Balance = () => {
    const {account, loading} = usePaymentAccount();

    const available = account.formatted_available;
    const pendingReceive = account.formatted_total_pending_receive;

    return (
        <Spin spinning={loading}>
            <StyledCard>
                <IconWrapperStyle>
                    <Icon icon={creditCardFill} width={24} height={24} />
                </IconWrapperStyle>

                <Stack spacing={1} sx={{p: 3}}>
                    <Typography sx={{typography: "subtitle2"}}>
                        <FormattedMessage defaultMessage="Available" />
                    </Typography>

                    <Typography sx={{typography: "h3"}}>
                        {available ?? (
                            <FormattedMessage defaultMessage="----" />
                        )}
                    </Typography>

                    {pendingReceive && (
                        <Typography variant="body2">
                            <FormattedMessage
                                defaultMessage="{value} pending credit"
                                values={{value: pendingReceive}}
                            />
                        </Typography>
                    )}
                </Stack>

                <Chart />
            </StyledCard>
        </Spin>
    );
};

const StyledCard = styled(Card)(({theme}) => ({
    position: "relative",
    width: "100%",
    boxShadow: "none",
    color: theme.palette.primary.darker,
    backgroundColor: theme.palette.primary.lighter,
    whiteSpace: "nowrap"
}));

const IconWrapperStyle = styled("div")(({theme}) => ({
    position: "absolute",
    right: theme.spacing(3),
    top: theme.spacing(3),
    width: 48,
    height: 48,
    color: theme.palette.primary.lighter,
    backgroundColor: theme.palette.primary.dark,
    display: "flex",
    borderRadius: "50%",
    justifyContent: "center",
    alignItems: "center"
}));

export default Balance;
