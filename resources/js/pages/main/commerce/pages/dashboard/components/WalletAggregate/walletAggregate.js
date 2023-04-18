import React, {useCallback, useEffect, useState} from "react";
import {Card, CardHeader, Stack, Typography} from "@mui/material";
import Scrollbar from "components/Scrollbar";
import {useWallets} from "hooks/global";
import LoadingFallback from "components/LoadingFallback";
import {FormattedMessage} from "react-intl";
import Wallet from "models/Wallet";
import IconBuilder from "components/IconBuilder";
import {formatNumber} from "utils/formatter";
import Divider from "@mui/material/Divider";
import {errorHandler, route, useRequest} from "services/Http";

const WalletAggregate = () => {
    const [request, loading] = useRequest();
    const [aggregates, setAggregates] = useState([]);

    const fetchAggregates = useCallback(() => {
        request
            .get(route("commerce-transaction.get-wallet-aggregate"))
            .then((data) => setAggregates(data))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        fetchAggregates();
    }, [fetchAggregates]);

    return (
        <Card>
            <CardHeader
                title={<FormattedMessage defaultMessage="Total Received" />}
            />

            <Scrollbar sx={{maxHeight: 500}}>
                <LoadingFallback
                    content={aggregates}
                    fallbackIconSize={100}
                    loading={loading}>
                    {(aggregates) => (
                        <Stack divider={<Divider />}>
                            {aggregates.map((aggregate) => (
                                <AggregateItem
                                    key={aggregate.coin}
                                    aggregate={aggregate}
                                />
                            ))}
                        </Stack>
                    )}
                </LoadingFallback>
            </Scrollbar>
        </Card>
    );
};

const AggregateItem = ({aggregate}) => {
    const {wallets} = useWallets();

    const record = wallets.find((o) => {
        return o.coin.identifier === aggregate.coin;
    });

    if (!record) {
        return null;
    }

    const wallet = Wallet.use(record);
    const icon = wallet.coin.svgIcon();

    return (
        <Stack
            direction="row"
            alignItems="center"
            sx={{px: 3, py: 2}}
            spacing={2}>
            <IconBuilder sx={{fontSize: "30px"}} icon={icon} />

            <Stack sx={{flexGrow: 1, minWidth: 50}}>
                <Typography variant="subtitle2" noWrap>
                    {wallet.coin.name}
                </Typography>

                <Typography variant="caption" color="text.secondary" noWrap>
                    <FormattedMessage
                        defaultMessage="{total} transactions"
                        values={{total: formatNumber(aggregate.total)}}
                    />
                </Typography>
            </Stack>

            <Stack sx={{alignItems: "end", minWidth: 50}}>
                <Typography variant="body2" noWrap>
                    {aggregate.total_value}
                </Typography>

                <Typography variant="caption" color="text.secondary" noWrap>
                    {aggregate.formatted_total_price}
                </Typography>
            </Stack>
        </Stack>
    );
};

export default WalletAggregate;
