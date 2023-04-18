import {useEffect, useState, useCallback} from "react";
import {isFunction} from "lodash";

export default function useSessionStorage(key, defaultValue) {
    const [value, setValue] = useState(() => {
        const storedValue = sessionStorage.getItem(key);
        return storedValue ? JSON.parse(storedValue) : defaultValue;
    });

    useEffect(() => {
        const storageListener = function (e) {
            if (e.storageArea === sessionStorage && e.key === key) {
                setValue(JSON.parse(e.newValue));
            }
        };

        window.addEventListener("storage", storageListener);

        return () => {
            window.removeEventListener("storage", storageListener);
        };
    }, [key, defaultValue]);

    const setValueInSessionStorage = useCallback(
        (value) => {
            setValue((current) => {
                const nextValue = isFunction(value) ? value(current) : value;
                sessionStorage.setItem(key, JSON.stringify(nextValue));
                return nextValue;
            });
        },
        [key]
    );

    return [value, setValueInSessionStorage];
}
