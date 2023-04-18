import React from "react";
import PeerTradesTable from "components/PeerTradesTable";

const ActivePeerTradeBuy = () => {
    return <PeerTradesTable type="buy" status="active" />;
};

ActivePeerTradeBuy.dimensions = {
    lg: {w: 6, h: 3, minW: 6, minH: 3},
    md: {w: 6, h: 3, minW: 6, minH: 3},
    sm: {w: 2, h: 3, minW: 2, minH: 3},
    xs: {w: 1, h: 3, minW: 1, minH: 3}
};

export default ActivePeerTradeBuy;
