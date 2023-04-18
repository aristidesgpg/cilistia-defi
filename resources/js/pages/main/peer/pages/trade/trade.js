import React, {useCallback, useEffect, useState} from "react";
import {useParams} from "react-router-dom";
import {errorHandler, route, useRequest} from "services/Http";
import PeerTrade from "models/PeerTrade";
import Result404 from "components/Result404";
import {PeerTradeProvider} from "contexts/PeerTradeContext";
import Content from "./components/Content";
import LoadingFallback from "components/LoadingFallback";

const Trade = () => {
    const {id} = useParams();
    const [request, loading] = useRequest();
    const [trade, setTrade] = useState();

    const fetchTrade = useCallback(() => {
        request
            .get(route("peer-trade.get", {trade: id}))
            .then((data) => setTrade(PeerTrade.use(data)))
            .catch(errorHandler());
    }, [request, id]);

    useEffect(() => {
        fetchTrade();
    }, [fetchTrade]);

    return (
        <LoadingFallback
            content={trade}
            fallback={<Result404 />}
            compact={true}
            loading={loading}
            size={70}>
            {(trade) => (
                <PeerTradeProvider trade={trade} fetchTrade={fetchTrade}>
                    <Content />
                </PeerTradeProvider>
            )}
        </LoadingFallback>
    );
};

export default Trade;
