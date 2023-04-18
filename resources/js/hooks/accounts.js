import {useDispatch, useSelector} from "react-redux";
import {find, first, get, isEmpty} from "lodash";
import WalletAccount from "models/WalletAccount";
import PaymentAccount from "models/PaymentAccount";
import {useEffect, useMemo} from "react";
import {setActiveAccount} from "redux/slices/wallet";
import CommerceAccount from "models/CommerceAccount";

/**
 * Get all wallet accounts
 */
export function useWalletAccounts() {
    const {data, loading} = useSelector((state) => {
        return get(state, "wallet.accounts");
    });

    return {accounts: data, loading};
}

/**
 * Select first wallet account
 */
export function useWalletAccountSelector() {
    const {accounts} = useWalletAccounts();
    const dispatch = useDispatch();

    const selected = useSelector((state) => {
        return get(state, "wallet.activeAccount");
    });

    useEffect(() => {
        if (!isEmpty(accounts) && !selected) {
            dispatch(setActiveAccount(first(accounts).id));
        }
    }, [accounts, dispatch, selected]);
}

/**
 * Get selected account
 */
export function useActiveWalletAccount() {
    const {accounts, loading} = useWalletAccounts();

    const selected = useSelector((state) => {
        return get(state, "wallet.activeAccount");
    });

    const account = useMemo(() => {
        const record = find(accounts, (o) => o.id === selected);
        return WalletAccount.use(record);
    }, [selected, accounts]);

    return {account, loading};
}

/**
 * Get payment account
 */
export function usePaymentAccount() {
    const {data, loading} = useSelector((state) => {
        return get(state, "payment.account");
    });

    const account = useMemo(() => {
        return PaymentAccount.use(data);
    }, [data]);

    return {account, loading};
}

/**
 * Get commerce account
 */
export function useCommerceAccount() {
    const {data, loading} = useSelector((state) => {
        return get(state, "commerce.account");
    });

    const account = useMemo(() => {
        return CommerceAccount.use(data);
    }, [data]);

    return {account, loading};
}
