import {useEffect, useRef} from "react";

const useCallbackRef = (callback) => {
    const action = useRef(callback);

    useEffect(() => {
        action.current = callback;
    }, [callback]);

    return action;
};

export default useCallbackRef;
