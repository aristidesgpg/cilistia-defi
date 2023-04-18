import React from "react";
import {FormattedMessage} from "react-intl";
import {experimentalStyled as styled} from "@mui/material/styles";
import Result from "components/Result";
import {Button} from "@mui/material";
import {SuccessIllustration} from "assets/index";

const Completed = ({lastSent, onReset}) => {
    if (!lastSent) return null;

    return (
        <Container>
            <Result
                className="animated fadeIn"
                title={<FormattedMessage defaultMessage="Sent" />}
                description={`${lastSent.value} ${lastSent.coin.symbol}`}
                extra={<ResetButton onReset={onReset} />}
                icon={SuccessIllustration}
                iconSize={170}
            />
        </Container>
    );
};

const ResetButton = ({onReset}) => {
    return (
        <Button onClick={onReset} variant="contained" sx={{borderRadius: '5px'}}>
            <FormattedMessage defaultMessage="Continue" />
        </Button>
    );
};

const Container = styled("div")(({theme}) => ({
    position: "absolute",
    background: theme.palette.background.paper,
    height: "100%",
    width: "100%",
    display: "flex",
    justifyContent: "center",
    flexDirection: "column",
    top: 0,
    right: 0,
    left: 0,
    zIndex: 10
}));

export default Completed;
