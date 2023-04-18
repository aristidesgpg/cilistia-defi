import React, {useCallback, useEffect, useState} from "react";
import {useParams} from "react-router-dom";
import {errorHandler, route, useRequest} from "services/Http";
import CommerceCustomer from "models/CommerceCustomer";
import Result404 from "components/Result404";
import LoadingFallback from "components/LoadingFallback";
import {CommerceCustomerProvider} from "contexts/CommerceCustomerContext";
import Content from "./components/Content";

const Customer = () => {
    const params = useParams();
    const [customer, setCustomer] = useState();
    const [request, loading] = useRequest();

    const fetchCustomer = useCallback(() => {
        request
            .get(route("commerce-customer.get", {id: params.id}))
            .then((data) => setCustomer(CommerceCustomer.use(data)))
            .catch(errorHandler());
    }, [request, params]);

    useEffect(() => {
        fetchCustomer();
    }, [fetchCustomer]);

    return (
        <LoadingFallback
            content={customer}
            fallback={<Result404 />}
            compact={true}
            loading={loading}
            size={70}>
            {(customer) => (
                <CommerceCustomerProvider
                    fetchCustomer={fetchCustomer}
                    customer={customer}>
                    <Content />
                </CommerceCustomerProvider>
            )}
        </LoadingFallback>
    );
};

export default Customer;
