import React, {useMemo} from "react";
import router from "router/router";
import {Button} from "@mui/material";
import {Link as RouterLink} from "react-router-dom";
import {FormattedMessage} from "react-intl";
import PropTypes from "prop-types";

const ActionCell = ({offer}) => {
    const link = useMemo(() => {
        return router.generatePath("main.peer.offer", {id: offer.id});
    }, [offer]);

    if (!offer.tradable) {
        return (
            <Button
            sx={{borderRadius: '5px'}}
                size="small"
                variant="outlined"
                component={RouterLink}
                to={link}>
                <FormattedMessage defaultMessage="View" />
            </Button>
        );
    }

    return offer.type === "sell" ? (
        <Button
        sx={{borderRadius: '5px'}}
            variant="contained"
            size="small"
            component={RouterLink}
            color="success"
            to={link}>
            <FormattedMessage defaultMessage="Buy" />
        </Button>
    ) : (
        <Button
        sx={{borderRadius: '5px'}}
            variant="contained"
            size="small"
            component={RouterLink}
            color="error"
            to={link}>
            <FormattedMessage defaultMessage="Sell" />
        </Button>
    );
};

ActionCell.propTypes = {
    offer: PropTypes.object
};

export default ActionCell;
