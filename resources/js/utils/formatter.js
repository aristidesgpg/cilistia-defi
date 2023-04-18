import numeral from "numeral";
import {parseDate} from "utils/helpers";

export function formatData(number) {
    return numeral(number).format("0[.]0b");
}

export function formatPercent(number) {
    return numeral(number / 100).format("0[.]0%");
}

export function formatNumber(number) {
    return numeral(number).format("0[.]0a");
}

export function formatValue(number) {
    return numeral(number).format("0,0[.]00");
}

export function formatDollar(number) {
    return numeral(number).format("$0,0[.]00");
}

export function formatDate(value, format = "LL") {
    return !value ? null : parseDate(value).format(format);
}

export function formatDateTime(value) {
    return formatDate(value, "L LT");
}

export function formatDateFromNow(value) {
    return !value ? null : parseDate(value).fromNow();
}
