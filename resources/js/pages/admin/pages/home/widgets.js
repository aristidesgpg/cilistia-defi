import SystemStatus from "components/SystemStatus";
import RegistrationChart from "components/RegistrationChart";
import LatestUsers from "components/LatestUsers";
import PendingVerification from "components/PendingVerification";
import PendingDeposits from "components/PendingDeposits";
import PendingWithdrawals from "components/PendingWithdrawals";
import EarningSummary from "components/EarningSummary";

export default [
    {
        name: "earning_summary",
        component: EarningSummary
    },
    {
        name: "system_status",
        component: SystemStatus
    },
    {
        name: "pending_verification",
        component: PendingVerification
    },
    {
        name: "pending_deposits",
        component: PendingDeposits
    },
    {
        name: "pending_withdrawals",
        component: PendingWithdrawals
    },
    {
        name: "latest_users",
        component: LatestUsers
    },
    {
        name: "registration_chart",
        component: RegistrationChart
    }
];
