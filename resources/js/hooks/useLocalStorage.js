import {isFunction} from "lodash";
import {useEffect, useState, useCallback} from "react";

export default function useLocalStorage(key, defaultValue) {
    const [value, setValue] = useState(() => {
        const storedValue = localStorage.getItem(key);
        return storedValue ? JSON.parse(storedValue) : defaultValue;
    });

    useEffect(() => {
        const storageListener = function (e) {
            if (e.storageArea === localStorage && e.key === key) {
                setValue(JSON.parse(e.newValue));
            }
        };

        window.addEventListener("storage", storageListener);

        return () => {
            window.removeEventListener("storage", storageListener);
        };
    }, [key, defaultValue]);

    const setValueInLocalStorage = useCallback(
        (value) => {
            setValue((current) => {
                const nextValue = isFunction(value) ? value(current) : value;
                localStorage.setItem(key, JSON.stringify(nextValue));
                return nextValue;
            });
        },
        [key]
    );

    return [value, setValueInLocalStorage];
}
