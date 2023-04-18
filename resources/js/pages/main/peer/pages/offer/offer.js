import React, {useCallback, useEffect, useState} from "react";
import {useParams} from "react-router-dom";
import {errorHandler, route, useRequest} from "services/Http";
import PeerOffer from "models/PeerOffer";
import Result404 from "components/Result404";
import {PeerOfferProvider} from "contexts/PeerOfferContext";
import Content from "./components/Content";
import LoadingFallback from "components/LoadingFallback";

const Offer = () => {
    const {id} = useParams();
    const [request, loading] = useRequest();
    const [offer, setOffer] = useState();

    const fetchOffer = useCallback(() => {
        request
            .get(route("peer-offer.get", {offer: id}))
            .then((data) => setOffer(PeerOffer.use(data)))
            .catch(errorHandler());
    }, [request, id]);

    useEffect(() => {
        fetchOffer();
    }, [fetchOffer]);

    return (
        <LoadingFallback
            content={offer}
            fallback={<Result404 />}
            compact={true}
            loading={loading}
            size={70}>
            {(offer) => (
                <PeerOfferProvider offer={offer} fetchOffer={fetchOffer}>
                    <Content />
                </PeerOfferProvider>
            )}
        </LoadingFallback>
    );
};

export default Offer;
