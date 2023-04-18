import React, {useCallback, useEffect, useState} from "react";
import {useParams} from "react-router-dom";
import {errorHandler, route, useRequest} from "services/Http";
import CommercePayment from "models/CommercePayment";
import Result404 from "components/Result404";
import {CommercePaymentProvider} from "contexts/CommercePaymentContext";
import LoadingFallback from "components/LoadingFallback";
import Content from "./components/Content";

const Payment = () => {
    const params = useParams();
    const [payment, setPayment] = useState();
    const [request, loading] = useRequest();

    const fetchPayment = useCallback(() => {
        request
            .get(route("commerce-payment.get", {id: params.id}))
            .then((data) => setPayment(CommercePayment.use(data)))
            .catch(errorHandler());
    }, [request, params]);

    useEffect(() => {
        fetchPayment();
    }, [fetchPayment]);

    return (
        <LoadingFallback
            content={payment}
            fallback={<Result404 />}
            compact={true}
            loading={loading}
            size={70}>
            {(payment) => (
                <CommercePaymentProvider
                    fetchPayment={fetchPayment}
                    payment={payment}>
                    <Content />
                </CommercePaymentProvider>
            )}
        </LoadingFallback>
    );
};

export default Payment;
