import React, {useMemo, useState} from "react";
import CoinSelect from "./components/CoinSelect";
import Price from "./components/Price";
import Chart from "./components/Chart";
import RangeSelect from "./components/RangeSelect";
import ResponsiveCard from "../ResponsiveWidgets/responsiveCard";
import {experimentalStyled as styled} from "@mui/material/styles";
import {useWallets} from "hooks/global";
import {find, first} from "lodash";
import Wallet from "models/Wallet";

const PriceChart = () => {
    const {wallets} = useWallets();
    const [selected, setSelected] = useState();
    const [range, setRange] = useState("hour");

    const collection = useMemo(() => {
        return wallets.filter((o) => o.price_change !== 0);
    }, [wallets]);

    const selectedWallet = useMemo(() => {
        if (selected) {
            return Wallet.use(find(collection, {id: selected}));
        } else {
            return Wallet.use(first(collection));
        }
    }, [selected, collection]);

    return (
        <StyledResponsiveCard>
            <BaseStyle>
                <ContainerData>
                    <Price selectedWallet={selectedWallet} />
                    <CoinSelect
                        setSelected={setSelected}
                        selectedWallet={selectedWallet}
                        collection={collection}
                    />
                </ContainerData>

                <ContainerChart>
                    <Chart selectedWallet={selectedWallet} range={range} />
                    <RangeSelect setRange={setRange} range={range} />
                </ContainerChart>
            </BaseStyle>
        </StyledResponsiveCard>
    );
};

const StyledResponsiveCard = styled(ResponsiveCard)(() => ({
    overflow: "hidden",
    padding: 0
}));

const BaseStyle = styled("div")(() => ({
    display: "flex",
    flexGrow: 1,
    justifyContent: "space-between",
    flexDirection: "column"
}));

const ContainerData = styled("div")(() => ({
    padding: "24px",
    display: "flex",
    justifyContent: "space-between",
    minHeight: 130
}));

const ContainerChart = styled("div")(() => ({
    flexGrow: 1,
    position: "relative",
    minHeight: 0
}));

PriceChart.dimensions = {
    lg: {w: 6, h: 3, minW: 6, minH: 3},
    md: {w: 6, h: 3, minW: 6, minH: 3},
    sm: {w: 2, h: 3, minW: 2, minH: 3},
    xs: {w: 1, h: 3, minW: 1, minH: 3}
};

export default PriceChart;
