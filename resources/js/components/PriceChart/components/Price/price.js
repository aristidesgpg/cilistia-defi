import React, {useCallback, useEffect, useState} from "react";
import {FormattedMessage} from "react-intl";
import {Stack, Typography} from "@mui/material";
import {errorHandler, route, useRequest} from "services/Http";
import Spin from "components/Spin";
import ChangeTrend from "components/ChangeTrend";

const Price = ({selectedWallet}) => {
    const [market, setMarket] = useState({});
    const [request, loading] = useRequest();

    const fetchPrice = useCallback(() => {
        if (selectedWallet.isNotEmpty()) {
            request
                .get(route("wallet.price", {wallet: selectedWallet.id}))
                .then((data) => setMarket(data))
                .catch(errorHandler());
        }
    }, [request, selectedWallet]);

    useEffect(() => {
        fetchPrice();
    }, [fetchPrice]);

    return (
        <Spin spinning={loading}>
            <Stack>
                <Typography variant="subtitle2">
                    <FormattedMessage defaultMessage="Current Price" />
                </Typography>

                <Typography variant="h4">
                    {market.formatted_price ?? (
                        <FormattedMessage defaultMessage="----" />
                    )}
                </Typography>

                {market.change && <ChangeTrend change={market.change} />}
            </Stack>
        </Spin>
    );
};

export default Price;
