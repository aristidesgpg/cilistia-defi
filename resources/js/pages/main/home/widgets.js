import PriceChart from "components/PriceChart";
import PaymentAccountChart from "components/PaymentAccountChart";
import WalletAccountChart from "components/WalletAccountChart";
import FeatureLimits from "components/FeatureLimits";
import RecentTransaction from "components/RecentTransaction";
import ActivePeerTradeSell from "components/ActivePeerTradeSell";
import ActivePeerTradeBuy from "components/ActivePeerTradeBuy";

export default [
    {
        name: "price_chart",
        component: PriceChart
    },
    {
        name: "payment_account_chart",
        component: PaymentAccountChart
    },
    {
        name: "wallet_account_chart",
        component: WalletAccountChart
    },
    {
        name: "recent_activity",
        component: RecentTransaction
    },
    {
        name: "feature_limits",
        component: FeatureLimits
    },
    {
        name: "active_peer_trade_buy",
        component: ActivePeerTradeBuy
    },
    {
        name: "active_peer_trade_sell",
        component: ActivePeerTradeSell
    }
];
