import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
import utc from "dayjs/plugin/utc";
import advancedFormat from "dayjs/plugin/advancedFormat";
import localizedFormat from "dayjs/plugin/localizedFormat";
import isBetween from "dayjs/plugin/isBetween";
import isSameOrAfter from "dayjs/plugin/isSameOrAfter";
import relativeTime from "dayjs/plugin/relativeTime";

dayjs.extend(isSameOrAfter);
dayjs.extend(relativeTime);
dayjs.extend(customParseFormat);
dayjs.extend(utc);
dayjs.extend(advancedFormat);
dayjs.extend(localizedFormat);
dayjs.extend(isBetween);

dayjs.locale("en");

export const now = () => dayjs();

export default dayjs;
