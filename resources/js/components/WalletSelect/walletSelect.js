import React, {useCallback, useEffect, useMemo} from "react";
import Wallet from "models/Wallet";
import {Box} from "@mui/material";
import IconBuilder from "components/IconBuilder";
import {AutoComplete} from "components/Form";
import {useWallets} from "hooks/global";
import {experimentalStyled as styled} from "@mui/material/styles";
import useCallbackRef from "hooks/useCallbackRef";
import {castArray, map} from "lodash";

const WalletSelect = ({
    onSelect,
    value,
    onChange,
    getValue = (o) => o.id,
    multiple = false,
    ...otherProps
}) => {
    const {wallets, loading} = useWallets();
    const getValueRef = useCallbackRef(getValue);

    const selected = useMemo(() => {
        const getValue = getValueRef.current;

        if (!multiple) {
            return wallets.find((o) => getValue(o) === value);
        }

        return wallets.filter((o) => {
            return castArray(value).includes(getValue(o));
        });
    }, [wallets, value, multiple, getValueRef]);

    const onSelectRef = useCallbackRef(onSelect);

    useEffect(() => {
        onSelectRef.current?.(selected);
    }, [onSelectRef, selected]);

    const handleChange = useCallback(
        (selected) => {
            const getValue = getValueRef.current;

            const value = multiple
                ? map(selected, getValue)
                : getValue(selected);

            return onChange?.(value);
        },
        [onChange, multiple, getValueRef]
    );

    return (
        <AutoComplete
            {...otherProps}
            multiple={multiple}
            value={selected}
            onChange={handleChange}
            options={wallets}
            getOptionLabel={(option) => option.coin.name}
            isOptionEqualToValue={(option, value) => option.id === value.id}
            loading={loading}
            renderOption={(props, option) => {
                const wallet = Wallet.use(option);

                return (
                    <CoinWrapper {...props} key={wallet.id}>
                        <IconBuilder
                            icon={wallet.coin.svgIcon()}
                            sx={{fontSize: "25px"}}
                        />

                        <Box component="span" sx={{ml: 1}}>
                            {wallet.coin.name}
                        </Box>
                    </CoinWrapper>
                );
            }}
        />
    );
};

const CoinWrapper = styled("li")({
    display: "flex",
    alignItems: "center",
    flexGrow: 1,
    flexBasis: 0
});

export default WalletSelect;
