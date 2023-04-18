import {useSelector} from "react-redux";
import {get} from "lodash";
import CommercePayment from "models/CommercePayment";
import {useMemo} from "react";

const useCommercePayment = () => {
    const {data, loading} = useSelector((state) => {
        return get(state, "commercePayment.resource");
    });

    const payment = useMemo(() => {
        return CommercePayment.use(data);
    }, [data]);

    return {payment, loading};
};

export const useCommerceSettings = () => {
    return useSelector((state) => {
        return get(state, "commercePayment.settings");
    });
};

export default useCommercePayment;
