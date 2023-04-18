import React from "react";
import Result403 from "components/Result403";

export function can(permission) {
    return function (next) {
        return function (auth) {
            if (!auth.can(permission)) {
                return <Result403 />;
            }
            return next(auth);
        };
    };
}

export function cannot(permission) {
    return function (next) {
        return function (auth) {
            if (auth.can(permission)) {
                return <Result403 />;
            }
            return next(auth);
        };
    };
}

export function check(predicate) {
    return function (next) {
        return function (auth) {
            if (!predicate(auth)) {
                return <Result403 />;
            }
            return next(auth);
        };
    };
}
